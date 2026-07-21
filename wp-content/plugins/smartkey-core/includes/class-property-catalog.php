<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Property_Catalog {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_content_types' ) );
		add_action( 'init', array( self::class, 'seed_phase_one_cities' ), 25 );
		add_action( 'init', array( self::class, 'seed_sample_properties' ), 30 );
		add_action( 'add_meta_boxes_skt_property', array( self::class, 'add_details_metabox' ) );
		add_action( 'save_post_skt_property', array( self::class, 'save_details_metabox' ) );
		add_action( 'init', array( self::class, 'maybe_flush_rewrite_rules' ), 99 );
		add_filter( 'manage_skt_property_posts_columns', array( self::class, 'add_admin_columns' ) );
		add_action( 'manage_skt_property_posts_custom_column', array( self::class, 'render_admin_column' ), 10, 2 );
	}

	public static function register_content_types(): void {
		register_post_type(
			'skt_property',
			array(
				'labels' => array(
					'name'          => __( 'Properties', 'smartkey-core' ),
					'singular_name' => __( 'Property', 'smartkey-core' ),
					'add_new_item'  => __( 'Add Property', 'smartkey-core' ),
					'edit_item'     => __( 'Edit Property', 'smartkey-core' ),
				),
				'public'             => true,
				'show_in_menu'       => 'smartkey',
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-building',
				'has_archive'        => 'properties',
				'rewrite'            => array( 'slug' => 'properties' ),
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
				'menu_position'      => 20,
				'publicly_queryable' => true,
			)
		);

		self::register_taxonomy( 'skt_property_city', __( 'Property Cities', 'smartkey-core' ), __( 'Property City', 'smartkey-core' ), 'property-city' );
		self::register_taxonomy( 'skt_property_type', __( 'Property Types', 'smartkey-core' ), __( 'Property Type', 'smartkey-core' ), 'property-type' );

		$string_fields = array(
			'skt_property_reference', 'skt_property_district', 'skt_property_transaction_type', 'skt_property_listing_status',
			'skt_property_rooms', 'skt_property_bathrooms', 'skt_property_gross_area', 'skt_property_net_area',
			'skt_property_construction_year', 'skt_property_building_age', 'skt_property_floor', 'skt_property_parking', 'skt_property_furnished', 'skt_property_amenities',
			'skt_property_developer', 'skt_property_payment_terms', 'skt_property_delivery_date', 'skt_property_latitude', 'skt_property_longitude',
			'skt_property_completion_status', 'skt_property_title_status',
			'skt_property_citizenship_review', 'skt_property_verification_status', 'skt_property_last_reviewed_date',
			'skt_property_source', 'skt_property_control_disclosure',
		);

		foreach ( $string_fields as $meta_key ) {
			register_post_meta(
				'skt_property',
				$meta_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_textarea_field',
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				)
			);
		}

		register_post_meta( 'skt_property', 'skt_property_inquiry_count', array( 'type' => 'integer', 'single' => true, 'default' => 0, 'show_in_rest' => false, 'sanitize_callback' => 'absint', 'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ) ) );
		register_post_meta( 'skt_property', 'skt_property_new_build', array( 'type' => 'boolean', 'single' => true, 'default' => false, 'show_in_rest' => true, 'sanitize_callback' => 'rest_sanitize_boolean', 'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ) ) );
	}

	private static function register_taxonomy( string $key, string $plural, string $singular, string $slug ): void {
		register_taxonomy(
			$key,
			array( 'skt_property' ),
			array(
				'labels'       => array( 'name' => $plural, 'singular_name' => $singular ),
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $slug ),
			)
		);
	}

	public static function seed_phase_one_cities(): void {
		if ( get_option( 'skt_property_cities_seeded_1' ) || ! taxonomy_exists( 'skt_property_city' ) ) {
			return;
		}

		foreach ( array( 'Istanbul', 'Ankara', 'Izmir', 'Antalya' ) as $city ) {
			if ( ! term_exists( $city, 'skt_property_city' ) ) {
				wp_insert_term( $city, 'skt_property_city' );
			}
		}
		update_option( 'skt_property_cities_seeded_1', 1, false );
	}

	public static function maybe_flush_rewrite_rules(): void {
		if ( '1' === get_option( 'skt_property_rewrite_version' ) ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'skt_property_rewrite_version', '1', false );
	}

	public static function seed_sample_properties(): void {
		if ( '2' === get_option( 'skt_sample_properties_version' ) || ! post_type_exists( 'skt_property' ) ) {
			return;
		}

		$samples = array(
			array( 'city' => 'Istanbul', 'district' => 'Kadikoy', 'title' => '[Sample] Istanbul Urban Residence', 'type' => 'Apartment', 'transaction' => 'sale', 'status' => 'available', 'rooms' => '2+1', 'bathrooms' => '2', 'gross' => '115 m²', 'net' => '92 m²', 'floor' => '6', 'year' => '2022', 'lat' => '40.9917', 'lng' => '29.0277' ),
			array( 'city' => 'Ankara', 'district' => 'Cankaya', 'title' => '[Sample] Ankara City Apartment', 'type' => 'Apartment', 'transaction' => 'rent', 'status' => 'rented', 'rooms' => '3+1', 'bathrooms' => '2', 'gross' => '145 m²', 'net' => '118 m²', 'floor' => '4', 'year' => '2020', 'lat' => '39.9022', 'lng' => '32.8607' ),
			array( 'city' => 'Izmir', 'district' => 'Karsiyaka', 'title' => '[Sample] Izmir Coastal Residence', 'type' => 'Residence', 'transaction' => 'sale', 'status' => 'sold', 'rooms' => '2+1', 'bathrooms' => '2', 'gross' => '125 m²', 'net' => '98 m²', 'floor' => '8', 'year' => (string) current_time( 'Y' ), 'new' => true, 'lat' => '38.4554', 'lng' => '27.1198' ),
			array( 'city' => 'Antalya', 'district' => 'Konyaalti', 'title' => '[Sample] Antalya Lifestyle Apartment', 'type' => 'Apartment', 'transaction' => 'rent', 'status' => 'available', 'rooms' => '1+1', 'bathrooms' => '1', 'gross' => '82 m²', 'net' => '67 m²', 'floor' => '3', 'year' => '2023', 'lat' => '36.8721', 'lng' => '30.6387' ),
		);

		foreach ( $samples as $index => $sample ) {
			$existing = get_page_by_title( $sample['title'], OBJECT, 'skt_property' );
			$content = '<div class="skt-sample-notice"><strong>Demonstration record:</strong> This published record uses sample values to review the website structure and is not a commercial offer.</div><h2>Property overview</h2><p>This sample demonstrates how SmartKeyTurkey presents properties and projects under its direct control. Final availability, documentation and transaction terms are provided on request.</p><h2>Review checklist</h2><ul><li>Confirm the current property file and control documentation.</li><li>Review title deed, restrictions and debts.</li><li>Confirm measured areas, delivery information and current status.</li><li>Review transaction eligibility for the specific buyer.</li></ul>';
			$post_id = $existing ? (int) $existing->ID : wp_insert_post( array( 'post_type' => 'skt_property', 'post_title' => $sample['title'] ) );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish', 'post_excerpt' => 'Demonstration property with complete sample data. Current documentation, availability and transaction terms are provided on request.', 'post_content' => $content ) );
			wp_set_object_terms( $post_id, $sample['city'], 'skt_property_city' );
			wp_set_object_terms( $post_id, $sample['type'], 'skt_property_type' );
			$meta = array(
				'skt_property_reference' => sprintf( 'SAMPLE-%s-%02d', strtoupper( substr( $sample['city'], 0, 3 ) ), $index + 1 ),
				'skt_property_district' => $sample['district'], 'skt_property_transaction_type' => $sample['transaction'], 'skt_property_listing_status' => $sample['status'],
				'skt_property_rooms' => $sample['rooms'], 'skt_property_bathrooms' => $sample['bathrooms'], 'skt_property_gross_area' => $sample['gross'], 'skt_property_net_area' => $sample['net'],
				'skt_property_new_build' => ! empty( $sample['new'] ) ? '1' : '0', 'skt_property_construction_year' => $sample['year'], 'skt_property_building_age' => ! empty( $sample['new'] ) ? 'New' : (string) max( 0, (int) current_time( 'Y' ) - (int) $sample['year'] ), 'skt_property_floor' => $sample['floor'], 'skt_property_parking' => 'Available — sample value', 'skt_property_furnished' => 'No — sample value',
				'skt_property_amenities' => 'Security, lift, landscaped common areas — sample values', 'skt_property_developer' => 'Available on request', 'skt_property_payment_terms' => 'Provided on request', 'skt_property_delivery_date' => 'Provided on request', 'skt_property_latitude' => $sample['lat'], 'skt_property_longitude' => $sample['lng'],
				'skt_property_completion_status' => 'Completed — sample value', 'skt_property_title_status' => 'Documentation provided on request',
				'skt_property_citizenship_review' => 'Not assessed; no eligibility claim', 'skt_property_verification_status' => 'Sample data — editorial review pending',
				'skt_property_last_reviewed_date' => current_time( 'Y-m-d' ), 'skt_property_source' => 'Internal demonstration record',
				'skt_property_control_disclosure' => 'SmartKeyTurkey works directly with this property or project under its control. This page currently uses demonstration data; final documents, availability and transaction terms are supplied on request.',
			);
			foreach ( $meta as $key => $value ) { update_post_meta( $post_id, $key, $value ); }
			delete_post_meta( $post_id, 'skt_property_price' ); delete_post_meta( $post_id, 'skt_property_currency' ); delete_post_meta( $post_id, 'skt_property_availability' ); delete_post_meta( $post_id, 'skt_property_representative_disclosure' );
		}
		update_option( 'skt_sample_properties_version', '2', false );
	}

	public static function add_details_metabox(): void {
		add_meta_box( 'skt-property-details', 'SmartKey Property Details', array( self::class, 'render_details_metabox' ), 'skt_property', 'normal', 'high' );
	}

	public static function render_details_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'skt_save_property_details', 'skt_property_details_nonce' );
		$fields = array(
			'skt_property_reference' => array( 'Reference', 'text' ), 'skt_property_district' => array( 'District / area shown publicly', 'text' ),
			'skt_property_transaction_type' => array( 'Transaction', 'select', array( 'sale' => 'For sale', 'rent' => 'For rent' ) ),
			'skt_property_listing_status' => array( 'Listing status', 'select', array( 'available' => 'Available', 'sold' => 'Sold', 'rented' => 'Rented' ) ),
			'skt_property_construction_year' => array( 'Construction year', 'number' ), 'skt_property_rooms' => array( 'Rooms', 'text' ), 'skt_property_bathrooms' => array( 'Bathrooms', 'text' ),
			'skt_property_gross_area' => array( 'Gross area', 'text' ), 'skt_property_net_area' => array( 'Net area', 'text' ), 'skt_property_floor' => array( 'Floor', 'text' ),
			'skt_property_parking' => array( 'Parking', 'text' ), 'skt_property_furnished' => array( 'Furnished status', 'text' ), 'skt_property_amenities' => array( 'Amenities', 'textarea' ),
			'skt_property_developer' => array( 'Developer', 'text' ), 'skt_property_payment_terms' => array( 'Payment terms', 'textarea' ), 'skt_property_delivery_date' => array( 'Delivery date', 'text' ),
			'skt_property_completion_status' => array( 'Completion status', 'text' ), 'skt_property_title_status' => array( 'Title / document status', 'text' ), 'skt_property_citizenship_review' => array( 'Citizenship review status', 'text' ),
			'skt_property_latitude' => array( 'Latitude', 'text' ), 'skt_property_longitude' => array( 'Longitude', 'text' ), 'skt_property_source' => array( 'Source / property file', 'text' ),
			'skt_property_verification_status' => array( 'Verification status', 'text' ), 'skt_property_last_reviewed_date' => array( 'Last reviewed date', 'date' ), 'skt_property_control_disclosure' => array( 'Direct-control disclosure', 'textarea' ),
		);
		$new_build = (bool) get_post_meta( $post->ID, 'skt_property_new_build', true );
		?><div class="skt-property-metabox"><p class="skt-new-build"><label><input type="checkbox" name="skt_property_new_build" value="1" <?php checked( $new_build ); ?>> New / newly built property — use the current year automatically</label></p><div class="skt-property-fields"><?php foreach ( $fields as $key => $config ) : $value = (string) get_post_meta( $post->ID, $key, true ); ?><label><span><?php echo esc_html( $config[0] ); ?></span><?php if ( 'select' === $config[1] ) : ?><select name="<?php echo esc_attr( $key ); ?>"><?php foreach ( $config[2] as $option => $label ) : ?><option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select><?php elseif ( 'textarea' === $config[1] ) : ?><textarea name="<?php echo esc_attr( $key ); ?>" rows="3"><?php echo esc_textarea( $value ); ?></textarea><?php else : ?><input type="<?php echo esc_attr( $config[1] ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo 'skt_property_construction_year' === $key && $new_build ? 'disabled' : ''; ?>><?php endif; ?></label><?php endforeach; ?></div></div><script>document.addEventListener('DOMContentLoaded',function(){const c=document.querySelector('[name="skt_property_new_build"]'),y=document.querySelector('[name="skt_property_construction_year"]');if(c&&y)c.addEventListener('change',function(){y.disabled=c.checked;if(c.checked)y.value='<?php echo esc_js( current_time( 'Y' ) ); ?>';});});</script><style>.skt-property-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.skt-property-fields label{display:flex;flex-direction:column;gap:6px;font-weight:600}.skt-property-fields input,.skt-property-fields select,.skt-property-fields textarea{width:100%}.skt-new-build{padding:12px;border-left:4px solid #84c341;background:#f0f6e9}@media(max-width:1000px){.skt-property-fields{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:700px){.skt-property-fields{grid-template-columns:1fr}}</style><?php
	}

	public static function save_details_metabox( int $post_id ): void {
		if ( ! isset( $_POST['skt_property_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['skt_property_details_nonce'] ) ), 'skt_save_property_details' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) { return; }
		$new_build = ! empty( $_POST['skt_property_new_build'] );
		update_post_meta( $post_id, 'skt_property_new_build', $new_build ? '1' : '0' );
		$keys = array( 'skt_property_reference', 'skt_property_district', 'skt_property_transaction_type', 'skt_property_listing_status', 'skt_property_rooms', 'skt_property_bathrooms', 'skt_property_gross_area', 'skt_property_net_area', 'skt_property_floor', 'skt_property_parking', 'skt_property_furnished', 'skt_property_amenities', 'skt_property_developer', 'skt_property_payment_terms', 'skt_property_delivery_date', 'skt_property_completion_status', 'skt_property_title_status', 'skt_property_citizenship_review', 'skt_property_latitude', 'skt_property_longitude', 'skt_property_source', 'skt_property_verification_status', 'skt_property_last_reviewed_date', 'skt_property_control_disclosure' );
		foreach ( $keys as $key ) { if ( isset( $_POST[ $key ] ) ) { update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) ); } }
		$year = $new_build ? current_time( 'Y' ) : absint( $_POST['skt_property_construction_year'] ?? 0 );
		update_post_meta( $post_id, 'skt_property_construction_year', $year ? (string) $year : '' );
		update_post_meta( $post_id, 'skt_property_building_age', $new_build ? 'New' : ( $year ? (string) max( 0, (int) current_time( 'Y' ) - $year ) : '' ) );
		delete_post_meta( $post_id, 'skt_property_price' ); delete_post_meta( $post_id, 'skt_property_currency' );
	}

	public static function add_admin_columns( array $columns ): array {
		$columns['skt_property_city']   = __( 'City', 'smartkey-core' );
		$columns['skt_property_ref']    = __( 'Reference', 'smartkey-core' );
		$columns['skt_property_status'] = __( 'Verification', 'smartkey-core' );
		return $columns;
	}

	public static function render_admin_column( string $column, int $post_id ): void {
		if ( 'skt_property_city' === $column ) {
			$terms = get_the_terms( $post_id, 'skt_property_city' );
			echo esc_html( $terms && ! is_wp_error( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '—' );
		}
		if ( 'skt_property_ref' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, 'skt_property_reference', true ) );
		}
		if ( 'skt_property_status' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, 'skt_property_verification_status', true ) );
		}
	}
}
