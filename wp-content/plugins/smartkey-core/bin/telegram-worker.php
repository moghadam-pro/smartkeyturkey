<?php

if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }

$wp_load = getenv( 'SMARTKEY_WP_LOAD' );
if ( ! $wp_load || ! is_file( $wp_load ) ) {
	fwrite( STDERR, "SMARTKEY_WP_LOAD must point to the site's wp-load.php.\n" );
	exit( 1 );
}

require_once $wp_load;

try {
	\SmartKeyTurkey\Core\Telegram_Bot::run();
} catch ( Throwable $error ) {
	fwrite( STDERR, '[' . gmdate( 'c' ) . '] ' . $error->getMessage() . "\n" );
	exit( 1 );
}
