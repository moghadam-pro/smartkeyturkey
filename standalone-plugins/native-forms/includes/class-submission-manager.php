<?php

namespace NativeForms;

defined( 'ABSPATH' ) || exit;

final class Submission_Manager {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_post_type' ), 12 );
		add_filter( 'manage_nf_submission_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_nf_submission_posts_custom_column', array( self::class, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes_nf_submission', array( self::class, 'add_details_box' ) );
	}

	public static function register_post_type(): void {
		register_post_type(
			'nf_submission',
			array(
				'labels' => array(
					'name'          => __( 'Submissions', 'native-forms' ),
					'singular_name' => __( 'Submission', 'native-forms' ),
					'edit_item'     => __( 'View Submission', 'native-forms' ),
					'search_items'  => __( 'Search Submissions', 'native-forms' ),
					'not_found'     => __( 'No submissions found', 'native-forms' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => false,
				'supports'     => array( 'title' ),
				'map_meta_cap' => false,
				'capabilities' => self::capabilities(),
			)
		);
	}

	public static function store( string $type, string $title, array $data, int $form_id = 0 ): int {
		$submission_id = wp_insert_post(
			array(
				'post_type'   => 'nf_submission',
				'post_status' => 'publish',
				'post_title'  => wp_strip_all_tags( $title ),
			),
			true
		);
		if ( is_wp_error( $submission_id ) ) {
			return 0;
		}
		update_post_meta( $submission_id, '_nf_type', sanitize_key( $type ) );
		update_post_meta( $submission_id, '_nf_data', self::sanitize_data( $data ) );
		update_post_meta( $submission_id, '_nf_form_id', absint( $form_id ) );
		update_post_meta( $submission_id, '_nf_status', 'new' );

		do_action( 'native_forms_submission_created', (int) $submission_id );
		return (int) $submission_id;
	}

	public static function columns( array $columns ): array {
		return array(
			'cb'         => $columns['cb'] ?? '',
			'title'      => __( 'Submission', 'native-forms' ),
			'nf_type'    => __( 'Type', 'native-forms' ),
			'nf_contact' => __( 'Contact', 'native-forms' ),
			'nf_status'  => __( 'Status', 'native-forms' ),
			'date'       => __( 'Received', 'native-forms' ),
		);
	}

	public static function column_content( string $column, int $post_id ): void {
		$data = get_post_meta( $post_id, '_nf_data', true );
		$data = is_array( $data ) ? $data : array();
		if ( 'nf_type' === $column ) {
			echo esc_html( ucfirst( (string) get_post_meta( $post_id, '_nf_type', true ) ) );
		}
		if ( 'nf_contact' === $column ) {
			echo esc_html( (string) ( $data['name'] ?? $data['full_name'] ?? $data['full-name'] ?? '' ) );
			$email = (string) ( $data['email'] ?? $data['your_email'] ?? $data['your-email'] ?? '' );
			if ( $email ) {
				echo '<br><small>' . esc_html( $email ) . '</small>';
			}
		}
		if ( 'nf_status' === $column ) {
			echo '<strong>' . esc_html( ucfirst( (string) get_post_meta( $post_id, '_nf_status', true ) ) ) . '</strong>';
		}
	}

	public static function add_details_box(): void {
		add_meta_box( 'nf_submission_details', __( 'Submitted details', 'native-forms' ), array( self::class, 'render_details_box' ), 'nf_submission', 'normal', 'high' );
	}

	public static function render_details_box( \WP_Post $post ): void {
		$data = get_post_meta( $post->ID, '_nf_data', true );
		$data = is_array( $data ) ? $data : array();
		echo '<table class="widefat striped"><tbody>';
		foreach ( $data as $key => $value ) {
			echo '<tr><th style="width:220px">' . esc_html( ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) ) ) . '</th><td style="white-space:pre-wrap">' . esc_html( is_array( $value ) ? implode( ', ', $value ) : (string) $value ) . '</td></tr>';
		}
		echo '</tbody></table><p><small>' . esc_html__( 'Stored privately in this WordPress installation. The plugin does not send submission emails.', 'native-forms' ) . '</small></p>';
	}

	private static function capabilities(): array {
		return array(
			'edit_post'              => 'manage_options',
			'read_post'              => 'manage_options',
			'delete_post'            => 'manage_options',
			'edit_posts'             => 'manage_options',
			'edit_others_posts'      => 'manage_options',
			'publish_posts'          => 'manage_options',
			'read_private_posts'     => 'manage_options',
			'delete_posts'           => 'manage_options',
			'delete_private_posts'   => 'manage_options',
			'delete_published_posts' => 'manage_options',
			'delete_others_posts'    => 'manage_options',
			'edit_private_posts'     => 'manage_options',
			'edit_published_posts'   => 'manage_options',
			'create_posts'           => 'do_not_allow',
		);
	}

	private static function sanitize_data( array $data ): array {
		$clean = array();
		foreach ( $data as $key => $value ) {
			$clean[ sanitize_key( (string) $key ) ] = self::sanitize_value( $value );
		}
		return $clean;
	}

	private static function sanitize_value( $value ) {
		if ( is_array( $value ) ) {
			return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
		}
		return sanitize_textarea_field( (string) $value );
	}
}
