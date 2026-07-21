<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Homepage {
	private const PAGE_VERSION = '1.0.0';

	public static function init(): void {
		add_shortcode( 'skt_home', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'optimize_homepage_assets' ), 100 );
		add_action( 'init', array( self::class, 'maybe_build_page' ), 45 );
		add_action( 'init', array( self::class, 'maybe_configure_seo' ), 46 );
	}

	public static function enqueue_assets(): void {
		if ( is_front_page() ) {
			wp_enqueue_style( 'smartkey-homepage', plugins_url( 'assets/css/homepage.css', SKT_CORE_FILE ), array( 'smartkey-design-tokens' ), SKT_CORE_VERSION );
		}
	}

	public static function optimize_homepage_assets(): void {
		if ( ! is_front_page() ) { return; }
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'contact-form-7' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
	}

	public static function render(): string {
		$properties = get_posts( array( 'post_type' => 'skt_property', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC' ) );
		$families   = get_terms( array( 'taxonomy' => 'skt_product_family', 'hide_empty' => true, 'number' => 6, 'orderby' => 'count', 'order' => 'DESC' ) );
		$insights   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 2, 'orderby' => 'date', 'order' => 'DESC' ) );
		$city_count = (int) wp_count_terms( array( 'taxonomy' => 'skt_property_city', 'hide_empty' => true ) );
		$products   = wp_count_posts( 'skt_product' );

		ob_start();
		?>
		<main class="skt-home" id="main-content">
			<section class="skt-home-hero"><div class="skt-home-shell skt-home-hero-grid">
				<div class="skt-home-hero-copy"><p class="skt-home-eyebrow"><?php esc_html_e( 'Property & petrochemical solutions in Turkey', 'smartkey-core' ); ?></p><h1><?php esc_html_e( 'Two specialist markets. One dependable partner.', 'smartkey-core' ); ?></h1><p><?php esc_html_e( 'Explore directly managed property opportunities and source petrochemical products through a clear, request-led process built around verified details.', 'smartkey-core' ); ?></p><div class="skt-home-actions"><a class="skt-home-button" href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'Explore properties', 'smartkey-core' ); ?></a><a class="skt-home-button skt-home-button-secondary" href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Browse products', 'smartkey-core' ); ?></a></div></div>
				<div class="skt-home-hero-panel" aria-label="<?php esc_attr_e( 'SmartKeyTurkey services', 'smartkey-core' ); ?>"><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><span>01</span><div><strong><?php esc_html_e( 'Properties', 'smartkey-core' ); ?></strong><small><?php esc_html_e( 'Sale and rental opportunities across priority Turkish cities', 'smartkey-core' ); ?></small></div><b aria-hidden="true">↗</b></a><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><span>02</span><div><strong><?php esc_html_e( 'Petrochemicals', 'smartkey-core' ); ?></strong><small><?php esc_html_e( 'Products, grades and qualified commercial inquiries', 'smartkey-core' ); ?></small></div><b aria-hidden="true">↗</b></a></div>
			</div></section>

			<section class="skt-home-proof" aria-label="<?php esc_attr_e( 'SmartKeyTurkey at a glance', 'smartkey-core' ); ?>"><div class="skt-home-shell"><div><strong><?php echo esc_html( max( 4, $city_count ) ); ?></strong><span><?php esc_html_e( 'priority cities', 'smartkey-core' ); ?></span></div><div><strong><?php echo esc_html( number_format_i18n( (int) ( $products->publish ?? 0 ) ) ); ?></strong><span><?php esc_html_e( 'published products', 'smartkey-core' ); ?></span></div><div><strong>2012</strong><span><?php esc_html_e( 'company foundation', 'smartkey-core' ); ?></span></div><div><strong><?php esc_html_e( 'On request', 'smartkey-core' ); ?></strong><span><?php esc_html_e( 'current commercial terms', 'smartkey-core' ); ?></span></div></div></section>

			<section class="skt-home-section"><div class="skt-home-shell"><div class="skt-home-heading"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'Choose your path', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Purpose-built journeys for buyers and businesses', 'smartkey-core' ); ?></h2></div><p><?php esc_html_e( 'Each market has its own information, qualification and inquiry process—without publishing unverified prices or generic promises.', 'smartkey-core' ); ?></p></div><div class="skt-home-paths"><article><span class="skt-home-path-index">B2C</span><h3><?php esc_html_e( 'Find a property in Turkey', 'smartkey-core' ); ?></h3><p><?php esc_html_e( 'Review location, configuration, construction details and current status for properties and projects managed directly by SmartKeyTurkey.', 'smartkey-core' ); ?></p><ul><li><?php esc_html_e( 'Sale and rental opportunities', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Istanbul, Ankara, Izmir and Antalya', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Documents and terms supplied on request', 'smartkey-core' ); ?></li></ul><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'View properties', 'smartkey-core' ); ?> →</a></article><article><span class="skt-home-path-index">B2B</span><h3><?php esc_html_e( 'Source petrochemical products', 'smartkey-core' ); ?></h3><p><?php esc_html_e( 'Browse product families and grades, then submit the technical, quantity and destination details required for a qualified offer.', 'smartkey-core' ); ?></p><ul><li><?php esc_html_e( 'Grade-level product discovery', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Technical and document requests', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Supplier-confirmed commercial terms', 'smartkey-core' ); ?></li></ul><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Browse catalogue', 'smartkey-core' ); ?> →</a></article></div></div></section>

			<?php if ( $properties ) : ?><section class="skt-home-section skt-home-section-muted"><div class="skt-home-shell"><div class="skt-home-heading"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'Current opportunities', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Featured properties', 'smartkey-core' ); ?></h2></div><a class="skt-home-text-link" href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'View all properties', 'smartkey-core' ); ?> →</a></div><div class="skt-home-property-grid"><?php foreach ( $properties as $property ) : $status = (string) get_post_meta( $property->ID, 'skt_property_listing_status', true ); $city = get_the_terms( $property->ID, 'skt_property_city' ); $media_label = in_array( $status, array( 'sold', 'rented' ), true ) ? sprintf( __( 'View %1$s — %2$s', 'smartkey-core' ), get_the_title( $property ), ucfirst( $status ) ) : sprintf( __( 'View %s', 'smartkey-core' ), get_the_title( $property ) ); ?><article><a class="skt-home-card-media" href="<?php echo esc_url( get_permalink( $property ) ); ?>" aria-label="<?php echo esc_attr( $media_label ); ?>"><?php if ( has_post_thumbnail( $property ) ) : echo get_the_post_thumbnail( $property->ID, 'medium_large', array( 'loading' => 'lazy', 'alt' => get_the_title( $property ) ) ); else : ?><b class="skt-home-card-placeholder" aria-hidden="true"></b><?php endif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( in_array( $status, array( 'sold', 'rented' ), true ) ) : ?><span aria-hidden="true"><?php echo esc_html( ucfirst( $status ) ); ?></span><?php endif; ?></a><div><p><?php echo esc_html( $city && ! is_wp_error( $city ) ? $city[0]->name : __( 'Turkey', 'smartkey-core' ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $property ) ); ?>"><?php echo esc_html( get_the_title( $property ) ); ?></a></h3><a href="<?php echo esc_url( get_permalink( $property ) ); ?>"><?php esc_html_e( 'View details', 'smartkey-core' ); ?> →</a></div></article><?php endforeach; ?></div></div></section><?php endif; ?>

			<?php if ( ! is_wp_error( $families ) && $families ) : ?><section class="skt-home-section"><div class="skt-home-shell"><div class="skt-home-heading"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'Industrial catalogue', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Explore product families', 'smartkey-core' ); ?></h2></div><a class="skt-home-text-link" href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Browse all products', 'smartkey-core' ); ?> →</a></div><div class="skt-home-family-grid"><?php foreach ( $families as $family ) : ?><a href="<?php echo esc_url( get_term_link( $family ) ); ?>"><strong><?php echo esc_html( $family->name ); ?></strong><span><?php echo esc_html( sprintf( _n( '%s product', '%s products', $family->count, 'smartkey-core' ), number_format_i18n( $family->count ) ) ); ?></span><b aria-hidden="true">→</b></a><?php endforeach; ?></div></div></section><?php endif; ?>

			<section class="skt-home-section skt-home-trust"><div class="skt-home-shell"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'A clearer way to proceed', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Information first. Commercial terms when the request is qualified.', 'smartkey-core' ); ?></h2></div><ol><li><span>01</span><div><strong><?php esc_html_e( 'Explore structured details', 'smartkey-core' ); ?></strong><p><?php esc_html_e( 'Compare consistent property facts or product specifications.', 'smartkey-core' ); ?></p></div></li><li><span>02</span><div><strong><?php esc_html_e( 'Send your requirements', 'smartkey-core' ); ?></strong><p><?php esc_html_e( 'Tell us your intended transaction, quantity, destination or timing.', 'smartkey-core' ); ?></p></div></li><li><span>03</span><div><strong><?php esc_html_e( 'Receive current information', 'smartkey-core' ); ?></strong><p><?php esc_html_e( 'Availability, documents and commercial terms are confirmed for the request.', 'smartkey-core' ); ?></p></div></li></ol></div></section>

			<?php if ( $insights ) : ?><section class="skt-home-section"><div class="skt-home-shell"><div class="skt-home-heading"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'News & guidance', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Latest insights', 'smartkey-core' ); ?></h2></div><a class="skt-home-text-link" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'View all insights', 'smartkey-core' ); ?> →</a></div><div class="skt-home-insights"><?php foreach ( $insights as $insight ) : ?><article><p><?php echo esc_html( get_the_date( 'F j, Y', $insight ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $insight ) ); ?>"><?php echo esc_html( get_the_title( $insight ) ); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt( $insight ), 24 ) ); ?></p><a href="<?php echo esc_url( get_permalink( $insight ) ); ?>"><?php esc_html_e( 'Read article', 'smartkey-core' ); ?> →</a></article><?php endforeach; ?></div></div></section><?php endif; ?>

			<section class="skt-home-cta"><div class="skt-home-shell"><div><p class="skt-home-eyebrow"><?php esc_html_e( 'Start with the right information', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Tell us what you are looking for.', 'smartkey-core' ); ?></h2><p><?php esc_html_e( 'Choose a property journey or submit a qualified petrochemical request. Our team will respond with the relevant next steps.', 'smartkey-core' ); ?></p></div><div class="skt-home-actions"><a class="skt-home-button" href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>"><?php esc_html_e( 'Find a property', 'smartkey-core' ); ?></a><a class="skt-home-button skt-home-button-secondary" href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) . '#request-quote' ); ?>"><?php esc_html_e( 'Request a quote', 'smartkey-core' ); ?></a></div></div></section>
		</main>
		<?php
		return (string) ob_get_clean();
	}

	public static function maybe_build_page(): void {
		if ( self::PAGE_VERSION === get_option( 'skt_homepage_version' ) ) { return; }
		$page_id = (int) get_option( 'page_on_front' );
		if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) { return; }
		if ( ! get_option( 'skt_homepage_content_backup' ) ) {
			update_option( 'skt_homepage_content_backup', array( 'content' => get_post_field( 'post_content', $page_id ), 'elementor_data' => get_post_meta( $page_id, '_elementor_data', true ), 'elementor_settings' => get_post_meta( $page_id, '_elementor_page_settings', true ) ), false );
		}
		wp_update_post( array( 'ID' => $page_id, 'post_title' => 'Home', 'post_content' => '[skt_home]' ) );
		$data = array( array( 'id' => 'e1a10001', 'elType' => 'container', 'isInner' => false, 'settings' => array( 'content_width' => 'full', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ) ), 'elements' => array( array( 'id' => 'e1a10002', 'elType' => 'widget', 'widgetType' => 'shortcode', 'settings' => array( 'shortcode' => '[skt_home]' ), 'elements' => array() ) ) ) );
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $page_id, '_elementor_page_settings', array( 'hide_title' => 'yes', 'page_layout' => 'elementor_full_width' ) );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_option( 'skt_homepage_version', self::PAGE_VERSION, false );
	}

	public static function maybe_configure_seo(): void {
		if ( self::PAGE_VERSION === get_option( 'skt_homepage_seo_version' ) ) { return; }
		$page_id = (int) get_option( 'page_on_front' );
		if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) { return; }
		update_post_meta( $page_id, 'rank_math_title', 'Property & Petrochemical Solutions in Turkey | SmartKeyTurkey' );
		update_post_meta( $page_id, 'rank_math_description', 'Explore directly managed properties in Istanbul, Ankara, Izmir and Antalya, or source petrochemical products through SmartKeyTurkey.' );
		update_post_meta( $page_id, 'rank_math_focus_keyword', 'property and petrochemical solutions Turkey' );
		update_option( 'skt_homepage_seo_version', self::PAGE_VERSION, false );
	}
}
