<?php
/**
 * Plugin Name: SmartKey Elementor
 * Description: Editable Elementor templates and focused dynamic widgets for SmartKeyTurkey.
 * Version: 0.2.0
 * Author: SmartKeyTurkey
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: smartkey-elementor
 */

defined( 'ABSPATH' ) || exit;

define( 'SKE_VERSION', '0.2.0' );
define( 'SKE_FILE', __FILE__ );
define( 'SKE_DIR', plugin_dir_path( __FILE__ ) );

require_once SKE_DIR . 'includes/class-template-manager.php';

SmartKeyTurkey\Elementor\Template_Manager::init();

register_deactivation_hook(
	__FILE__,
	static function (): void {
		update_option( 'skt_elementor_chrome_enabled', '0', false );
	}
);
