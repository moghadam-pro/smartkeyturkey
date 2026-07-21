<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Site_Chrome {
	private const INSTAGRAM = 'https://www.instagram.com/smartkeyturkey/';
	private const LINKEDIN  = 'https://www.linkedin.com/company/smartkeyturkey/';
	private const DEVELOPER = 'https://moghadam.pro/';

	public static function init(): void {
		add_action( 'init', array( self::class, 'migrate_brand_name' ), 40 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ), 25 );
		add_action( 'wp_body_open', array( self::class, 'render_header' ), 5 );
		add_action( 'wp_footer', array( self::class, 'render_footer' ), 5 );
		add_filter( 'body_class', array( self::class, 'body_class' ) );
	}

	public static function enqueue_assets(): void {
		wp_enqueue_style( 'smartkey-site-chrome', plugins_url( 'assets/css/site-chrome.css', SKT_CORE_FILE ), array(), SKT_CORE_VERSION );
	}

	public static function body_class( array $classes ): array {
		$classes[] = 'skt-custom-chrome';
		return $classes;
	}

	public static function render_header(): void {
		$icon = get_site_icon_url( 128 );
		$wordmark = plugins_url( 'assets/images/skt-wordmark.svg', SKT_CORE_FILE );
		?>
		<header class="skt-site-header" aria-label="<?php esc_attr_e( 'Site header', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell">
				<a class="skt-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-core' ); ?>">
					<?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" width="42" height="42" alt=""><?php endif; ?>
					<img class="skt-wordmark" src="<?php echo esc_url( $wordmark ); ?>" width="210" height="22" alt="SmartKeyTurkey">
				</a>
				<nav class="skt-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'smartkey-core' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smartkey-core' ); ?></a>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'Properties', 'smartkey-core' ); ?></a>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Petrochemicals', 'smartkey-core' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Insights', 'smartkey-core' ); ?></a>
				</nav>
				<a class="skt-header-cta" href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) . '#request-quote' ); ?>"><?php esc_html_e( 'Request a Quote', 'smartkey-core' ); ?></a>
			</div>
		</header>
		<?php
	}

	public static function render_footer(): void {
		$icon = get_site_icon_url( 128 );
		$wordmark = plugins_url( 'assets/images/skt-wordmark.svg', SKT_CORE_FILE );
		?>
		<footer class="skt-site-footer" aria-label="<?php esc_attr_e( 'Site footer', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell skt-footer-grid">
				<div class="skt-footer-intro">
					<a class="skt-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-core' ); ?>"><?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" width="38" height="38" alt=""><?php endif; ?><img class="skt-footer-wordmark" src="<?php echo esc_url( $wordmark ); ?>" width="210" height="22" alt="SmartKeyTurkey"></a>
					<p><?php esc_html_e( 'Property discovery and petrochemical sourcing coordination across Turkey.', 'smartkey-core' ); ?></p>
					<p class="skt-footer-disclosure"><?php esc_html_e( 'SmartKey acts as an intermediary and authorized sales representative; it is not the property owner or product manufacturer.', 'smartkey-core' ); ?></p>
				</div>
				<div><h2><?php esc_html_e( 'Explore', 'smartkey-core' ); ?></h2><ul><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'Properties', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Petrochemical Products', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'News & Insights', 'smartkey-core' ); ?></a></li></ul></div>
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
		if ( '1' === get_option( 'skt_brand_name_compact_migration' ) ) {
			return;
		}

		global $wpdb;
		$replace = static function ( $value ) use ( &$replace ) {
			if ( is_string( $value ) ) {
				return str_replace( 'SmartKeyTurkey', 'SmartKeyTurkey', $value );
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $item ) {
					$value[ $key ] = $replace( $item );
				}
			}
			return $value;
		};

		$posts = $wpdb->get_results( "SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_title LIKE '%SmartKeyTurkey%' OR post_content LIKE '%SmartKeyTurkey%' OR post_excerpt LIKE '%SmartKeyTurkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $posts as $post ) {
			wp_update_post( array( 'ID' => (int) $post->ID, 'post_title' => $replace( $post->post_title ), 'post_content' => $replace( $post->post_content ), 'post_excerpt' => $replace( $post->post_excerpt ) ) );
		}

		$meta_rows = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%SmartKeyTurkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $meta_rows as $row ) {
			update_metadata_by_mid( 'post', (int) $row->meta_id, $replace( maybe_unserialize( $row->meta_value ) ) );
		}

		$option_names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_value LIKE '%SmartKeyTurkey%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $option_names as $option_name ) {
			update_option( $option_name, $replace( get_option( $option_name ) ) );
		}
		update_option( 'blogname', 'SmartKeyTurkey' );
		update_option( 'skt_brand_name_compact_migration', '1', false );
	}
}
