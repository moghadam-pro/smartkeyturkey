<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Property_Frontend {
	private const ARCHIVE_TITLE = 'SmartKey — Property Archive';
	private const SINGLE_TITLE  = 'SmartKey — Single Property';

	public static function init(): void {
		add_shortcode( 'skt_property_archive', array( self::class, 'render_archive' ) );
		add_shortcode( 'skt_property_single', array( self::class, 'render_single' ) );
		add_filter( 'template_include', array( self::class, 'route_templates' ), 100 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'init', array( self::class, 'seed_elementor_templates' ), 35 );
		add_action( 'admin_post_skt_property_inquiry', array( self::class, 'handle_inquiry' ) );
		add_action( 'admin_post_nopriv_skt_property_inquiry', array( self::class, 'handle_inquiry' ) );
	}

	public static function enqueue_assets(): void {
		if ( is_singular( 'skt_property' ) || is_post_type_archive( 'skt_property' ) || is_tax( array( 'skt_property_city', 'skt_property_type' ) ) ) {
			wp_enqueue_style( 'smartkey-property-templates', plugins_url( 'assets/css/property-templates.css', SKT_CORE_FILE ), array( 'smartkey-design-tokens' ), SKT_CORE_VERSION );
		}
	}

	public static function route_templates( string $template ): string {
		if ( is_singular( 'skt_property' ) ) {
			return SKT_CORE_DIR . 'templates/single-skt_property.php';
		}
		if ( is_post_type_archive( 'skt_property' ) || is_tax( array( 'skt_property_city', 'skt_property_type' ) ) ) {
			return SKT_CORE_DIR . 'templates/archive-skt_property.php';
		}
		return $template;
	}

	public static function render_elementor_template( string $title, string $fallback ): void {
		$ids = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		if ( $ids && class_exists( '\Elementor\Plugin' ) ) {
			$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( (int) $ids[0], true );
			if ( trim( (string) $content ) ) {
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return;
			}
		}
		echo do_shortcode( $fallback ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function render_archive(): string {
		global $wp_query;
		$is_city = is_tax( 'skt_property_city' );
		$city    = $is_city ? get_queried_object() : null;
		$title   = $is_city ? sprintf( 'Properties in %s', $city->name ) : 'Properties in Turkey';
		$intro   = $is_city
			? sprintf( 'Explore SmartKeyTurkey properties and projects in %s. Current status, documents and transaction terms are provided on request.', $city->name )
			: 'Explore properties and projects directly managed by SmartKeyTurkey across Istanbul, Ankara, Izmir and Antalya. Current status, documents and transaction terms are provided on request.';
		$cities  = get_terms( array( 'taxonomy' => 'skt_property_city', 'hide_empty' => false, 'orderby' => 'name' ) );

		ob_start();
		?>
		<main class="skt-property-archive" id="main-content">
			<section class="skt-property-hero"><div class="skt-property-shell"><nav class="skt-property-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Properties</span></nav><p class="skt-property-eyebrow">Property advisory in Turkey</p><h1><?php echo esc_html( $title ); ?></h1><p><?php echo esc_html( $intro ); ?></p><a class="skt-property-button" href="#property-results">Explore opportunities</a></div></section>
			<section class="skt-property-shell skt-city-section" aria-labelledby="city-title"><div class="skt-property-section-heading"><div><p class="skt-property-eyebrow">Phase-one locations</p><h2 id="city-title">Choose a city</h2></div></div><div class="skt-city-grid">
			<?php foreach ( $cities as $term ) : ?><a class="skt-city-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><span class="skt-city-index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( array_search( $term, $cities, true ) + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $term->name ); ?></h3><p><?php echo esc_html( self::city_summary( $term->slug ) ); ?></p><span>View city opportunities →</span></a><?php endforeach; ?>
			</div></section>
			<section class="skt-property-shell skt-property-results" id="property-results" aria-labelledby="results-title"><div class="skt-property-section-heading"><div><p class="skt-property-eyebrow">Reviewed listings</p><h2 id="results-title"><?php echo $is_city ? esc_html( $city->name . ' opportunities' ) : 'Available properties'; ?></h2></div><p><?php echo esc_html( sprintf( _n( '%s listing', '%s listings', (int) $wp_query->found_posts, 'smartkey-core' ), number_format_i18n( (int) $wp_query->found_posts ) ) ); ?></p></div>
			<?php if ( have_posts() ) : ?><div class="skt-property-grid"><?php while ( have_posts() ) : the_post(); self::render_card(); endwhile; ?></div><nav class="skt-property-pagination" aria-label="Property result pages"><?php echo wp_kses_post( paginate_links( array( 'type' => 'list' ) ) ); ?></nav><?php else : ?><div class="skt-property-empty"><h3>Curated listings are being prepared</h3><p>Tell us your preferred city and intended use. Current availability and transaction terms are supplied directly on request.</p><a class="skt-property-button" href="#property-consultation">Request a shortlist</a></div><?php endif; ?>
			</section>
			<?php echo self::render_inquiry_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</main>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_card(): void {
		$post_id = get_the_ID();
		$city    = get_the_terms( $post_id, 'skt_property_city' );
		$status  = (string) get_post_meta( $post_id, 'skt_property_listing_status', true );
		$transaction = (string) get_post_meta( $post_id, 'skt_property_transaction_type', true );
		?>
		<article <?php post_class( 'skt-property-card' ); ?>><?php if ( in_array( $status, array( 'sold', 'rented' ), true ) ) : ?><span class="skt-property-status-tag"><?php echo esc_html( 'sold' === $status ? 'Sold' : 'Rented' ); ?></span><?php endif; ?><a class="skt-property-card-image" href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); else : ?><span aria-hidden="true">SK</span><?php endif; ?></a><div><p class="skt-property-card-city"><?php echo esc_html( $city && ! is_wp_error( $city ) ? $city[0]->name : 'Turkey' ); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><strong><?php echo esc_html( 'rent' === $transaction ? 'For rent — terms on request' : 'For sale — terms on request' ); ?></strong></div></article>
		<?php
	}

	public static function render_single(): string {
		if ( ! is_singular( 'skt_property' ) ) {
			return '';
		}
		$id = get_queried_object_id();
		$fields = array( 'Transaction' => 'skt_property_transaction_type', 'Status' => 'skt_property_listing_status', 'District' => 'skt_property_district', 'Rooms' => 'skt_property_rooms', 'Bathrooms' => 'skt_property_bathrooms', 'Gross area' => 'skt_property_gross_area', 'Net area' => 'skt_property_net_area', 'Construction year' => 'skt_property_construction_year', 'Building age' => 'skt_property_building_age', 'Floor' => 'skt_property_floor', 'Parking' => 'skt_property_parking', 'Furnished' => 'skt_property_furnished', 'Amenities' => 'skt_property_amenities', 'Developer' => 'skt_property_developer', 'Payment terms' => 'skt_property_payment_terms', 'Delivery date' => 'skt_property_delivery_date', 'Completion' => 'skt_property_completion_status', 'Title status' => 'skt_property_title_status', 'Citizenship review' => 'skt_property_citizenship_review' );
		$cities = get_the_terms( $id, 'skt_property_city' );
		$city = $cities && ! is_wp_error( $cities ) ? $cities[0] : null;
		$disclosure = get_post_meta( $id, 'skt_property_control_disclosure', true ) ?: 'SmartKeyTurkey works directly with properties and projects under its control. Current documents, availability and transaction terms are supplied on request.';
		$status = (string) get_post_meta( $id, 'skt_property_listing_status', true );
		$latitude = (string) get_post_meta( $id, 'skt_property_latitude', true ); $longitude = (string) get_post_meta( $id, 'skt_property_longitude', true );
		ob_start();
		?>
		<main class="skt-property-single" id="main-content"><section class="skt-property-hero"><div class="skt-property-shell"><nav class="skt-property-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_property' ) ); ?>">Properties</a><?php if ( $city ) : ?><span>/</span><a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( $city->name ); ?></a><?php endif; ?></nav><div class="skt-property-single-grid"><div class="skt-property-featured"><?php if ( in_array( $status, array( 'sold', 'rented' ), true ) ) : ?><span class="skt-property-status-tag"><?php echo esc_html( 'sold' === $status ? 'Sold' : 'Rented' ); ?></span><?php endif; ?><?php echo get_the_post_thumbnail( $id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div><p class="skt-property-eyebrow"><?php echo esc_html( $city ? $city->name : 'Turkey property' ); ?></p><h1><?php echo esc_html( get_the_title( $id ) ); ?></h1><p><?php echo esc_html( get_the_excerpt( $id ) ); ?></p><a class="skt-property-button" href="#property-consultation">Request current details</a></div></div></div></section><section class="skt-property-shell skt-property-detail"><article><?php echo apply_filters( 'the_content', get_post_field( 'post_content', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( $latitude && $longitude ) : ?><section class="skt-property-map"><h2>Approximate area map</h2><iframe title="Approximate property area" loading="lazy" src="<?php echo esc_url( sprintf( 'https://www.openstreetmap.org/export/embed.html?bbox=%1$f%%2C%2$f%%2C%3$f%%2C%4$f&layer=mapnik&marker=%5$f%%2C%6$f', (float) $longitude - .01, (float) $latitude - .01, (float) $longitude + .01, (float) $latitude + .01, (float) $latitude, (float) $longitude ) ); ?>"></iframe><a href="<?php echo esc_url( sprintf( 'https://www.openstreetmap.org/?mlat=%1$f&mlon=%2$f#map=15/%1$f/%2$f', (float) $latitude, (float) $longitude ) ); ?>" target="_blank" rel="noopener">Open approximate area map ↗</a></section><?php endif; ?></article><aside><h2>Property facts</h2><dl><?php foreach ( $fields as $label => $key ) : $value = get_post_meta( $id, $key, true ); if ( $value ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endif; endforeach; ?></dl><p class="skt-property-disclosure"><?php echo esc_html( $disclosure ); ?></p></aside></section><?php echo self::render_inquiry_form( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></main>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_inquiry_form( int $property_id = 0 ): string {
		$status = isset( $_GET['inquiry'] ) ? sanitize_key( wp_unslash( $_GET['inquiry'] ) ) : '';
		ob_start(); ?>
		<section class="skt-property-inquiry" id="property-consultation"><div class="skt-property-shell"><div class="skt-property-section-heading"><div><p class="skt-property-eyebrow">Direct inquiry</p><h2>Request current property terms</h2></div><p>Pricing, availability and transaction terms are provided directly on request.</p></div><?php if ( 'sent' === $status ) : ?><p class="skt-property-notice" role="status">Thank you. Your inquiry has been sent for review.</p><?php elseif ( 'error' === $status ) : ?><p class="skt-property-notice is-error" role="alert">The inquiry could not be sent. Please review the required fields and try again.</p><?php endif; ?><form class="skt-property-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'skt_property_inquiry', 'skt_property_nonce' ); ?><input type="hidden" name="action" value="skt_property_inquiry"><input type="hidden" name="property_id" value="<?php echo esc_attr( $property_id ); ?>"><label>Name *<input name="name" required autocomplete="name"></label><label>Email *<input type="email" name="email" required autocomplete="email"></label><label>Phone / WhatsApp<input name="phone" autocomplete="tel"></label><label>Preferred city<select name="city"><option value="">Select a city</option><?php foreach ( array( 'Istanbul', 'Ankara', 'Izmir', 'Antalya' ) as $city ) : ?><option><?php echo esc_html( $city ); ?></option><?php endforeach; ?></select></label><label>Transaction<select name="transaction"><option value="">Select</option><option>Buy</option><option>Rent</option></select></label><label>Intended use<select name="purpose"><option value="">Select intended use</option><option>Primary residence</option><option>Investment</option><option>Holiday home</option><option>Other</option></select></label><label class="skt-property-form-wide">Requirements<textarea name="requirements" rows="5" placeholder="Area, property type, rooms, timing and document questions"></textarea></label><label class="skt-property-consent skt-property-form-wide"><input type="checkbox" name="consent" value="1" required> I agree that SmartKeyTurkey may use these details to respond to this inquiry. *</label><label class="skt-property-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label><button class="skt-property-button" type="submit">Send property inquiry</button></form></div></section>
		<?php return (string) ob_get_clean();
	}

	public static function handle_inquiry(): void {
		$property_id = isset( $_POST['property_id'] ) ? absint( $_POST['property_id'] ) : 0;
		$return = $property_id ? get_permalink( $property_id ) : get_post_type_archive_link( 'skt_property' );
		if ( ! isset( $_POST['skt_property_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['skt_property_nonce'] ) ), 'skt_property_inquiry' ) || ! empty( $_POST['website'] ) || empty( $_POST['consent'] ) ) {
			wp_safe_redirect( add_query_arg( 'inquiry', 'error', $return ) . '#property-consultation' ); exit;
		}
		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! $name || ! is_email( $email ) ) { wp_safe_redirect( add_query_arg( 'inquiry', 'error', $return ) . '#property-consultation' ); exit; }
		$data = array( 'Property' => $property_id ? get_the_title( $property_id ) : 'General inquiry', 'Name' => $name, 'Email' => $email, 'Phone' => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ), 'City' => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ), 'Transaction' => sanitize_text_field( wp_unslash( $_POST['transaction'] ?? '' ) ), 'Purpose' => sanitize_text_field( wp_unslash( $_POST['purpose'] ?? '' ) ), 'Requirements' => sanitize_textarea_field( wp_unslash( $_POST['requirements'] ?? '' ) ) );
		$stored = Request_Manager::store_property( $property_id, $data );
		if ( $stored && $property_id ) { update_post_meta( $property_id, 'skt_property_inquiry_count', (int) get_post_meta( $property_id, 'skt_property_inquiry_count', true ) + 1 ); }
		wp_safe_redirect( add_query_arg( 'inquiry', $stored ? 'sent' : 'error', $return ) . '#property-consultation' ); exit;
	}

	public static function seed_elementor_templates(): void {
		if ( '1' === get_option( 'skt_elementor_property_templates_version' ) || ! post_type_exists( 'elementor_library' ) ) { return; }
		self::seed_template( self::ARCHIVE_TITLE, 'archive', '[skt_property_archive]', 'c31a001', 'c31a002' );
		self::seed_template( self::SINGLE_TITLE, 'single-post', '[skt_property_single]', 'd41b001', 'd41b002' );
		update_option( 'skt_elementor_property_templates_version', '1', false );
	}

	private static function seed_template( string $title, string $type, string $shortcode, string $container, string $widget ): void {
		$ids = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => array( 'publish', 'draft' ), 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		$id = $ids ? (int) $ids[0] : wp_insert_post( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'post_title' => $title ) );
		if ( is_wp_error( $id ) || ! $id ) { return; }
		$data = array( array( 'id' => $container, 'elType' => 'container', 'isInner' => false, 'settings' => array( 'content_width' => 'full', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ) ), 'elements' => array( array( 'id' => $widget, 'elType' => 'widget', 'widgetType' => 'shortcode', 'settings' => array( 'shortcode' => $shortcode ), 'elements' => array() ) ) ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' ); update_post_meta( $id, '_elementor_template_type', $type ); update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) ); update_post_meta( $id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
	}

	private static function city_summary( string $slug ): string {
		$copy = array( 'istanbul' => 'International demand, diverse districts and extensive urban connectivity.', 'antalya' => 'Coastal living and established interest among international buyers.', 'ankara' => 'Capital-city services, long-term living and a substantial domestic market.', 'izmir' => 'Coastal metropolitan lifestyle with established residential districts.' );
		return $copy[ $slug ] ?? 'Structured local discovery with case-specific verification.';
	}
}
