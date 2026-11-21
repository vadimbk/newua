# Playbook: mass update of Grand Instrument products (upc `GI-`)

Based on the 2026-11-20/21 run (2033 new products, 204 manufacturer fixes, 19 disabled, 5 duplicates retired).
Working files of that run: `save/gi_20261120/` (PLAN.md, inputs, payloads, journals, dumps).

## 1. What runs by itself and what needs this playbook

| Task | How | Action needed |
|---|---|---|
| Price, stock, cost of existing cards | prod cron `toptul-23062026` at minute 20 of 08/12/16/20 h, DMPRO profile `Toptul-parsing-23062026`, matches `upc = 'GI-' . sku` | none, check it works (step 2) |
| New products from the supplier | this playbook, steps 3-9 | periodic, when many in-stock items are missing on the site |
| Brand / status cleanup of existing cards | `scripts/gi_fix_existing.php` | same run as new products |
| Dev (`ls`) prices and stock | `scripts/gi_align_dev_from_prod.php` | after every prod change, dev runs no supplier crons |

## 2. Sources and which one is right

- Prom feed (content: names RU/UK, descriptions, pictures, params, categories): `toptul.online/products_feed.xml?hash_tag=...`.
  The full URL with the hash is in the stale ls DMPRO profiles `Grand_*`. The feed has all ~6900 offers, in stock or not.
- Parser CSV (price UAH, stock, purchase): `http://10.100.0.124:8001/feeds/5_toptul.csv`, `sku;price;stock;purchase;vat`, in-stock items only,
  refreshed ~08:30. The cron uses it: price = CSV price / 45 (USD), quantity = stock, cost_price = purchase.
- Brand truth: the supplier site `https://grandinstrument.ua/ua/<article slug>/`, block `Виробник: <a href=".../vendors/<slug>/">`
  plus `"sku" content="..."`. Product URLs come from its sitemap (products-1..5.xml).
  The feed `<vendor>` is wrong for many items (PeakMeter -> Protester, "Dalnoboishchik" -> Standart, empty -> Україна).
  Vendor slug `sng` is the brand "Україна".
- Prod DB `radioshop` is the truth for the site. Dev `ls` follows prod, never the other way.

## 3. Hard rules

1. Add only brands that are on `grandinstrument.ua/ua/vendors/` AND already have GI cards on the site.
   Every product must be confirmed on the supplier site by its exact article. Never add the feed's no-brand value, an empty vendor or an unconfirmed brand.
2. No special price for new products.
3. All CLI scripts with `/usr/local/lsws/lsphp74/bin/php` (php 8.3 silently fails on thumbnails).
4. Prod writes only after an explicit go from Vadym for this run; staging first, prod second.
5. Prod time window: not between HH:15 and HH:30 at 08/12/16/20 h (the cron starts at HH:20).
6. Full dump of the target DB before the first write on it.
7. Never overwrite a different existing image file; every script logs what it created.

## 4. Steps

Set once per run (date of the run):
```
cd /var/www/ls.radio-shop.com.ua
PHP=/usr/local/lsws/lsphp74/bin/php
D=save/gi_YYYYMMDD; mkdir -p $D/src
P=/var/www/radio-shop.com.ua/html/config.php     # prod config
```

### Step 1. Freeze the inputs
Download the feed and the CSV once into `$D/src/` (`toptul_feed.xml`, `5_toptul.csv`). Every later step reads these files,
so build, staging and prod use the same snapshot. Allow `LIBXML_PARSEHUGE` (feed ~40 MB).

### Step 2. Check the cron before touching anything
Count GI cards with `date_modified` today and compare prices with the fresh CSV (`price = CSV/45`).
Reference: `save/gi_20261120/discovery/croncheck.php`. If the cron is broken, fix that first: new cards would not be updated either.

### Step 3. Find candidates (read-only)
1. Candidates = CSV articles that are not inside any prod `upc` (split the upc by comma; `GI-` prefix).
2. Keep only feed vendors that map to the official brand list and already have GI cards.
3. Look every candidate up on grandinstrument.ua: article -> slug (lowercase, translit, non-alnum -> `-`, also without dashes,
   `х`->`h|kh`, `ц`->`c|ts`), check the URL in the sitemap list, fetch the page, read manufacturer and sku. Pause ~0.4 s between requests.
4. Mark possible duplicates: the same article typed with Cyrillic look-alike letters (`З`/`3`, `В`, `Н`, `Р`, `С`...),
   the code inside another upc (`GI-FSEB1240G`), an extra `N` prefix or `G`/`-B` suffix. They go to a review list, not to the import.
5. Output: `$D/gi_final_list_YYYYMMDD.csv` with `vendorCode,gi_brand`, and `gi_offbrand_final_YYYYMMDD.csv` for existing cards
   whose manufacturer must change.

The 2026-11 run did this with ad-hoc scripts, kept in `save/gi_20261120/discovery/` (p19 candidates, gi_take_check / gi_take_pass2 supplier lookup,
gi_brand_fetch / gi_pass2 existing cards, catsug category suggestions). They are not a tool yet: consolidating them into
`scripts/gi_candidates.php` is the open improvement for the next run.

Note: MariaDB compares `upc` case-insensitively and ignores trailing spaces, so `GI-RP7336s` and `GI-ANBV24A0 ` ARE found by the cron.
Only true look-alike letters break the match.

### Step 4. Categories
Run a build with `--no-images` first (fast). Products whose feed category has no clear vote from existing GI cards (share >= 0.7)
land in `category_todo.csv`. Prepare `feed category id -> site category id` suggestions, Vadym approves, save as `$D/category_map.json`
(start from `save/gi_20261120/category_map.json`, 71 entries). Feed category 73875618 (promotional goods) stays unmapped (skipped).

### Step 5. Build (no DB writes; downloads images into the staging root)
```
$PHP scripts/gi_import_new.php build --feed=$D/src/toptul_feed.xml --csv=$D/src/5_toptul.csv \
  --list=$D/gi_final_list_YYYYMMDD.csv --categories=$D/category_map.json \
  --prod-config=$P --dev-config=html/config.php --root=html --out=$D/build1 > $D/build1.log 2>&1
$PHP scripts/gi_fix_existing.php build --prod-config=$P --offbrand=$D/gi_offbrand_final_YYYYMMDD.csv \
  --csv=$D/src/5_toptul.csv --out=$D/fix_data.json
```
`--id-start` defaults to 7813001: pick a range free in BOTH DBs (`max(product_id)` of prod + margin).
Review `preview.csv` (one row per product), `skipped.csv`, `warnings.txt`, `category_todo.csv`. Expect a few minutes for ~2000 products.

Image handling inside the build (already in the script):
- `a.allegroimg.com` hangs from our servers; it is fetched through `https://external-content.duckduckgo.com/iu/?u=<url>&f=1`.
- Prom serves part of the pictures as WebP under a `.jpg` name; OpenCart 3 cannot make thumbnails from WebP (empty picture in admin
  and listings). The build re-encodes WebP to JPEG. Check after any build:
  `file -b --mime-type` over `files.json` paths must show only jpeg/png/gif.
- Description pictures are localised to `catalog/desc/GRANDINSTRUMENT/`; hidden external background images are stripped.

### Step 6. Staging (`ls`)
```
mysqldump -h 10.100.0.103 -u <db user> --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 ls \
  | gzip > save/ls_full_YYYYMMDD_before_gi_import.sql.gz
$PHP scripts/gi_fix_existing.php apply --config=html/config.php --data=$D/fix_data.json --journal=$D/fix_journal_ls.json --no-quantity-guard [--dry-run]
$PHP scripts/gi_import_new.php apply --config=html/config.php --payload=$D/build1/payload.json --journal=$D/import_journal_ls.json [--dry-run]
$PHP scripts/gi_attr_uk.php html/config.php $D/import_journal_ls.json scripts/gi_attr_uk.json [--dry-run]
rm -rf /var/www/ls.radio-shop.com.ua/cache/*      # LSCache of staging; absolute path only
```
Run every apply with `--dry-run` first. The fix must run before the import (the import needs new manufacturers such as Spin).
A dry-run of the import reports fewer attribute rows than the real run, because new attributes are not created in a dry-run.
`gi_attr_uk.php` translates only names present in `scripts/gi_attr_uk.json`; add the new names it reports as "no translation" (UK, by hand).
Verify on `https://ls.radio-shop.com.ua:4443/`: 10 random new product pages, a category listing, search by article, admin product form.

### Step 7. Prod (`radioshop`), only after the go
```
mysqldump ... radioshop | gzip > $D/radioshop_full_YYYYMMDD_before_gi_import.sql.gz
$PHP scripts/gi_import_new.php sync-files --manifest=$D/build1/files.json --from=html --to=/var/www/radio-shop.com.ua/html --created-log=$D/prod_files_created.log [--dry-run]
$PHP scripts/gi_fix_existing.php apply --config=$P --data=$D/fix_data.json --journal=$D/fix_journal_prod.json [--dry-run]
$PHP scripts/gi_import_new.php apply --config=$P --payload=$D/build1/payload.json --journal=$D/import_journal_prod.json --assign-sku [--dry-run]
$PHP scripts/gi_attr_uk.php $P $D/import_journal_prod.json scripts/gi_attr_uk.json
```
`--assign-sku` gives the next internal sku numbers on prod and writes them to `payload.json.applied.json`; copy them to `ls`
(same product_id) so both DBs carry the same sku.
Then ask Vadym to save any one product in the prod admin without changes: the LSCache module purges the Product/Category tags,
and only then the new products appear in category listings. Do not wipe the whole prod LSCache.
The save does not purge every cached category or filter page (seen 2026-11-21). For leftovers, scan the cache and delete only the
matching entry files: many entries are gzip-compressed, so a text grep misses them; use
`$PHP scripts/lscache_scan.php /var/www/radio-shop.com.ua/cache '<needle>'` (decompresses bodies).
A useful needle is `/var/www/radio-shop.com.ua/html/image/`: OpenCart prints that filesystem path as `<img src>` when it cannot make a thumbnail.

Verify with `curl -sk --resolve radio-shop.com.ua:443:127.0.0.1 ...`: random new product pages (200, local images only),
a category listing (`x-litespeed-cache: miss` after the purge), search, admin product list thumbnails.
After the next cron run, check that the new products got `date_modified` today and `price = CSV/45`.

### Step 8. Align dev with prod
```
$PHP scripts/gi_align_dev_from_prod.php --source-config=$P --target-config=html/config.php --journal=$D/align_dev_journal.json [--dry-run]
```
Copies upc, sku, quantity, price, cost_price, status, stock_status_id, manufacturer_id (and specials) for every prod `GI-%` card that exists on dev.
Run it after the prod cron has updated prices.

### Step 9. Rollback
Each script has a journal and its own rollback; go in reverse order:
```
$PHP scripts/gi_import_new.php rollback --config=<cfg> --journal=$D/import_journal_<db>.json
$PHP scripts/gi_import_new.php remove-files --created-log=$D/prod_files_created.log --root=/var/www/radio-shop.com.ua/html
$PHP scripts/gi_fix_existing.php rollback --config=<cfg> --journal=$D/fix_journal_<db>.json
```
Last resort: the full dump taken before the run.

## 5. Look-alike duplicates (old cards with Cyrillic letters in the upc)
If an old card carries a broken upc and a live card with the correct upc exists, do not merge them: disable the old card and move its
seo keywords to `oc_seo_url_generator_redirects` (301 to the live card), see `scripts/gi_twins_redirect.php` (pairs are hard-coded, one-off).
The redirect table is read only when the keyword is absent from `oc_seo_url`. If no live twin exists, fix the upc instead
(`gi_fix_existing.php` does that for codes present in the CSV).

## 6. Known traps
- Supplier vendor in the feed is unreliable: always use the supplier site.
- Some feed names lack the article or carry look-alike letters; reported by the build, not changed.
- After replacing image files on disk, pages in LSCache still point to the old result; purge as in step 7 and scan for leftovers.
- The 2024 GI batch also had WebP pictures under `.jpg` names (54 files, converted 2026-11-21). After any image work, check that every
  file referenced by `oc_product.image` / `oc_product_image` is jpeg, png or gif.
- Many broken image URLs in admin can trigger the anti-flood ban on the front proxy 10.100.0.91 (blank 403 for that IP).
- Dev `ls` cannot receive supplier crons; without step 8 its GI prices drift within a day.
