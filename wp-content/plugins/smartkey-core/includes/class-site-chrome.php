<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Site_Chrome {
	private const INSTAGRAM = 'https://www.instagram.com/smartkeyturkey/';
	private const LINKEDIN  = 'https://www.linkedin.com/company/smartkeyturkey/';
	private const DEVELOPER = 'https://moghadam.pro/';

	public static function init(): void {
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
		?>
		<header class="skt-site-header" aria-label="<?php esc_attr_e( 'Site header', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell">
				<a class="skt-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKey Turkey home', 'smartkey-core' ); ?>">
					<?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" width="42" height="42" alt=""><?php endif; ?>
					<span><strong>SmartKey</strong><small>Turkey</small></span>
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
		?>
		<footer class="skt-site-footer" aria-label="<?php esc_attr_e( 'Site footer', 'smartkey-core' ); ?>">
			<div class="skt-chrome-shell skt-footer-grid">
				<div class="skt-footer-intro">
					<p class="skt-footer-brand">SmartKey <span>Turkey</span></p>
					<p><?php esc_html_e( 'Property discovery and petrochemical sourcing coordination across Turkey.', 'smartkey-core' ); ?></p>
					<p class="skt-footer-disclosure"><?php esc_html_e( 'SmartKey acts as an intermediary and authorized sales representative; it is not the property owner or product manufacturer.', 'smartkey-core' ); ?></p>
				</div>
				<div><h2><?php esc_html_e( 'Explore', 'smartkey-core' ); ?></h2><ul><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'Properties', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Petrochemical Products', 'smartkey-core' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'News & Insights', 'smartkey-core' ); ?></a></li></ul></div>
				<div><h2><?php esc_html_e( 'Follow SmartKey', 'smartkey-core' ); ?></h2><ul><li><a href="<?php echo esc_url( self::INSTAGRAM ); ?>" target="_blank" rel="noopener noreferrer">Instagram <span aria-hidden="true">↗</span></a></li><li><a href="<?php echo esc_url( self::LINKEDIN ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn <span aria-hidden="true">↗</span></a></li></ul></div>
			</div>
			<div class="skt-chrome-shell skt-footer-bottom">
				<p>© 2012–2026 SmartKey Turkey. <?php esc_html_e( 'All rights reserved.', 'smartkey-core' ); ?></p>
				<p><?php esc_html_e( 'Designed and developed by', 'smartkey-core' ); ?> <a href="<?php echo esc_url( self::DEVELOPER ); ?>" target="_blank" rel="noopener noreferrer">Moghadam.pro</a></p>
			</div>
		</footer>
		<?php
	}
}
