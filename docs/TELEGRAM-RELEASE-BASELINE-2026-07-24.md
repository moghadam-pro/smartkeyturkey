# Telegram release baseline — 24 July 2026

This document is the single source of truth for continuing SmartKeyTurkey Telegram-bot work. It reconciles the repository, the four manually deployed release archives, the Persian handoff report, and the deployment log in `#smartkey-website`.

## Authoritative state

- Current and only deployable SmartKey Core release: **1.6.2**
- Authoritative source: `main` at or after commit `6fb24d5`
- Current documentation baseline: commit `0f43473`
- Production status reported by the completed deployment workflow: **1.6.2 active**
- Telegram service reported state: **enabled and active**
- Notification incident for requests `#427–#429`: **closed**
- Last reported notification queue: **empty**
- Current subscribed recipients reported by the handoff: two of the three authorized Telegram IDs

Older packages remain historical rollback artifacts. They must not be installed over 1.6.2:

- 1.5.0 — initial private Telegram operations bot
- 1.6.0 — redesigned request notifications and property workflow
- 1.6.1 — media, location, draft-detail and UX refinements
- 1.6.2 — safe API error reporting, cover `file_id` caching, text fallback and bounded retries

## Artifact audit

All supplied archives:

- contain one `smartkey-core/` root;
- contain no absolute path or parent-directory traversal entry;
- declare a plugin version and stable tag matching the archive name;
- passed a targeted scan for embedded Telegram tokens and private keys.

| Artifact | SHA-256 | Files | Role |
| --- | --- | ---: | --- |
| `smartkey-core-1.5.0.tar.gz` | `48e441fa500c9f0e0a7815e028e31523ea7dabc57aae23012fbe002f7326e6a4` | 39 | Historical |
| `smartkey-core-1.6.0.tar.gz` | `2e97ab4ffc186d06f121522b7684911397b3670dad664ba9d7658dadafdb5ea8` | 40 | Historical |
| `smartkey-core-1.6.1.tar.gz` | `7e06c51b4c061d72e662ed1c6234ee4d4ad6fc677b27568527195ad5a6035504` | 40 | Historical |
| `smartkey-core-1.6.2.tar.gz` | `2dd24badcbfd8af515d0e550e73930a31851ec59f3b604824e353f78b21cc9d6` | 40 | Current release |

The extracted `smartkey-core-1.6.2.tar.gz` tree is file-for-file identical to `wp-content/plugins/smartkey-core/` in the repository at the time of this audit.

Release archives are intentionally not committed to the public repository. This avoids repository bloat and accidental use of an obsolete binary. The hashes above identify the reviewed local artifacts.

## Evidence reconciliation

Three independent records agree on the final state:

1. Git history contains the implementation sequence through `6fb24d5`, followed by roadmap and documentation updates.
2. The Slack deployment thread records package hashes, server-side validation, each production deployment, the 1.6.2 incident recovery, and successful recipient-specific delivery without duplicates.
3. `TELEGRAM-BOT-HANDOFF-2026-07-24-FA.md` reports the same commit chain, hashes, service state, queue recovery, backups and remaining optional work.

The earlier Slack message that called the merged result “1.5.0” is superseded by the correction posted on 24 July 2026 and by this baseline. It described stale documentation, not the deployed code.

## Production facts carried forward

- WordPress root: `/home/smartamin/htdocs/smartkeyturkey.com`
- Plugin path: `/home/smartamin/htdocs/smartkeyturkey.com/wp-content/plugins/smartkey-core`
- PHP CLI: `/usr/bin/php`
- Service: `smartkey-telegram.service`
- Secret environment file: `/etc/smartkey/telegram.env`
- The real bot token remains server-only and must never be printed, copied into Slack, or committed.
- Production is not a Git checkout; deploy from a reviewed archive with backup, staging extraction, validation and rollback.

These are operational paths, not credentials. Before the next deployment, verify them again instead of assuming server state is unchanged.

## Safe continuation rule

For every future Telegram change:

1. Start from the current `main` and SmartKey Core 1.6.2.
2. Increase the plugin version and changelog deliberately.
3. Run PHP syntax, whitespace and secret checks.
4. Build one new release artifact and record its SHA-256.
5. Compare the artifact against the intended source tree.
6. Back up production and extract the new package outside the web root.
7. Validate the staged package before replacing the live plugin.
8. Restart and verify the worker, WordPress bootstrap and key public URLs.
9. Test notification delivery without creating recipient duplicates.
10. Update this baseline, README, roadmap/change log and the existing Slack deployment thread.

## Optional follow-up

- Submit one controlled real request and confirm immediate delivery to both current subscribers.
- Review the service journal for new Telegram API failures.
- Decide whether draft test property `#402` should be retained.
- Remove older server rollback copies only after a fresh inventory and explicit approval.
- Add the third authorized user as a subscriber only if requested and after that user sends `/start`.
- Treat larger-than-20-MB Telegram video support and property edit/delete as separate, security-reviewed scopes.

