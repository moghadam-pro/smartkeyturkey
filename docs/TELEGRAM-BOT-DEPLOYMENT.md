# SmartKeyTurkey Telegram bot deployment

The bot is part of SmartKey Core and uses WordPress APIs. It does not open a port, run a web server, alter Nginx/PHP-FPM, or connect directly to MariaDB, so it does not conflict with CloudPanel.

## Security first

The original token was disclosed and must be revoked in BotFather. Generate a replacement and use it only in `/etc/smartkey/telegram.env`. Never commit the real token.

## Files and location

Deploy the repository plugin to the existing site at:

`/home/cloudpanel/htdocs/smartkeyturkey.com/wp-content/plugins/smartkey-core/`

If the CloudPanel site user or document root differs, replace both paths and the `User`/`Group` values in the service file. Find the real site user with `stat -c '%U:%G' /home/cloudpanel/htdocs/smartkeyturkey.com/wp-load.php`.
Also confirm the PHP CLI path with `command -v php`; update `ExecStart` if CloudPanel uses a versioned path such as `/usr/bin/php8.3`.

## Installation commands

Run as root after deploying the updated plugin:

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

- viewing the 10 newest website requests and receiving new-request notifications;
- guided draft property creation with all structured property fields;
- batch/multi-select photo intake followed by **Finish Photos**;
- setting a property to Published or Draft.

New properties start as Draft. The first received image is the featured image and every image ID is stored in `skt_property_gallery_ids`.
