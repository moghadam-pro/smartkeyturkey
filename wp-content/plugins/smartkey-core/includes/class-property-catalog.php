<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Property_Catalog {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_content_types' ) );
		add_action( 'init', array( self::class, 'seed_phase_one_cities' ), 25 );
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
