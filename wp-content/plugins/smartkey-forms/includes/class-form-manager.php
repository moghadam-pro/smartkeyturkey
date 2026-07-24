<?php

namespace SmartKeyTurkey\Forms;

defined( 'ABSPATH' ) || exit;

final class Form_Manager {
	private const META_FIELDS = '_skf_fields';
	private const META_TYPE   = '_skf_submission_type';

	public static function init(): void {
		add_action( 'init', array( self::class, 'register_post_type' ), 11 );
		add_action( 'add_meta_boxes_skf_form', array( self::class, 'add_meta_boxes' ) );
		add_action( 'save_post_skf_form', array( self::class, 'save' ), 10, 2 );
		add_shortcode( 'smartkey_form', array( self::class, 'shortcode' ) );
		add_action( 'admin_post_skf_submit', array( self::class, 'handle_submission' ) );
		add_action( 'admin_post_nopriv_skf_submit', array( self::class, 'handle_submission' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_assets' ) );
		add_filter( 'manage_skf_form_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_skf_form_posts_custom_column', array( self::class, 'column_content' ), 10, 2 );
		add_action( 'admin_menu', array( self::class, 'register_menu' ), 20 );
	}

	public static function register_assets(): void {
		wp_register_style( 'smartkey-forms', plugins_url( 'assets/css/forms.css', SKF_FILE ), array(), SKF_VERSION );
	}

	public static function register_post_type(): void {
		register_post_type(
			'skf_form',
			array(
				'labels' => array(
					'name'          => __( 'Forms', 'smartkey-forms' ),
					'singular_name' => __( 'Form', 'smartkey-forms' ),
					'add_new_item'  => __( 'Add New Form', 'smartkey-forms' ),
					'edit_item'     => __( 'Edit Form', 'smartkey-forms' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => false,
				'supports'     => array( 'title' ),
			)
		);
	}

	public static function register_menu(): void {
		$parent = menu_page_url( 'smartkey', false ) ? 'smartkey' : 'tools.php';
		add_submenu_page( $parent, __( 'SmartKey Forms', 'smartkey-forms' ), __( 'Forms', 'smartkey-forms' ), 'edit_posts', 'edit.php?post_type=skf_form' );
	}

	public static function add_meta_boxes(): void {
		add_meta_box( 'skf_builder', __( 'Form fields', 'smartkey-forms' ), array( self::class, 'render_builder' ), 'skf_form', 'normal', 'high' );
		add_meta_box( 'skf_embed', __( 'Embed', 'smartkey-forms' ), array( self::class, 'render_embed' ), 'skf_form', 'side', 'high' );
	}

	public static function render_builder( \WP_Post $post ): void {
		wp_nonce_field( 'skf_save_form', 'skf_nonce' );
		$fields = get_post_meta( $post->ID, self::META_FIELDS, true );
		$type   = get_post_meta( $post->ID, self::META_TYPE, true ) ?: 'general';
		?>
		<p><label for="skf-submission-type"><strong><?php esc_html_e( 'Submission type', 'smartkey-forms' ); ?></strong></label></p>
		<input id="skf-submission-type" name="skf_submission_type" class="regular-text" value="<?php echo esc_attr( $type ); ?>" pattern="[a-z0-9_-]+">
		<p><label for="skf-fields"><strong><?php esc_html_e( 'Fields — one per line', 'smartkey-forms' ); ?></strong></label></p>
		<p class="description"><?php esc_html_e( 'Format: type|name|label|required|options. Supported types: text, email, tel, number, textarea, select, checkbox. Separate select options with commas.', 'smartkey-forms' ); ?></p>
		<textarea id="skf-fields" name="skf_fields" class="large-text code" rows="14" spellcheck="false"><?php echo esc_textarea( (string) $fields ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example: email|email|Business email|required', 'smartkey-forms' ); ?></p>
		<?php
	}

	public static function render_embed( \WP_Post $post ): void {
		echo '<p><code>[smartkey_form id="' . esc_html( (string) $post->ID ) . '"]</code></p>';
		echo '<p>' . esc_html__( 'Use the shortcode widget in Elementor. The form and its stored submissions remain independent from SmartKey Core.', 'smartkey-forms' ) . '</p>';
	}

	public static function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['skf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['skf_nonce'] ) ), 'skf_save_form' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$fields = isset( $_POST['skf_fields'] ) ? sanitize_textarea_field( wp_unslash( $_POST['skf_fields'] ) ) : '';
		$type   = isset( $_POST['skf_submission_type'] ) ? sanitize_key( wp_unslash( $_POST['skf_submission_type'] ) ) : 'general';
		update_post_meta( $post_id, self::META_FIELDS, $fields );
		update_post_meta( $post_id, self::META_TYPE, $type ?: 'general' );
	}

	public static function shortcode( array $atts ): string {
		wp_enqueue_style( 'smartkey-forms' );
		$atts    = shortcode_atts( array( 'id' => 0, 'button' => __( 'Send request', 'smartkey-forms' ) ), $atts, 'smartkey_form' );
		$form_id = absint( $atts['id'] );
		if ( ! $form_id || 'skf_form' !== get_post_type( $form_id ) || 'publish' !== get_post_status( $form_id ) ) {
			return current_user_can( 'edit_posts' ) ? '<p>' . esc_html__( 'Select a published SmartKey form.', 'smartkey-forms' ) . '</p>' : '';
		}
		$fields = self::parse_fields( (string) get_post_meta( $form_id, self::META_FIELDS, true ) );
		if ( ! $fields ) {
			return '';
		}
		$status = isset( $_GET['skf_status'] ) ? sanitize_key( wp_unslash( $_GET['skf_status'] ) ) : '';
		ob_start();
		?>
		<div class="skf-form-wrap">
			<?php if ( 'sent' === $status ) : ?><p class="skf-notice" role="status"><?php esc_html_e( 'Thank you. Your request has been recorded.', 'smartkey-forms' ); ?></p><?php endif; ?>
			<?php if ( 'error' === $status ) : ?><p class="skf-notice is-error" role="alert"><?php esc_html_e( 'Please review the required fields and try again.', 'smartkey-forms' ); ?></p><?php endif; ?>
			<form class="skf-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="skf_submit">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( (string) $form_id ); ?>">
				<?php wp_nonce_field( 'skf_submit_' . $form_id, 'skf_nonce' ); ?>
				<?php foreach ( $fields as $field ) : self::render_field( $field ); endforeach; ?>
				<label class="skf-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
				<button type="submit"><?php echo esc_html( (string) $atts['button'] ); ?></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function handle_submission(): void {
		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$referer = wp_get_referer() ?: home_url( '/' );
		if ( ! $form_id || 'skf_form' !== get_post_type( $form_id ) || ! isset( $_POST['skf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['skf_nonce'] ) ), 'skf_submit_' . $form_id ) || ! empty( $_POST['website'] ) ) {
			self::redirect( $referer, 'error' );
		}
		$fields = self::parse_fields( (string) get_post_meta( $form_id, self::META_FIELDS, true ) );
		$data   = array();
		foreach ( $fields as $field ) {
			$value = $_POST[ $field['name'] ] ?? '';
			$value = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : sanitize_textarea_field( wp_unslash( $value ) );
			if ( $field['required'] && '' === $value ) {
				self::redirect( $referer, 'error' );
			}
			if ( 'email' === $field['type'] && $value && ! is_email( $value ) ) {
				self::redirect( $referer, 'error' );
			}
			$data[ $field['name'] ] = $value;
		}
		$type = (string) get_post_meta( $form_id, self::META_TYPE, true );
		$id   = Submission_Manager::store( $type ?: 'general', get_the_title( $form_id ), $data, 0, $form_id );
		self::redirect( $referer, $id ? 'sent' : 'error' );
	}

	public static function columns( array $columns ): array {
		$columns['skf_shortcode'] = __( 'Shortcode', 'smartkey-forms' );
		return $columns;
	}

	public static function column_content( string $column, int $post_id ): void {
		if ( 'skf_shortcode' === $column ) {
			echo '<code>[smartkey_form id="' . esc_html( (string) $post_id ) . '"]</code>';
		}
	}

	private static function parse_fields( string $definition ): array {
		$allowed = array( 'text', 'email', 'tel', 'number', 'textarea', 'select', 'checkbox' );
		$fields  = array();
		foreach ( preg_split( '/\R/', $definition ) as $line ) {
			$line = trim( $line );
			if ( ! $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 5 ) );
			$type  = in_array( $parts[0] ?? '', $allowed, true ) ? $parts[0] : 'text';
			$name  = sanitize_key( $parts[1] ?? '' );
			if ( ! $name ) {
				continue;
			}
			$fields[] = array(
				'type'     => $type,
				'name'     => $name,
				'label'    => sanitize_text_field( $parts[2] ?? $name ),
				'required' => 'required' === strtolower( $parts[3] ?? '' ),
				'options'  => array_filter( array_map( 'sanitize_text_field', explode( ',', $parts[4] ?? '' ) ) ),
			);
		}
		return $fields;
	}

	private static function render_field( array $field ): void {
		$required = $field['required'] ? ' required' : '';
		$label    = $field['label'] . ( $field['required'] ? ' *' : '' );
		echo '<label>' . esc_html( $label );
		if ( 'textarea' === $field['type'] ) {
			echo '<textarea name="' . esc_attr( $field['name'] ) . '" rows="5"' . $required . '></textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'select' === $field['type'] ) {
			echo '<select name="' . esc_attr( $field['name'] ) . '"' . $required . '><option value="">' . esc_html__( 'Select', 'smartkey-forms' ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			foreach ( $field['options'] as $option ) {
				echo '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'checkbox' === $field['type'] ) {
			echo '<input type="checkbox" name="' . esc_attr( $field['name'] ) . '" value="1"' . $required . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $field['name'] ) . '"' . $required . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</label>';
	}

	private static function redirect( string $url, string $status ): void {
		wp_safe_redirect( add_query_arg( 'skf_status', $status, remove_query_arg( 'skf_status', $url ) ) );
		exit;
	}
}
