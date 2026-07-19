# CloudPanel backup and staging runbook

Target infrastructure: Hetzner VPS in Helsinki, managed with CloudPanel.

## Change constraints

- Do not update or delete any WordPress plugin.
- Keep the current third-party premium packages until official licenses are obtained.
- Keep Rank Math PRO inactive.
- Do not install or activate WPML String Translation on production before backup and staging exist.
- No development or bulk import on production.

## Backup strategy

Use two independent layers:

1. **Hetzner server snapshot** before infrastructure changes. This is a whole-server rollback point.
2. **CloudPanel Remote Backup** to off-site storage for site files, database and vhost configuration.

An off-site destination is required. Preferred options:

- Hetzner Storage Box over SFTP
- S3-compatible object storage
- Another SFTP server

Do not treat a backup stored only on the same VPS as sufficient.

### Verification

- Confirm the backup job reports success.
- Confirm the remote destination contains the site backup and database backup.
- Record creation time, retention and destination without recording credentials.
- Test restoration into staging before calling the backup verified.

## Staging architecture

CloudPanel does not document a one-click WordPress staging clone. Create an isolated site manually:

- Hostname: `staging.smartkeyturkey.com`
- Separate CloudPanel site user
- Separate database and database user
- Same PHP major/minor version as production initially
- TLS certificate after DNS is pointed to the server
- HTTP Basic Authentication at the web-server/control-panel level
- WordPress `blog_public = 0` and `noindex, nofollow`
- Outbound email disabled or rerouted to a safe mailbox
- Analytics, Search Console verification and production webhooks disabled
- Separate salts and credentials

### Clone sequence

1. Create the staging DNS record.
2. Create a new WordPress or PHP site in CloudPanel for the staging hostname.
3. Copy the production files into the staging document root.
4. Restore the production database into the separate staging database.
5. Update staging database credentials in `wp-config.php`.
6. Run a serialization-safe URL replacement from `https://smartkeyturkey.com` to `https://staging.smartkeyturkey.com` with WP-CLI.
7. Set `WP_ENVIRONMENT_TYPE` to `staging`.
8. Set WordPress and maximum memory limits.
9. Disable indexing and external side effects.
10. Verify login, pages, media, Elementor, WPML and permalinks.
11. Perform a documented restore test.

## WordPress memory

CloudPanel already exposes a PHP memory limit of 512 MB. The WordPress-specific limit is still 40 MB.

Open the production site's File Manager and edit:

`htdocs/smartkeyturkey.com/wp-config.php`

Add or replace these definitions before the line that loads `wp-settings.php`:

```php
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
```

Rules:

- Do not create duplicate definitions.
- Do not paste the database password or authentication salts into tickets, GitHub or Slack.
- Recheck Tools → Site Health → Info → WordPress Constants after saving.

For staging, also add:

```php
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
```

## WPML String Translation

The add-on is not currently present in the Installed Plugins list.

After staging is verified:

1. Obtain the `wpml-string-translation.zip` package that matches the installed WPML family.
2. In staging, open Plugins → Add Plugin → Upload Plugin.
3. Upload the ZIP and select Install Now.
4. Activate WPML String Translation.
5. Verify it appears as active in Installed Plugins.
6. Test Elementor template strings, Rank Math settings, forms and custom fields.

Do not activate it on production until the staging compatibility test passes.

## Required access

- Signed-in CloudPanel browser session, normally reached through the server's configured CloudPanel hostname or `https://SERVER_IP:8443`
- Signed-in Hetzner Cloud Console session for the server snapshot
- An off-site backup destination and its securely entered credentials
- DNS access for the staging subdomain

