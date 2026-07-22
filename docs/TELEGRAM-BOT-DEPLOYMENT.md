# SmartKeyTurkey Telegram bot deployment

The bot is part of SmartKey Core and uses WordPress APIs. It does not open a port, run a web server, alter Nginx/PHP-FPM, or connect directly to MariaDB, so it does not conflict with CloudPanel.

## Security first

The original token was disclosed and must be revoked in BotFather. Generate a replacement and use it only in `/etc/smartkey/telegram.env`. Never commit the real token.

## Files and location

Deploy the repository plugin to the existing site at:

`/home/smartamin/htdocs/smartkeyturkey.com/wp-content/plugins/smartkey-core/`

The production CloudPanel site user is `smartamin`, the WordPress root is `/home/smartamin/htdocs/smartkeyturkey.com`, and the confirmed PHP 8.2 CLI executable is `/usr/bin/php`.

## Installation commands

Run as root after deploying the updated plugin. The commands below use the confirmed production paths:

```bash
install -d -m 750 /etc/smartkey
install -m 600 deploy/telegram/smartkey-telegram.env.example /etc/smartkey/telegram.env
nano /etc/smartkey/telegram.env
install -m 644 deploy/telegram/smartkey-telegram.service /etc/systemd/system/smartkey-telegram.service
systemctl daemon-reload
systemctl enable --now smartkey-telegram.service
systemctl status smartkey-telegram.service
```

Edit the environment file with the rotated token and verify the actual `wp-load.php` path. If the CloudPanel site user is not `cloudpanel`, edit the service before starting it.

## Operations

```bash
journalctl -u smartkey-telegram.service -f
systemctl restart smartkey-telegram.service
systemctl stop smartkey-telegram.service
```

`Restart=always` restarts the worker after failure. `systemctl enable` starts it automatically after a server reboot.

## Access and behavior

Only Telegram numeric IDs `55906253`, `499185195`, and `85074725` receive responses. Usernames are intentionally not used for authorization because they can be changed. Unauthorized users receive no response.

The English menu supports:

- concise illustrated notifications for new website requests, with full details on demand;
- guided single-message draft property creation with dynamic city and property-type choices;
- batch/multi-select photo and video intake followed by **Finish Media**;
- listing draft properties, publishing a draft and finding a property by ID to return it to Draft.

Authorized users receive notifications only after sending `/start`. With Telegram's hosted Bot API, downloadable media is limited to 20 MB; accepted videos must also be no longer than 59 seconds. Supporting larger videos requires a separately operated local Bot API server.

New properties start as Draft. The first received image is the featured image and every image ID is stored in `skt_property_gallery_ids`.
