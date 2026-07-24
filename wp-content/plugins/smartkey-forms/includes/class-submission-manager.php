<?php

namespace SmartKeyTurkey\Forms;

defined( 'ABSPATH' ) || exit;

final class Submission_Manager {
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_post_type' ), 12 );
		add_action( 'wpcf7_before_send_mail', array( self::class, 'capture_rfq' ), 10, 3 );
		add_filter( 'wpcf7_skip_mail', array( self::class, 'disable_rfq_email' ), 10, 2 );
		add_filter( 'manage_skt_request_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_skt_request_posts_custom_column', array( self::class, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes_skt_request', array( self::class, 'add_details_box' ) );
		add_action( 'admin_menu', array( self::class, 'register_menu' ), 20 );
	}

	public static function register_post_type(): void {
		register_post_type(
			'skt_request',
			array(
				'labels' => array(
					'name'          => __( 'Submissions', 'smartkey-forms' ),
					'singular_name' => __( 'Submission', 'smartkey-forms' ),
					'edit_item'     => __( 'View Submission', 'smartkey-forms' ),
					'search_items'  => __( 'Search Submissions', 'smartkey-forms' ),
					'not_found'     => __( 'No submissions found', 'smartkey-forms' ),
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

	public static function register_menu(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$parent = menu_page_url( 'smartkey', false ) ? 'smartkey' : 'tools.php';
		add_submenu_page( $parent, __( 'Form Submissions', 'smartkey-forms' ), __( 'Submissions', 'smartkey-forms' ), 'manage_options', 'edit.php?post_type=skt_request' );
	}

	public static function disable_rfq_email( bool $skip, $contact_form ): bool {
		return self::is_rfq_form( $contact_form ) ? true : $skip;
	}

	public static function capture_rfq( $contact_form, &$abort, $submission ): void {
		if ( ! $submission && class_exists( '\WPCF7_Submission' ) ) {
			$submission = \WPCF7_Submission::get_instance();
		}
		if ( ! self::is_rfq_form( $contact_form ) || ! $submission ) {
			return;
		}
		$posted = $submission->get_posted_data();
		$data   = array();
		foreach ( (array) $posted as $key => $value ) {
			if ( str_starts_with( (string) $key, '_' ) || 'consent' === $key ) {
				continue;
			}
			$data[ sanitize_key( (string) $key ) ] = self::sanitize_value( $value );
		}
		$product = sanitize_text_field( (string) ( $data['product-grade'] ?? $data['product'] ?? 'General product request' ) );
		self::store( 'petrochemical', 'Petrochemical RFQ — ' . $product, $data, (int) ( $data['product-id'] ?? 0 ) );
	}

	public static function store( string $type, string $title, array $data, int $related_id = 0, int $form_id = 0 ): int {
		$request_id = wp_insert_post(
			array(
				'post_type'   => 'skt_request',
				'post_status' => 'publish',
				'post_title'  => wp_strip_all_tags( $title ),
			),
			true
		);
		if ( is_wp_error( $request_id ) ) {
			return 0;
		}
		update_post_meta( $request_id, 'skt_request_type', sanitize_key( $type ) );
		update_post_meta( $request_id, 'skt_request_data', self::sanitize_data( $data ) );
		update_post_meta( $request_id, 'skt_request_related_id', absint( $related_id ) );
		update_post_meta( $request_id, 'skt_request_form_id', absint( $form_id ) );
		update_post_meta( $request_id, 'skt_request_status', 'new' );

		/**
		 * New integration event. Notification providers can subscribe without
		 * becoming a dependency of the forms plugin.
		 */
		do_action( 'smartkey_forms_submission_created', (int) $request_id );

		/**
		 * Compatibility event for SmartKey Core 1.6.x Telegram integrations.
		 */
		do_action( 'skt_request_created', (int) $request_id );
		return (int) $request_id;
	}

	public static function columns( array $columns ): array {
		return array(
			'cb'          => $columns['cb'] ?? '',
			'title'       => __( 'Submission', 'smartkey-forms' ),
			'skt_type'    => __( 'Type', 'smartkey-forms' ),
			'skt_contact' => __( 'Contact', 'smartkey-forms' ),
			'skt_status'  => __( 'Status', 'smartkey-forms' ),
			'date'        => __( 'Received', 'smartkey-forms' ),
		);
	}

	public static function column_content( string $column, int $post_id ): void {
		$data = get_post_meta( $post_id, 'skt_request_data', true );
		$data = is_array( $data ) ? $data : array();
		if ( 'skt_type' === $column ) {
			echo esc_html( ucfirst( (string) get_post_meta( $post_id, 'skt_request_type', true ) ) );
		}
		if ( 'skt_contact' === $column ) {
			echo esc_html( (string) ( $data['name'] ?? $data['full-name'] ?? $data['your-name'] ?? '' ) );
			$email = (string) ( $data['email'] ?? $data['business-email'] ?? $data['your-email'] ?? '' );
			if ( $email ) {
				echo '<br><small>' . esc_html( $email ) . '</small>';
			}
		}
		if ( 'skt_status' === $column ) {
			echo '<strong>' . esc_html( ucfirst( (string) get_post_meta( $post_id, 'skt_request_status', true ) ) ) . '</strong>';
		}
	}

	public static function add_details_box(): void {
		add_meta_box( 'skf_submission_details', __( 'Submitted details', 'smartkey-forms' ), array( self::class, 'render_details_box' ), 'skt_request', 'normal', 'high' );
	}

	public static function render_details_box( \WP_Post $post ): void {
		$data = get_post_meta( $post->ID, 'skt_request_data', true );
		$data = is_array( $data ) ? $data : array();
		echo '<table class="widefat striped"><tbody>';
		foreach ( $data as $key => $value ) {
			echo '<tr><th style="width:220px">' . esc_html( ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) ) ) . '</th><td style="white-space:pre-wrap">' . esc_html( is_array( $value ) ? implode( ', ', $value ) : (string) $value ) . '</td></tr>';
		}
		echo '</tbody></table><p><small>' . esc_html__( 'Stored privately in WordPress. Email delivery is disabled for SmartKeyTurkey-managed forms.', 'smartkey-forms' ) . '</small></p>';
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

	private static function is_rfq_form( $contact_form ): bool {
		return is_object( $contact_form ) && method_exists( $contact_form, 'title' ) && 'Petrochemical RFQ' === (string) $contact_form->title();
	}

	private static function sanitize_data( array $data ): array {
		foreach ( $data as $key => $value ) {
			$clean[ sanitize_key( (string) $key ) ] = self::sanitize_value( $value );
		}
		return $clean ?? array();
	}

	private static function sanitize_value( $value ) {
		if ( is_array( $value ) ) {
			return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
		}
		return sanitize_textarea_field( (string) $value );
	}
}
