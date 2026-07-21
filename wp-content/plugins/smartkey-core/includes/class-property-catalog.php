<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Property_Catalog {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_content_types' ) );
		add_action( 'init', array( self::class, 'seed_phase_one_cities' ), 25 );
		add_action( 'init', array( self::class, 'seed_sample_properties' ), 30 );
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
			'skt_property_reference', 'skt_property_district', 'skt_property_price', 'skt_property_currency',
			'skt_property_rooms', 'skt_property_bathrooms', 'skt_property_gross_area', 'skt_property_net_area',
			'skt_property_completion_status', 'skt_property_title_status', 'skt_property_availability',
			'skt_property_citizenship_review', 'skt_property_verification_status', 'skt_property_last_reviewed_date',
			'skt_property_source', 'skt_property_representative_disclosure',
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
		if ( get_option( 'skt_sample_properties_version' ) || ! post_type_exists( 'skt_property' ) ) {
			return;
		}

		$samples = array(
			array( 'city' => 'Istanbul', 'district' => 'Kadikoy (sample area)', 'title' => '[Sample] Istanbul Urban Residence', 'type' => 'Apartment', 'rooms' => '2+1', 'bathrooms' => '2', 'gross' => '115 m²', 'net' => '92 m²', 'completion' => 'Completed — sample value' ),
			array( 'city' => 'Ankara', 'district' => 'Cankaya (sample area)', 'title' => '[Sample] Ankara City Apartment', 'type' => 'Apartment', 'rooms' => '3+1', 'bathrooms' => '2', 'gross' => '145 m²', 'net' => '118 m²', 'completion' => 'Completed — sample value' ),
			array( 'city' => 'Izmir', 'district' => 'Karsiyaka (sample area)', 'title' => '[Sample] Izmir Coastal Residence', 'type' => 'Residence', 'rooms' => '2+1', 'bathrooms' => '2', 'gross' => '125 m²', 'net' => '98 m²', 'completion' => 'Under review — sample value' ),
			array( 'city' => 'Antalya', 'district' => 'Konyaalti (sample area)', 'title' => '[Sample] Antalya Lifestyle Apartment', 'type' => 'Apartment', 'rooms' => '1+1', 'bathrooms' => '1', 'gross' => '82 m²', 'net' => '67 m²', 'completion' => 'Completed — sample value' ),
		);

		foreach ( $samples as $index => $sample ) {
			$existing = get_page_by_title( $sample['title'], OBJECT, 'skt_property' );
			if ( $existing ) {
				continue;
			}
			$content = '<div class="skt-sample-notice"><strong>Demonstration record:</strong> This draft contains sample values for layout and workflow review. It is not a real listing or commercial offer.</div><h2>Sample property overview</h2><p>This record demonstrates the intended SmartKeyTurkey property structure for editorial review. Address, ownership, price, availability, title status and legal eligibility have not been verified.</p><h2>Review checklist</h2><ul><li>Confirm seller or authorized listing source.</li><li>Verify title deed, encumbrances, restrictions and debts.</li><li>Confirm measured areas, completion status and current availability.</li><li>Review transaction eligibility for the specific buyer.</li></ul>';
			$post_id = wp_insert_post( array( 'post_type' => 'skt_property', 'post_status' => 'draft', 'post_title' => $sample['title'], 'post_excerpt' => 'Draft demonstration property with sample data only. Not a real listing, verified offer or statement of availability.', 'post_content' => $content ) );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
			wp_set_object_terms( $post_id, $sample['city'], 'skt_property_city' );
			wp_set_object_terms( $post_id, $sample['type'], 'skt_property_type' );
			$meta = array(
				'skt_property_reference' => sprintf( 'SAMPLE-%s-%02d', strtoupper( substr( $sample['city'], 0, 3 ) ), $index + 1 ),
				'skt_property_district' => $sample['district'], 'skt_property_price' => 'Sample only — confirm after sourcing', 'skt_property_currency' => '',
				'skt_property_rooms' => $sample['rooms'], 'skt_property_bathrooms' => $sample['bathrooms'], 'skt_property_gross_area' => $sample['gross'], 'skt_property_net_area' => $sample['net'],
				'skt_property_completion_status' => $sample['completion'], 'skt_property_title_status' => 'Not reviewed — sample record', 'skt_property_availability' => 'Not verified — sample record',
				'skt_property_citizenship_review' => 'Not assessed; no eligibility claim', 'skt_property_verification_status' => 'Sample data — editorial review pending',
				'skt_property_last_reviewed_date' => current_time( 'Y-m-d' ), 'skt_property_source' => 'Internal demonstration record — replace before publication',
				'skt_property_representative_disclosure' => 'SmartKeyTurkey acts as an intermediary and advisor and is not the property owner. This draft uses sample data and must not be published as a real listing.',
			);
			foreach ( $meta as $key => $value ) { update_post_meta( $post_id, $key, $value ); }
		}
		update_option( 'skt_sample_properties_version', '1', false );
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
