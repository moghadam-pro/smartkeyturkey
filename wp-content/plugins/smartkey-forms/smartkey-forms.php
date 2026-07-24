<?php
/**
 * Plugin Name: SmartKey Forms
 * Description: Form building, submission storage and notification integrations for SmartKeyTurkey.
 * Version: 0.1.0
 * Author: SmartKeyTurkey
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: smartkey-forms
 */

defined( 'ABSPATH' ) || exit;

define( 'SKF_VERSION', '0.1.0' );
define( 'SKF_FILE', __FILE__ );
define( 'SKF_DIR', plugin_dir_path( __FILE__ ) );

require_once SKF_DIR . 'includes/class-form-manager.php';
require_once SKF_DIR . 'includes/class-submission-manager.php';

SmartKeyTurkey\Forms\Form_Manager::init();
SmartKeyTurkey\Forms\Submission_Manager::init();

/**
 * Public integration API for site-owned plugins and themes.
 */
function smartkey_forms_store_submission( string $type, string $title, array $data, int $related_id = 0, int $form_id = 0 ): int {
	return SmartKeyTurkey\Forms\Submission_Manager::store( $type, $title, $data, $related_id, $form_id );
}

register_activation_hook(
	__FILE__,
	static function (): void {
		SmartKeyTurkey\Forms\Form_Manager::register_post_type();
		SmartKeyTurkey\Forms\Submission_Manager::register_post_type();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
