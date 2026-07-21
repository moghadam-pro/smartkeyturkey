<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Company_Pages {
	private const VERSION = '1.0.0';

	public static function init(): void {
		add_shortcode( 'skt_about', array( self::class, 'render_about' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'init', array( self::class, 'maybe_create_about' ), 48 );
		add_filter( 'template_include', array( self::class, 'route_404' ), 110 );
	}

	public static function enqueue_assets(): void {
		if ( is_page( 'about-us' ) || is_404() ) { wp_enqueue_style( 'smartkey-company-pages', plugins_url( 'assets/css/company-pages.css', SKT_CORE_FILE ), array( 'smartkey-design-tokens' ), SKT_CORE_VERSION ); }
	}

	public static function render_about(): string {
		ob_start(); ?>
		<main class="skt-about" id="main-content">
			<section class="skt-company-hero"><div class="skt-company-shell"><p class="skt-company-eyebrow">About SmartKeyTurkey</p><h1>Direct expertise across two specialist markets.</h1><p>SmartKeyTurkey connects clear information, qualified requests and practical local experience across property and petrochemical trade in Turkey.</p></div></section>
			<section class="skt-company-section"><div class="skt-company-shell skt-company-intro"><div><p class="skt-company-eyebrow">Our role</p><h2>A focused team for high-consideration decisions.</h2></div><p>For property, SmartKeyTurkey works directly with properties and projects under its control. For petrochemicals, it acts as an authorized sales representative and sourcing coordinator—not the manufacturer. In both journeys, availability, documents and commercial terms are confirmed for each request.</p></div></section>
			<section class="skt-company-section skt-company-muted"><div class="skt-company-shell"><div class="skt-company-heading"><p class="skt-company-eyebrow">What we do</p><h2>Separate processes. One standard of clarity.</h2></div><div class="skt-company-cards"><article><span>01</span><h3>Properties in Turkey</h3><p>Sale and rental opportunities in Istanbul, Ankara, Izmir and Antalya, presented with structured facts, current status and a direct inquiry path.</p><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>">Explore properties →</a></article><article><span>02</span><h3>Petrochemical sourcing</h3><p>Product and grade discovery for business buyers, followed by a qualified RFQ covering quantity, destination, packaging and required documents.</p><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>">Browse products →</a></article></div></div></section>
			<section class="skt-company-section"><div class="skt-company-shell"><div class="skt-company-heading"><p class="skt-company-eyebrow">How we work</p><h2>Built around traceable information.</h2></div><div class="skt-company-values"><article><strong>Structured discovery</strong><p>Consistent facts make complex options easier to review.</p></article><article><strong>Request-led terms</strong><p>Prices and commercial terms are supplied for the specific inquiry.</p></article><article><strong>Role transparency</strong><p>Our position is stated clearly for each business journey.</p></article><article><strong>Human review</strong><p>Legal, technical and regulatory questions are routed for qualified review.</p></article></div></div></section>
			<section class="skt-company-location"><div class="skt-company-shell"><div><p class="skt-company-eyebrow">Visit or contact us</p><h2>Beykent, Istanbul</h2><address>Cumhuriyet Mah. Gurpinar Yolu Street - Beykent - B. Cekmece<br>ERESIN YASAM MERKEZi No:10 A Block, 6th floor, office 112</address><p><a href="tel:+905050887188">+90 505 088 71 88</a></p></div><div class="skt-company-actions"><a class="skt-company-button" href="https://maps.app.goo.gl/zkBfCS655RkLEPd7A" target="_blank" rel="noopener noreferrer">Open Google Maps ↗</a><a class="skt-company-button is-secondary" href="tel:+905050887188">Call SmartKeyTurkey</a></div></div></section>
		</main>
		<?php return (string) ob_get_clean();
	}

	public static function maybe_create_about(): void {
		if ( self::VERSION === get_option( 'skt_about_page_version' ) ) { return; }
		$page = get_page_by_path( 'about-us' );
		$page_id = $page ? (int) $page->ID : (int) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'About Us', 'post_name' => 'about-us', 'post_content' => '[skt_about]' ) );
		if ( ! $page_id ) { return; }
		wp_update_post( array( 'ID' => $page_id, 'post_status' => 'publish', 'post_content' => '[skt_about]' ) );
		$data = array( array( 'id' => 'ab100001', 'elType' => 'container', 'isInner' => false, 'settings' => array( 'content_width' => 'full', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ) ), 'elements' => array( array( 'id' => 'ab100002', 'elType' => 'widget', 'widgetType' => 'shortcode', 'settings' => array( 'shortcode' => '[skt_about]' ), 'elements' => array() ) ) ) );
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' ); update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) ); update_post_meta( $page_id, '_elementor_page_settings', array( 'hide_title' => 'yes', 'page_layout' => 'elementor_full_width' ) ); update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, 'rank_math_title', 'About SmartKeyTurkey | Property & Petrochemical Solutions' ); update_post_meta( $page_id, 'rank_math_description', 'Learn how SmartKeyTurkey supports directly managed property opportunities and qualified petrochemical sourcing from its Istanbul office.' );
		update_option( 'skt_about_page_version', self::VERSION, false );
	}

	public static function route_404( string $template ): string { return is_404() ? SKT_CORE_DIR . 'templates/404.php' : $template; }
}
