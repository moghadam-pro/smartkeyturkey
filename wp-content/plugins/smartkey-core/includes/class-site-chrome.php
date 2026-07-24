<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Site_Chrome {
	private const INSTAGRAM = 'https://www.instagram.com/smartkeyturkey/';
	private const LINKEDIN  = 'https://www.linkedin.com/company/smartkeyturkey/';
	private const DEVELOPER = 'https://moghadam.pro/';
	private const MAPS      = 'https://maps.app.goo.gl/zkBfCS655RkLEPd7A';
	private const PHONE     = '+905050887188';

	public static function init(): void {
		add_action( 'after_setup_theme', array( self::class, 'register_menus' ) );
		add_action( 'init', array( self::class, 'maybe_seed_menus' ), 42 );
		add_action( 'init', array( self::class, 'migrate_brand_name' ), 40 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ), 100 );
		add_action( 'wp_body_open', array( self::class, 'render_header' ), 5 );
		add_action( 'wp_footer', array( self::class, 'render_footer' ), 5 );
		add_filter( 'body_class', array( self::class, 'body_class' ) );
		add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
		add_filter( 'wp_resource_hints', array( self::class, 'resource_hints' ), 10, 2 );
	}

	public static function register_menus(): void {
		register_nav_menus(
			array(
				'skt-primary' => __( 'SmartKey Primary Navigation', 'smartkey-core' ),
				'skt-footer'  => __( 'SmartKey Footer Navigation', 'smartkey-core' ),
			)
		);
	}

	public static function maybe_seed_menus(): void {
		if ( '1' === get_option( 'skt_native_navigation_version' ) ) {
			return;
		}
		$items = array(
			'Home'            => home_url( '/' ),
			'Properties'      => get_post_type_archive_link( 'skt_property' ),
			'Petrochemicals'  => get_post_type_archive_link( 'skt_product' ),
			'Attractions'     => get_post_type_archive_link( 'skt_attraction' ),
			'News & Insights' => home_url( '/blog/' ),
			'About Us'        => home_url( '/about-us/' ),
		);
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		foreach ( array( 'skt-primary' => 'SmartKey Primary', 'skt-footer' => 'SmartKey Footer' ) as $location => $name ) {
			$menu = wp_get_nav_menu_object( $name );
			$id   = $menu ? (int) $menu->term_id : wp_create_nav_menu( $name );
			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}
			$id = (int) $id;
			if ( ! wp_get_nav_menu_items( $id ) ) {
				foreach ( $items as $label => $url ) {
					if ( $url ) {
						wp_update_nav_menu_item( $id, 0, array( 'menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish' ) );
					}
				}
			}
			$locations[ $location ] = $id;
		}
		set_theme_mod( 'nav_menu_locations', $locations );
		update_option( 'skt_native_navigation_version', '1', false );
	}

	public static function enqueue_assets(): void {
		wp_enqueue_style( 'smartkey-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto:wght@400;500;700&display=swap', array(), null );
		wp_enqueue_style( 'smartkey-design-tokens', plugins_url( 'assets/css/design-tokens.css', SKT_CORE_FILE ), array( 'smartkey-fonts' ), SKT_CORE_VERSION );
		wp_enqueue_style( 'smartkey-site-chrome', plugins_url( 'assets/css/site-chrome.css', SKT_CORE_FILE ), array( 'smartkey-design-tokens' ), SKT_CORE_VERSION );
		wp_enqueue_script( 'smartkey-site-chrome', plugins_url( 'assets/js/site-chrome.js', SKT_CORE_FILE ), array(), SKT_CORE_VERSION, true );
	}

	public static function resource_hints( array $urls, string $relation_type ): array {
		if ( 'preconnect' === $relation_type ) {
			$urls[] = array( 'href' => 'https://fonts.googleapis.com', 'crossorigin' => 'anonymous' );
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
		}
		return $urls;
	}

	public static function body_class( array $classes ): array {
		$classes[] = 'skt-custom-chrome';
		return $classes;
	}

	public static function render_header(): void {
		$icon = plugins_url( 'assets/images/skt-mark.svg', SKT_CORE_FILE );
		$wordmark = plugins_url( 'assets/images/skt-wordmark.svg', SKT_CORE_FILE );
		?>
		<header class="skt-site-header" aria-label="<?php esc_attr_e( 'Site header', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell">
				<a class="skt-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-core' ); ?>">
					<img src="<?php echo esc_url( $icon ); ?>" width="48" height="38" alt="">
					<img class="skt-wordmark" src="<?php echo esc_url( $wordmark ); ?>" width="210" height="22" alt="SmartKeyTurkey">
				</a>
				<button class="skt-menu-toggle" type="button" aria-expanded="false" aria-controls="skt-primary-nav"><span class="skt-menu-toggle-label"><?php esc_html_e( 'Menu', 'smartkey-core' ); ?></span><span class="skt-menu-toggle-icon" aria-hidden="true"><i></i><i></i><i></i></span></button>
				<?php wp_nav_menu( array( 'theme_location' => 'skt-primary', 'container' => 'nav', 'container_id' => 'skt-primary-nav', 'container_class' => 'skt-primary-nav', 'menu_class' => 'skt-primary-menu', 'fallback_cb' => false, 'depth' => 2 ) ); ?>
				<a class="skt-header-cta" href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) . '#request-quote' ); ?>"><?php esc_html_e( 'Request a Quote', 'smartkey-core' ); ?></a>
			</div>
		</header>
		<?php
	}

	public static function render_footer(): void {
		$icon = plugins_url( 'assets/images/skt-mark.svg', SKT_CORE_FILE );
		$wordmark = plugins_url( 'assets/images/skt-wordmark.svg', SKT_CORE_FILE );
		?>
		<footer class="skt-site-footer" aria-label="<?php esc_attr_e( 'Site footer', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell skt-footer-grid">
				<div class="skt-footer-intro">
					<a class="skt-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-core' ); ?>"><img src="<?php echo esc_url( $icon ); ?>" width="48" height="38" alt=""><img class="skt-footer-wordmark" src="<?php echo esc_url( $wordmark ); ?>" width="210" height="22" alt="SmartKeyTurkey"></a>
					<p><?php esc_html_e( 'Property discovery and petrochemical sourcing coordination across Turkey.', 'smartkey-core' ); ?></p>
					<p class="skt-footer-disclosure"><?php esc_html_e( 'SmartKeyTurkey works directly with properties and projects under its control. For petrochemicals, it acts as an authorized sales representative and is not the manufacturer.', 'smartkey-core' ); ?></p>
				</div>
				<div><h2><?php esc_html_e( 'Explore', 'smartkey-core' ); ?></h2><?php wp_nav_menu( array( 'theme_location' => 'skt-footer', 'container' => false, 'menu_class' => 'skt-footer-menu', 'fallback_cb' => false, 'depth' => 1 ) ); ?></div>
				<div class="skt-footer-contact"><h2><?php esc_html_e( 'Contact', 'smartkey-core' ); ?></h2><address>Cumhuriyet Mah. Gurpinar Yolu Street - Beykent - B. Cekmece<br>ERESIN YASAM MERKEZi No:10 A Block, 6th floor, office 112</address><p><a href="tel:<?php echo esc_attr( self::PHONE ); ?>">+90 505 088 71 88</a></p><a class="skt-footer-map-link" href="<?php echo esc_url( self::MAPS ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open in Google Maps', 'smartkey-core' ); ?> ↗</a></div>
				<div><h2><?php esc_html_e( 'Follow SmartKey', 'smartkey-core' ); ?></h2><ul><li><a href="<?php echo esc_url( self::INSTAGRAM ); ?>" target="_blank" rel="noopener noreferrer">Instagram <span aria-hidden="true">↗</span></a></li><li><a href="<?php echo esc_url( self::LINKEDIN ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn <span aria-hidden="true">↗</span></a></li></ul></div>
			</div>
			<div class="skt-chrome-shell skt-footer-bottom">
				<p>© 2012–2026 SmartKeyTurkey. <?php esc_html_e( 'All rights reserved.', 'smartkey-core' ); ?></p>
				<p><?php esc_html_e( 'Designed and developed by', 'smartkey-core' ); ?> <a href="<?php echo esc_url( self::DEVELOPER ); ?>" target="_blank" rel="noopener noreferrer">Moghadam.pro</a></p>
			</div>
		</footer>
		<?php
	}

	public static function migrate_brand_name(): void {
		if ( '2' === get_option( 'skt_brand_name_compact_migration' ) ) {
			return;
		}

		global $wpdb;
		$replace = static function ( $value ) use ( &$replace ) {
			if ( is_string( $value ) ) {
				return str_replace( 'SmartKey Turkey', 'SmartKeyTurkey', $value );
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $item ) {
					$value[ $key ] = $replace( $item );
				}
			}
			return $value;
		};

		$posts = $wpdb->get_results( "SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_title LIKE '%SmartKey Turkey%' OR post_content LIKE '%SmartKey Turkey%' OR post_excerpt LIKE '%SmartKey Turkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $posts as $post ) {
			wp_update_post( array( 'ID' => (int) $post->ID, 'post_title' => $replace( $post->post_title ), 'post_content' => $replace( $post->post_content ), 'post_excerpt' => $replace( $post->post_excerpt ) ) );
		}

		$meta_rows = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%SmartKey Turkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $meta_rows as $row ) {
			update_metadata_by_mid( 'post', (int) $row->meta_id, $replace( maybe_unserialize( $row->meta_value ) ) );
		}

		$option_names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_value LIKE '%SmartKey Turkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $option_names as $option_name ) {
			update_option( $option_name, $replace( get_option( $option_name ) ) );
		}
		update_option( 'blogname', 'SmartKeyTurkey' );
		update_option( 'skt_brand_name_compact_migration', '2', false );
	}
}
