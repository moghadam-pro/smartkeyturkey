<?php
/**
 * Plugin Name: SmartKey Core
 * Description: Structured product catalog and controlled product importer for SmartKeyTurkey.
 * Version: 0.9.0
 * Author: SmartKeyTurkey
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: smartkey-core
 */

defined( 'ABSPATH' ) || exit;

define( 'SKT_CORE_VERSION', '0.9.0' );
define( 'SKT_CORE_FILE', __FILE__ );
define( 'SKT_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once SKT_CORE_DIR . 'includes/class-product-catalog.php';
require_once SKT_CORE_DIR . 'includes/class-product-importer.php';
require_once SKT_CORE_DIR . 'includes/class-dashboard.php';
require_once SKT_CORE_DIR . 'includes/class-product-frontend.php';
require_once SKT_CORE_DIR . 'includes/class-site-chrome.php';
require_once SKT_CORE_DIR . 'includes/class-property-catalog.php';
require_once SKT_CORE_DIR . 'includes/class-property-frontend.php';
require_once SKT_CORE_DIR . 'includes/class-admin-menu.php';

SmartKeyTurkey\Core\Product_Catalog::init();
SmartKeyTurkey\Core\Product_Importer::init();
SmartKeyTurkey\Core\Dashboard::init();
SmartKeyTurkey\Core\Product_Frontend::init();
SmartKeyTurkey\Core\Site_Chrome::init();
SmartKeyTurkey\Core\Property_Catalog::init();
SmartKeyTurkey\Core\Property_Frontend::init();
SmartKeyTurkey\Core\Admin_Menu::init();

register_activation_hook(
	__FILE__,
	static function (): void {
		SmartKeyTurkey\Core\Product_Catalog::register_content_types();
		SmartKeyTurkey\Core\Property_Catalog::register_content_types();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);
