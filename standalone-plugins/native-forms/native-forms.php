<?php
/**
 * Plugin Name: Native Forms
 * Description: Lightweight form builder with private WordPress submission storage and RTL support.
 * Version: 1.0.0
 * Author: Moghadam.pro
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: native-forms
 */

defined( 'ABSPATH' ) || exit;

define( 'NATIVE_FORMS_VERSION', '1.0.0' );
define( 'NATIVE_FORMS_FILE', __FILE__ );
define( 'NATIVE_FORMS_DIR', plugin_dir_path( __FILE__ ) );

require_once NATIVE_FORMS_DIR . 'includes/class-form-manager.php';
require_once NATIVE_FORMS_DIR . 'includes/class-submission-manager.php';

\NativeForms\Form_Manager::init();
\NativeForms\Submission_Manager::init();

function native_forms_store_submission( string $type, string $title, array $data, int $form_id = 0 ): int {
	return \NativeForms\Submission_Manager::store( $type, $title, $data, $form_id );
}

register_activation_hook(
	__FILE__,
	static function (): void {
		\NativeForms\Form_Manager::register_post_type();
		\NativeForms\Submission_Manager::register_post_type();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
