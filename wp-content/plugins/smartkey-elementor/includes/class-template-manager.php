<?php

namespace SmartKeyTurkey\Elementor;

defined( 'ABSPATH' ) || exit;

final class Template_Manager {
	private const VERSION = '5';

	public static function init(): void {
		add_action( 'init', array( self::class, 'seed' ), 80 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ), 90 );
		add_action( 'admin_menu', array( self::class, 'register_admin_page' ), 30 );
		add_action( 'admin_post_ske_toggle_chrome', array( self::class, 'toggle_chrome' ) );
	}

	public static function register_admin_page(): void {
		add_submenu_page( 'smartkey', 'Elementor Migration', 'Elementor Migration', 'manage_options', 'ske-migration', array( self::class, 'render_admin_page' ) );
	}

	public static function render_admin_page(): void {
		$enabled = '1' === get_option( 'skt_elementor_chrome_enabled' );
		?>
		<div class="wrap">
			<h1>Elementor Migration</h1>
			<p>Global Header and Footer templates remain editable in Elementor Theme Builder.</p>
			<p><strong>Elementor chrome:</strong> <?php echo esc_html( $enabled ? 'Enabled' : 'Disabled — PHP fallback is active' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ske_toggle_chrome">
				<input type="hidden" name="enabled" value="<?php echo esc_attr( $enabled ? '0' : '1' ); ?>">
				<?php wp_nonce_field( 'ske_toggle_chrome' ); ?>
				<button class="button button-primary" type="submit"><?php echo esc_html( $enabled ? 'Use PHP fallback' : 'Use Elementor Header & Footer' ); ?></button>
			</form>
		</div>
		<?php
	}

	public static function toggle_chrome(): void {
		check_admin_referer( 'ske_toggle_chrome' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'smartkey-elementor' ) );
		}
		update_option( 'skt_elementor_chrome_enabled', isset( $_POST['enabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ? '1' : '0', false );
		wp_safe_redirect( admin_url( 'admin.php?page=ske-migration' ) );
		exit;
	}

	public static function enqueue(): void {
		if ( '1' === get_option( 'skt_elementor_chrome_enabled' ) ) {
			wp_enqueue_style( 'smartkey-elementor-chrome', plugins_url( 'assets/css/chrome.css', SKE_FILE ), array(), SKE_VERSION );
		}
	}

	public static function seed(): void {
		if ( self::VERSION === get_option( 'ske_templates_version' ) || ! post_type_exists( 'elementor_library' ) || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		$header = self::upsert( 'SmartKey — Global Header', 'header', self::header_data() );
		$footer = self::upsert( 'SmartKey — Global Footer', 'footer', self::footer_data() );
		if ( $header && $footer ) {
			update_option( 'ske_templates_version', self::VERSION, false );
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}
	}

	private static function upsert( string $title, string $type, array $data ): int {
		$ids = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => array( 'publish', 'draft' ), 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		$id  = $ids ? (int) $ids[0] : (int) wp_insert_post( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'post_title' => $title ) );
		if ( ! $id ) {
			return 0;
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', $type );
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}
		update_post_meta( $id, '_elementor_conditions', array( 'include/general' ) );
		wp_set_object_terms( $id, $type, 'elementor_library_type' );
		$conditions        = (array) get_option( 'elementor_pro_theme_builder_conditions', array() );
		$conditions[ $id ] = array( 'include/general' );
		update_option( 'elementor_pro_theme_builder_conditions', $conditions, false );
		return $id;
	}

	private static function widget( string $id, string $type, array $settings ): array {
		return array( 'id' => $id, 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
	}

	private static function container( string $id, array $settings, array $elements ): array {
		return array( 'id' => $id, 'elType' => 'container', 'isInner' => false, 'settings' => $settings, 'elements' => $elements );
	}

	private static function header_data(): array {
		$mark = content_url( '/plugins/smartkey-core/assets/images/skt-mark.svg' );
		$word = content_url( '/plugins/smartkey-core/assets/images/skt-wordmark.svg' );
		return array(
			self::container(
				'skeh0001',
				array( 'content_width' => 'full', 'css_classes' => 'ske-header', 'flex_direction' => 'row', 'justify_content' => 'space-between', 'align_items' => 'center' ),
				array(
					self::container(
						'skeh0002',
						array( 'content_width' => 'full', 'css_classes' => 'ske-brand', 'flex_direction' => 'row', 'align_items' => 'center' ),
						array(
							self::widget( 'skeh0003', 'image', array( 'image' => array( 'url' => $mark, 'id' => '' ), 'image_size' => 'full', 'link_to' => 'custom', 'link' => array( 'url' => home_url( '/' ) ) ) ),
							self::widget( 'skeh0004', 'image', array( 'image' => array( 'url' => $word, 'id' => '' ), 'image_size' => 'full', 'link_to' => 'custom', 'link' => array( 'url' => home_url( '/' ) ) ) ),
						)
					),
					self::widget( 'skeh0005', 'nav-menu', array( 'menu' => 'SmartKey Primary', 'layout' => 'horizontal', 'pointer' => 'underline', 'submenu_icon' => array( 'value' => '<i class="fas fa-caret-down"></i>', 'library' => 'fa-solid' ) ) ),
					self::widget( 'skeh0006', 'button', array( 'text' => 'Request a Quote', 'link' => array( 'url' => get_post_type_archive_link( 'skt_product' ) . '#request-quote' ), 'css_classes' => 'ske-header-cta' ) ),
				)
			),
		);
	}

	private static function footer_data(): array {
		$mark = content_url( '/plugins/smartkey-core/assets/images/skt-mark.svg' );
		$word = content_url( '/plugins/smartkey-core/assets/images/skt-wordmark.svg' );
		return array(
			self::container(
				'skef0001',
				array( 'content_width' => 'full', 'css_classes' => 'ske-footer' ),
				array(
					self::container(
						'skef0002',
						array( 'content_width' => 'boxed', 'css_classes' => 'ske-footer-grid', 'flex_direction' => 'row' ),
						array(
							self::container(
								'skef0003',
								array( 'css_classes' => 'ske-footer-column ske-footer-intro' ),
								array(
									self::container( 'skef0004', array( 'css_classes' => 'ske-footer-brand', 'flex_direction' => 'row' ), array(
										self::widget( 'skef0005', 'image', array( 'image' => array( 'url' => $mark, 'id' => '' ), 'image_size' => 'full' ) ),
										self::widget( 'skef0006', 'image', array( 'image' => array( 'url' => $word, 'id' => '' ), 'image_size' => 'full' ) ),
									) ),
									self::widget( 'skef0007', 'text-editor', array( 'editor' => '<p>Property discovery and petrochemical sourcing coordination across Turkey.</p><p><small>SmartKeyTurkey works directly with properties and projects under its control. For petrochemicals, it acts as an authorized sales representative and is not the manufacturer.</small></p>' ) ),
								)
							),
							self::container( 'skef0008', array( 'css_classes' => 'ske-footer-column' ), array(
								self::widget( 'skef0009', 'heading', array( 'title' => 'Explore', 'header_size' => 'h2' ) ),
								self::widget( 'skef0010', 'nav-menu', array( 'menu' => 'SmartKey Footer', 'layout' => 'vertical', 'pointer' => 'none' ) ),
							) ),
							self::container( 'skef0011', array( 'css_classes' => 'ske-footer-column' ), array(
								self::widget( 'skef0012', 'heading', array( 'title' => 'Contact', 'header_size' => 'h2' ) ),
								self::widget( 'skef0013', 'text-editor', array( 'editor' => '<p>Cumhuriyet Mah. Gurpinar Yolu Street - Beykent - B. Cekmece<br>ERESIN YASAM MERKEZi No:10 A Block, 6th floor, office 112</p><p><a href="tel:+905050887188">+90 505 088 71 88</a></p><p><a href="https://maps.app.goo.gl/zkBfCS655RkLEPd7A" target="_blank" rel="noopener">Open in Google Maps ↗</a></p>' ) ),
							) ),
							self::container( 'skef0014', array( 'css_classes' => 'ske-footer-column' ), array(
								self::widget( 'skef0015', 'heading', array( 'title' => 'Follow SmartKey', 'header_size' => 'h2' ) ),
								self::widget( 'skef0016', 'social-icons', array( 'social_icon_list' => array(
									array( '_id' => 'ig001', 'social_icon' => array( 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ), 'link' => array( 'url' => 'https://www.instagram.com/smartkeyturkey/', 'is_external' => true ) ),
									array( '_id' => 'li001', 'social_icon' => array( 'value' => 'fab fa-linkedin-in', 'library' => 'fa-brands' ), 'link' => array( 'url' => 'https://www.linkedin.com/company/smartkeyturkey/', 'is_external' => true ) ),
								) ) ),
							) ),
						)
					),
					self::container( 'skef0017', array( 'content_width' => 'boxed', 'css_classes' => 'ske-footer-bottom', 'flex_direction' => 'row', 'justify_content' => 'space-between' ), array(
						self::widget( 'skef0018', 'text-editor', array( 'editor' => '<p>© 2012–2026 SmartKeyTurkey. All rights reserved.</p>' ) ),
						self::widget( 'skef0019', 'text-editor', array( 'editor' => '<p>Designed and developed by <a href="https://moghadam.pro/" target="_blank" rel="noopener">Moghadam.pro</a></p>' ) ),
					) ),
				)
			),
		);
	}
}
