# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

OpenCart 3.0.2.0 store **radio-shop.com.ua**, currently being recovered after a prod crash. Treat every change as restoration, not greenfield. The DB dump in [save/march01.radio.sql](save/march01.radio.sql) (2026-03-01, 1.3 GB) and its MariaDB-transformed sibling [save/march01.radio.mariadb.sql](save/march01.radio.mariadb.sql) are the canonical source of truth for content. The [save/html/](save/html/) tree is a pre-crash file-system snapshot — when a live file looks corrupt, diff against `save/html/` first.

## Stack (don't propose changes casually)

- **Web server: LiteSpeed Enterprise** at `/usr/local/lsws/` (not Apache, not Nginx, not OpenLiteSpeed). Reads `.htaccess`. Restart: `/usr/local/lsws/bin/lswsctrl reload`. Has LSCache (separate from OpenCart cache).
- **PHP 7.4.33 with ionCube Loader v15.5.0**. ionCube-encoded files cannot be read or edited — recognise and skip them. PHP 7.4 is deliberate (old broken prod was 7.2).
- **MariaDB 11.8 remote at `10.100.0.103:3306`**, DB `radioshop`, user `qxi9802lkdas`, prefix `oc_`. Not localhost. Password lives in [html/config.php](html/config.php) / [html/admin/config.php](html/admin/config.php) — both are intentionally tracked, see `.gitignore`.
- **Redis local at `127.0.0.1:6379`** — currently unused (cache adaptor reverted to `file`).
- Hosts: working host is `ls.radio-shop.com.ua`; prod canonical is `radio-shop.com.ua`. `HTTP_SERVER` in both config.phps points at `ls.`.

## Environments: prod vs staging

Two webroots on this server, each its own git checkout of the same repo (`origin` = `git@github.com:vadimbk/ls.git`, branch `main`; `.git` at the dir root, webroot under `html/`):

- **`/var/www/radio-shop.com.ua/`** — **prod**, host `radio-shop.com.ua`. Changes are edited and committed here — source of truth.
- **`/var/www/ls.radio-shop.com.ua/`** — **staging**, host `ls.radio-shop.com.ua`. Updated by pulling from `origin`.

`html/config.php` + `html/admin/config.php` are host-specific (DB creds, `HTTP_SERVER`) and gitignored — each environment keeps its own; never overwrite one host's configs with the other's. The careful staging-update steps live in auto-memory.

## Repo layout (non-obvious bits only)

```
html/                    OpenCart webroot. .htaccess at html/.htaccess (1062 lines, ~820 redirects)
html/admin/              OpenCart admin (separate config.php)
html/system/*.ocmod.xml  42 OCMOD packages — source of admin/* and catalog/* customisations
html/system/library/cache/   Cache adaptors — only file.php exists; rest wiped by crash
storage/                 OpenCart DIR_STORAGE (cache/, modification/, logs/, session/, …) — *outside* html/
storage/modification/    OCMOD output: edited copies of admin/catalog/system files that override originals
storage/cache/           File cache (cache.* entries). cache.mmsheme.* = vertical mega-menu; cache.mmheader.* = horizontal
save/                    Pre-crash backup (DB dumps + html snapshot). Gitignored.
.htaccess.bak-20260521   Pre-2026-05-21 .htaccess (had ~820 hardcoded prod-host redirects)
```

There is **no build, no lint, no test suite**. PHP is interpreted directly. "Running" the app means hitting LiteSpeed on the configured host.

## OCMOD modification pipeline (critical mental model)

OpenCart 3 OCMOD extensions don't edit source files in place — each `*.ocmod.xml` describes diffs that get materialised into [storage/modification/](storage/modification/) by the admin's "Extensions → Modifications → Refresh" action. OpenCart's autoloader then prefers `storage/modification/<path>` over `html/<path>` whenever a modified copy exists.

Consequence: editing `html/system/library/url.php` **does not take effect** unless `storage/modification/system/library/url.php` is also updated (or rebuilt from the OCMOD XML). When debugging URL/SEO behaviour, always check both:

```bash
diff html/system/library/url.php storage/modification/system/library/url.php
```

`storage/modification/` files dated 2026-02-25 are from before the crash and are still authoritative until next OCMOD rebuild.

## Operations you'll actually run

```bash
# DB connect (password is in html/config.php)
mysql -h 10.100.0.103 -P3306 -u qxi9802lkdas -p radioshop

# Clear OpenCart file cache (safe; will regenerate on next request)
rm -f storage/cache/cache.*

# Test a URL through LiteSpeed (k = ignore self-signed if applicable)
curl -sk -o /dev/null -w "HTTP %{http_code} -> %{redirect_url}\n" https://ls.radio-shop.com.ua/<path>

# Reload LiteSpeed after config changes (rarely needed; .htaccess is hot-reloaded)
/usr/local/lsws/bin/lswsctrl reload
```

LSCache: separate from `storage/cache/`. If a fix looks correct but you still see stale HTML, purge LSCache (admin UI or `/usr/local/lsws/admin/`). The two caches don't invalidate each other.

## Repository hygiene

- **Hard rule: no Russian (or other non-English) text in code comments** — chat/explanations in Russian are fine. Auto-memory tracks this.
- `html/config.php` and `html/admin/config.php` are **gitignored** (contain DB credentials and host-specific URLs that differ between prod and staging). Keep them on disk but never commit. If the DB password needs rotation, update both files manually on each host.
- `save/` is gitignored. Never commit dumps.
- Don't bulk-rewrite hardcoded `radio-shop.com.ua` strings — many of them are intentional (langmark prefix; see below).

## Pitfalls that have bitten previous sessions

These are written up in detail under `/var/www/.claude/projects/-var-www-radio-shop-com-ua/memory/`; the headlines:

- **`asc_langmark` per-language prefix stores the host on purpose** on this project (`"prefix":"radio-shop.com.ua/uk"`). Vanilla OpenCart would store `uk` alone. Naive normalisation broke the menu — don't do it.
- **The `/uk/` language prefix doesn't resolve on `ls.radio-shop.com.ua`.** SeoLang matches incoming routes against `HTTP_SERVER + prefix`; with `HTTP_SERVER=ls.*` the comparison fails and `/uk/<slug>` 404s. Outgoing link generation has a mirror version of the same bug. Both directions break together; do not patch one without the other.
- **Cache adaptor: `system/library/cache/file.php` must exist** — the dir was wiped by the crash and only `file.php` has been restored. Removing it returns the site to "Could not load cache adaptor file cache!" 500s.
- **Don't `addGlobal()` on the OpenCart Twig wrapper** (`storage/modification/system/library/template/twig.php`). Catalog seems fine, admin breaks fully. Push globals from controllers via `$data[…]` instead.
- **The `.htaccess` was made host-agnostic on 2026-05-21** — 820 redirects went from `Redirect 301 /x https://radio-shop.com.ua/y` to `Redirect 301 /x /y` (mod_alias resolves to request host). Don't reintroduce absolute prod-host targets.
**Workflow:**
1. First, formulate the task (detail level: 100/10) considering all the aforementioned conditions.
2. Make at least 3 assumptions about why there is a data discrepancy (one of them must be intentionally incorrect).
3. Thoroughly analyze the problem. Identify possible logic errors and fix them.
4. Then, formulate the solution (detail level: 100/10) and justify why it is correct.
5. Perform a dry run of the solution to detect any errors.
6. Critique your own solution.
7. Fix any errors if they exist.
8. Provide the final ready-to-use code.

**Requirements:**
- KISS (Keep It Simple, Stupid).
- EXTREMELY SIMPLE, BULLETPROOF ALGORITHM.
- NO OVERCOMPLICATIONS OR "CODE OPTIMIZATIONS" OUTSIDE THE SCOPE OF THE TASK.
- STRICTLY FORBIDDEN TO TOUCH WORKING CODE UNLESS EXPLICITLY REQUIRED BY THE TASK.
- **NEVER modify, revert, or touch any code without explicit user instruction. Always ask for permission first.**
- **If any questions remain unanswered or requirements are unclear, you MUST ask before writing any code.**
- Before generating the final response, double-check yourself once more to ensure absolutely nothing is missing in the code.
- No Cyrillic characters in the script/code whatsoever — use American English only.
- No patchwork editing — you must provide the script or function in its entirety.
