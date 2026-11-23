# Playbook: update of HOEGERT products from the eopt feed (upc `BOC-<EAN>`)

Based on the 2026-11-21/23 run (32 re-hooked clothing sizes, 9 re-coded EANs, 3 unhooked, 31 new products).
Working files of that run: `save/hoegert_20261121/` (PLAN.md with facts, decisions and run log, inputs, payloads, journals).

## 1. What runs by itself and what needs this playbook

| Task | How | Action needed |
|---|---|---|
| Price and stock of existing cards | prod cron token `22052026` at :10 of 7/11/15/19 h, DMPRO profile `Hoegert-parsing-13022026` (only_update_data), reads `http://10.100.0.124:8001/feeds/6_hoegert.csv`, matches `upc = 'BOC-' . sku` | none; check it works (step 2) |
| Wrong supplier link (rotated sizes, re-coded EAN) | `scripts/hoegert_fix_existing.php` | every run, before the import |
| New products | `scripts/hoegert_import_new.php` | periodic |
| Dev (`ls`) prices and stock | `scripts/hoegert_align_dev_from_prod.php` | after every prod change; dev runs no supplier crons |

## 2. Sources

- Feed (content, uk only): `https://eopt.ua/eopt-cc7ba726731effab410b2d46aed1da7f.xml` (the URL rotates; the ones in the old
  ls DMPRO profiles `Hoegert-UA` / `HOEGERT-price` are 404). `<sku>` = EAN, `<code>` = article (in the name in brackets),
  `<base_price>` UAH, one `<image>`, `<param>` uk. No category per offer. eopt.ua is a sister site of the same company.
- ru text and category: the product page `https://eopt.ua/ru/products/<slug>`: h1, `#description .tab_product_pad_top`,
  `#features ul.features_tab li` (other order than the feed, extra lines), breadcrumbs (often empty for new items).
  Request with gzip. Items without a feed picture usually have no content on eopt at all.
- Parser CSV (`sku;price;stock;purchase;vat`, in-stock only) = what the cron writes: price = CSV/45, quantity = stock,
  quantity 0 when the EAN is absent.
- Prod DB `radioshop` is the truth. Dev follows prod.

## 3. Hard rules

1. All CLI with `/usr/local/lsws/lsphp74/bin/php`.
2. Prod writes only after an explicit go from Vadym for this run; dev first.
3. Prod window: not between HH:05 and HH:20 at 7/11/15/19 (cron at HH:10).
4. Full dump of the target DB before the first write on it. `--dry-run` first, read the full output.
5. Never change the URL or name of an existing card; fix the link (upc) instead.
6. Retire a link with the `1` prefix (`1BOC-<EAN>`), never delete cards.
7. No special price for new products. New cards without a photo or a description are imported disabled.

## 4. Steps

```
cd /var/www/ls.radio-shop.com.ua
PHP=/usr/local/lsws/lsphp74/bin/php
D=save/hoegert_YYYYMMDD; mkdir -p $D/src
P=/var/www/radio-shop.com.ua/html/config.php
```

### Step 1. Freeze inputs
Download the feed to `$D/src/eopt_feed.xml` and the CSV to `$D/src/6_hoegert.csv`; every later step reads these files.

### Step 2. Check the cron
Prod HOEGERT cards with `date_modified` today, and `price*45` vs CSV (differences under 2 UAH are rounding).

### Step 3. Fix links, build per DB (read-only)
```
$PHP scripts/hoegert_fix_existing.php build --config=$P --feed=$D/src/eopt_feed.xml --csv=$D/src/6_hoegert.csv \
  --out=$D/fix_data_prod.json [--unhook=<product_id,...>]
```
Kinds: `rehook` (the card name says article A, the feed gives A one EAN, the card holds another one: rotated sizes),
`ean_changed` (the card's EAN left the feed, its article is there under one free EAN), `hook` (upc is a bare article),
`unhook` (listed ids get `1BOC-`). The build refuses a final state with one EAN on two cards; a `hook` whose EAN another card
already holds is reported as a duplicate card (2026-11: 7808817/7809078/7809079 on prod). Review the list and the report.

### Step 4. New products build (no DB writes, images into the dev root)
```
$PHP scripts/hoegert_import_new.php build --feed=$D/src/eopt_feed.xml --csv=$D/src/6_hoegert.csv --prod-config=$P \
  --dev-config=html/config.php --root=html --out=$D/build1 --overrides=$D/overrides.json \
  --fix-data=$D/fix_data_prod.json[,$D/fix_data_ls.json] --id-start=<block free on both DBs>
$PHP scripts/hoegert_import_new.php translate --todo=$D/build1/translate_todo.json --env=.env --overrides=$D/overrides.json
# rebuild after translate; translate_todo.json must end empty
```
`--fix-data` makes the build count fixes that are not applied yet on a DB (otherwise released EANs look taken).
Review `preview.csv`: category source (`override`, `sibling`, `vote <prefix> n/m`), flags, ru names. Wrong votes go to
`overrides.json` as `{"<EAN>": {"category_id": N, "note": "..."}}`. Check `files.json` paths are jpeg/png/gif only.

### Step 5. Dev (`ls`)
```
mysqldump ... ls | gzip > save/ls_full_YYYYMMDD_before_hoegert.sql.gz
$PHP scripts/hoegert_fix_existing.php build --config=html/config.php ... --out=$D/fix_data_ls.json [--unhook=...]
$PHP scripts/hoegert_fix_existing.php apply --config=html/config.php --data=$D/fix_data_ls.json --journal=$D/fix_journal_ls.json [--dry-run]
# repeat build/apply (fix2_..., fix3_...) until build reports no changes
$PHP scripts/hoegert_import_new.php apply --config=html/config.php --payload=$D/build1/payload.json --journal=$D/import_journal_ls.json [--dry-run]
rm -rf /var/www/ls.radio-shop.com.ua/cache/*
```
Verify on `https://ls.radio-shop.com.ua:4443/` (uk under `/uk/`, ru at the root): product pages, search by article, category.
Do NOT run the align script before prod is fixed: it copies prod upc back.

### Step 6. Prod, after the go
```
mysqldump ... radioshop | gzip > $D/radioshop_full_YYYYMMDD_before_hoegert.sql.gz
$PHP scripts/hoegert_import_new.php sync-files --manifest=$D/build1/files.json --from=html --to=/var/www/radio-shop.com.ua/html --created-log=$D/prod_files_created.log [--dry-run]
$PHP scripts/hoegert_fix_existing.php apply --config=$P --data=$D/fix_data_prod.json --journal=$D/fix_journal_prod.json [--dry-run]
# rebuild + apply the next pass until build reports no changes
cp $D/build1/payload.json $D/build1/payload_prod.json
$PHP scripts/hoegert_import_new.php apply --config=$P --payload=$D/build1/payload_prod.json --journal=$D/import_journal_prod.json --assign-sku [--dry-run]
```
Then one admin save of any product on prod (LSCache purges the Product/Category tags). Pages of a card that was disabled
(retired duplicate) may stay cached after that save: find the entry with
`find /var/www/radio-shop.com.ua/cache -type f -print0 | xargs -0 /bin/grep -a -l '<old keyword>'`, check its tag line
(`P_<product_id>`) and delete only that file. After the next cron run check the
re-hooked and new cards (`date_modified` today, `price = CSV/45`).

### Step 7. Align dev with prod
```
$PHP scripts/hoegert_align_dev_from_prod.php --source-config=$P --target-config=html/config.php --journal=$D/align_dev_journal.json [--dry-run]
```
Copies upc, sku, quantity, price, cost_price, status, stock_status_id, manufacturer_id by product_id (also the prod sku of
the new cards).

### Step 8. Rollback (reverse order)
```
$PHP scripts/hoegert_import_new.php rollback --config=<cfg> --journal=$D/import_journal_<db>.json
$PHP scripts/hoegert_import_new.php remove-files --created-log=$D/prod_files_created.log --root=/var/www/radio-shop.com.ua/html
$PHP scripts/hoegert_fix_existing.php rollback --config=<cfg> --journal=$D/fix2_journal_<db>.json
$PHP scripts/hoegert_fix_existing.php rollback --config=<cfg> --journal=$D/fix_journal_<db>.json
```

## 5. Known traps
- Name comparison with the feed: ~1300 cards differ only in wording or the size suffix after HOEGERT, ~90 only in HTML
  entities; that is not an error. The real errors are a different article in the brackets.
- The feed gives one EAN to two codes for some items (`N_HT1P008`, `.HT4C672`, `HT1E402_`, `HT1R444_1`); the card is fine.
- Some existing attributes have typos in the UK name ("Масимальний діаметр ..."): the import matches by the RU name too.
- Some existing group-8 attributes have an empty RU name (Сезон, Стандарт); the import does not change existing attributes.
- Older clothing cards own keywords of other sizes; new cards then get a `-2` keyword. Do not "fix" old URLs.
