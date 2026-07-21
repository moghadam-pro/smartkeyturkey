# Production change protocol

Effective 2026-07-20 by owner decision. SmartKeyTurkey is being developed directly on production without staging.

## Before each change

1. Confirm the latest Hetzner backup is available.
2. Define one isolated change and its rollback.
3. Record the current state without secrets.
4. Do not update or delete plugins.

## Apply and verify

1. Make only the defined change.
2. Test homepage, WordPress admin and the directly affected feature.
3. Check the latest NGINX and PHP-FPM logs.
4. Revert immediately if a critical error appears.
5. Record the result in Git and the daily Slack update.

## File ownership guardrail

Production site user: `smartamin`.

After editing a production file through a privileged account, confirm its owner and permissions. In particular, `wp-config.php` must remain readable by the PHP-FPM site user. On 2026-07-20, an ownership change caused HTTP 500 and was resolved by restoring the correct site-user ownership and restrictive file permissions. Production account names and exact access configuration are intentionally excluded from this public repository.

## Bulk operations

Do not run bulk imports, search/replace, mass translation or schema migrations until:

- a fresh backup exists;
- the import is idempotent or has a tested rollback;
- a small pilot batch passes review;
- external email, analytics and webhooks are understood.

## Abandoned staging resources

The following resources were created but must remain empty of production data unless the owner reverses the decision:

- `staging.smartkeyturkey.com`
- Site user `smartkeystg`
- Database `smartkeyturkey-staging`
