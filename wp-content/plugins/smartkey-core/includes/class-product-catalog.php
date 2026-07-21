<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Product_Catalog {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_content_types' ) );
		add_filter( 'manage_skt_product_posts_columns', array( self::class, 'add_admin_columns' ) );
		add_action( 'manage_skt_product_posts_custom_column', array( self::class, 'render_admin_column' ), 10, 2 );
	}

	public static function register_content_types(): void {
		register_post_type(
			'skt_product',
			array(
				'labels' => array(
					'name'          => __( 'Petrochemical Products', 'smartkey-core' ),
					'singular_name' => __( 'Petrochemical Product', 'smartkey-core' ),
					'add_new_item'  => __( 'Add Petrochemical Product', 'smartkey-core' ),
					'edit_item'     => __( 'Edit Petrochemical Product', 'smartkey-core' ),
				),
				'public'             => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-products',
				'has_archive'        => 'petrochemical-products',
				'rewrite'            => array( 'slug' => 'petrochemical-products' ),
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
				'menu_position'      => 21,
				'publicly_queryable' => true,
			)
		);

		register_taxonomy(
			'skt_product_family',
			array( 'skt_product' ),
			array(
				'labels'       => array(
					'name'          => __( 'Product Families', 'smartkey-core' ),
					'singular_name' => __( 'Product Family', 'smartkey-core' ),
				),
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'product-family' ),
			)
		);

		$meta_fields = array(
			'skt_grade',
			'skt_source_url',
			'skt_source_image_url',
			'skt_image_rights_status',
			'skt_applications',
			'skt_technical_properties',
			'skt_verification_status',
			'skt_availability_status',
			'skt_representative_disclosure',
			'skt_last_reviewed_date',
		);

		foreach ( $meta_fields as $meta_key ) {
			register_post_meta(
				'skt_product',
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

		foreach ( array( 'skt_view_count', 'skt_rfq_count' ) as $counter_key ) {
			register_post_meta(
				'skt_product',
				$counter_key,
				array(
					'type'              => 'integer',
					'single'            => true,
					'default'           => 0,
					'show_in_rest'      => false,
					'sanitize_callback' => 'absint',
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				)
			);
		}

		register_post_meta(
			'skt_product',
			'skt_last_rfq_date',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}

	public static function add_admin_columns( array $columns ): array {
		$columns['skt_family']       = __( 'Family', 'smartkey-core' );
		$columns['skt_grade']        = __( 'Grade', 'smartkey-core' );
		$columns['skt_verification'] = __( 'Verification', 'smartkey-core' );
		return $columns;
	}

	public static function render_admin_column( string $column, int $post_id ): void {
		if ( 'skt_family' === $column ) {
			$terms = get_the_terms( $post_id, 'skt_product_family' );
			echo esc_html( $terms && ! is_wp_error( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '—' );
		}

		if ( 'skt_grade' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, 'skt_grade', true ) );
		}

		if ( 'skt_verification' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, 'skt_verification_status', true ) );
		}
	}
}
