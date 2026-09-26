# INFOROVA — Content & Advertisement Website

A simple, run-and-forget PHP/MySQL blog: articles, categories and static pages. There is no admin panel, no login, and no locker/timer/redirect system — a visitor goes straight from a link to the article, and Google AdSense Auto ads decides where to place ads automatically.

## Included
- Responsive light-blue editorial design
- Home, categories, search, article pages
- About, Contact, Privacy Policy, Terms, Disclaimer, Cookie Policy, FAQ, Editorial Policy, Advertise, Accessibility
- MySQL-backed articles/categories/pages content
- Google AdSense Auto ads script embedded directly in every page's `<head>` (no ad-code settings screen needed)
- `ads.txt`, `robots.txt`, sitemap endpoint and security headers
- No forced ad clicks, fake traffic, automatic ad interaction, timers, or cloaking

## Setup (one time)
1. Import `sql/schema.sql` into MySQL, then `sql/seed_content.sql`.
2. Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and optionally `DB_PORT` and `APP_URL` (see `.env.example`).
3. Deploy and open the site — articles and pages appear automatically (the first request also seeds ~38 additional long-form articles and 3 resource-hub pages). Nothing else to configure.

## Advertising
The AdSense Auto ads script for `ca-pub-1690994340482950` is already placed in `includes/header.php`, so it loads on every page without any per-page setup. Auto ads decides how many units to show and where, typically two to three per page. `ads.txt` at the site root already lists the matching publisher ID. Approval itself depends on Google's own review; no implementation can guarantee it.

## Production notes
- This build intentionally has no admin UI — content lives in the SQL seed files and is only meant to change by editing those files and redeploying.
- Ad placement and count are controlled by Google's Auto ads system, not by this codebase.
