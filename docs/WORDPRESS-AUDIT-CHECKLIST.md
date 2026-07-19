# WordPress infrastructure audit

Run this audit on staging before changing production.

## Access required

- A temporary, named WordPress Administrator account created specifically for this project
- Staging WordPress URL and confirmation that it is not indexed
- Hosting/control-panel or SSH/SFTP access only if server-level checks or deployment require it
- Backup dashboard access or written confirmation of database/files backup and tested restore path
- No credentials sent through GitHub, repository files, commit messages, or public Slack channels

## Environment

- [ ] WordPress core version and update status
- [ ] PHP version, SAPI, memory limit, max execution time, max input variables and upload size
- [ ] MySQL/MariaDB version, charset and collation
- [ ] HTTPS, canonical host, permalink structure and timezone
- [ ] WordPress Address and Site Address
- [ ] REST API, loopback requests and WP-Cron health
- [ ] Writable directories and filesystem method
- [ ] `WP_DEBUG`, logging and environment type are appropriate for staging/production

## WPML gate

- [ ] WordPress 6.0+
- [ ] PHP 7.4–8.5
- [ ] MySQL 5.6+ or MariaDB 10.1+ using `utf8mb4`
- [ ] WordPress memory 128 MB minimum; 256 MB preferred
- [ ] REST API enabled
- [ ] Database user can create WPML tables
- [ ] PHP `mbstring`, SimpleXML and libxml 2.7.8+ available
- [ ] PHP `eval()` is not disabled
- [ ] WPML Multilingual CMS and String Translation installed, licensed and current
- [ ] English is configured as the source language
- [ ] Persian and Arabic render RTL; Turkish and English render LTR

## Elementor gate

- [ ] Elementor and Elementor Pro versions are mutually compatible and current
- [ ] Elementor experimental features are inventoried
- [ ] Elementor V4 beta-only features are avoided until WPML issues are cleared
- [ ] Global colors, fonts, breakpoints and container settings are inventoried
- [ ] Theme Builder templates, display conditions, forms and submissions are inventoried
- [ ] Regenerate CSS/Data works on staging

## Theme and plugins

- [ ] Hello Elementor version and child-theme status
- [ ] Active and inactive plugin inventory with purpose, owner, license and update status
- [ ] Duplicate SEO, cache, security, form, optimization or translation functionality identified
- [ ] Abandoned, vulnerable or unnecessary extensions flagged
- [ ] Must-use plugins and drop-ins inventoried
- [ ] Custom snippets and code-injection plugins exported for review

## Content and migration

- [ ] Pages, posts, properties, products, taxonomies and media counted
- [ ] Existing custom fields and post relationships documented
- [ ] Forms, notifications, recipients and delivery logs tested safely
- [ ] Current URLs, redirects, metadata, canonical tags and structured data exported
- [ ] Existing multilingual data and language relationships identified
- [ ] Personal data and retention requirements documented

## Backup, staging and deployment

- [ ] Fresh files and database backup exists
- [ ] Restore procedure and responsible person are documented
- [ ] Staging is protected and excluded from search indexing
- [ ] Production-to-staging refresh rules protect live leads and credentials
- [ ] Deployment and rollback procedure is documented

## Audit output

The audit ends with a red/amber/green report, a version freeze, a plugin keep/replace/remove decision, and explicit approval to begin implementation.

