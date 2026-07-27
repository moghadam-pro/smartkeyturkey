<?php

namespace NativeForms;

defined( 'ABSPATH' ) || exit;

final class Form_Manager {
	private const META_FIELDS = '_nf_fields';
	private const META_TYPE   = '_nf_submission_type';

	public static function init(): void {
		add_action( 'init', array( self::class, 'register_post_type' ), 11 );
		add_action( 'add_meta_boxes_nf_form', array( self::class, 'add_meta_boxes' ) );
		add_action( 'save_post_nf_form', array( self::class, 'save' ), 10, 2 );
		add_shortcode( 'native_form', array( self::class, 'shortcode' ) );
		add_action( 'admin_post_nf_submit', array( self::class, 'handle_submission' ) );
		add_action( 'admin_post_nopriv_nf_submit', array( self::class, 'handle_submission' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_assets' ) );
		add_filter( 'manage_nf_form_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_nf_form_posts_custom_column', array( self::class, 'column_content' ), 10, 2 );
		add_action( 'admin_menu', array( self::class, 'register_menu' ), 20 );
	}

	public static function register_assets(): void {
		wp_register_style( 'native-forms', plugins_url( 'assets/css/forms.css', NATIVE_FORMS_FILE ), array(), NATIVE_FORMS_VERSION );
	}

	public static function register_post_type(): void {
		register_post_type(
			'nf_form',
			array(
				'labels' => array(
					'name'          => __( 'Forms', 'native-forms' ),
					'singular_name' => __( 'Form', 'native-forms' ),
					'add_new_item'  => __( 'Add New Form', 'native-forms' ),
					'edit_item'     => __( 'Edit Form', 'native-forms' ),
					'not_found'     => __( 'No forms found', 'native-forms' ),
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
		add_menu_page(
			__( 'Native Forms', 'native-forms' ),
			__( 'Forms', 'native-forms' ),
			'edit_posts',
			'native-forms',
			array( self::class, 'overview' ),
			'dashicons-feedback',
			25
		);
		add_submenu_page( 'native-forms', __( 'Forms', 'native-forms' ), __( 'Forms', 'native-forms' ), 'edit_posts', 'edit.php?post_type=nf_form' );
		add_submenu_page( 'native-forms', __( 'Submissions', 'native-forms' ), __( 'Submissions', 'native-forms' ), 'manage_options', 'edit.php?post_type=nf_submission' );
		remove_submenu_page( 'native-forms', 'native-forms' );
	}

	public static function overview(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Native Forms', 'native-forms' ); ?></h1>
			<p><?php esc_html_e( 'Create lightweight forms and keep their submissions privately inside WordPress.', 'native-forms' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=nf_form' ) ); ?>"><?php esc_html_e( 'Manage Forms', 'native-forms' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=nf_submission' ) ); ?>"><?php esc_html_e( 'View Submissions', 'native-forms' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function add_meta_boxes(): void {
		add_meta_box( 'nf_builder', __( 'Form fields', 'native-forms' ), array( self::class, 'render_builder' ), 'nf_form', 'normal', 'high' );
		add_meta_box( 'nf_embed', __( 'Embed', 'native-forms' ), array( self::class, 'render_embed' ), 'nf_form', 'side', 'high' );
	}

	public static function render_builder( \WP_Post $post ): void {
		wp_nonce_field( 'nf_save_form', 'nf_nonce' );
		$fields = get_post_meta( $post->ID, self::META_FIELDS, true );
		$type   = get_post_meta( $post->ID, self::META_TYPE, true ) ?: 'general';
		?>
		<p><label for="nf-submission-type"><strong><?php esc_html_e( 'Submission type', 'native-forms' ); ?></strong></label></p>
		<input id="nf-submission-type" name="nf_submission_type" class="regular-text" value="<?php echo esc_attr( $type ); ?>" pattern="[a-z0-9_-]+">
		<p><label for="nf-fields"><strong><?php esc_html_e( 'Fields — one per line', 'native-forms' ); ?></strong></label></p>
		<p class="description"><?php esc_html_e( 'Format: type|name|label|required|options|help. Types: section, text, email, tel, number, textarea, select, radio, scale, checkbox. Separate options with commas. Labels, options and help text support Persian and other Unicode languages.', 'native-forms' ); ?></p>
		<textarea id="nf-fields" name="nf_fields" class="large-text code" rows="16" spellcheck="false"><?php echo esc_textarea( (string) $fields ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example: email|email|Email address|required||We will never publish your email.', 'native-forms' ); ?></p>
		<?php
	}

	public static function render_embed( \WP_Post $post ): void {
		echo '<p><code>[native_form id="' . esc_html( (string) $post->ID ) . '"]</code></p>';
		echo '<p>' . esc_html__( 'Use this shortcode in the block editor, Elementor or any shortcode-compatible page builder.', 'native-forms' ) . '</p>';
	}

	public static function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['nf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nf_nonce'] ) ), 'nf_save_form' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$fields = isset( $_POST['nf_fields'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nf_fields'] ) ) : '';
		$type   = isset( $_POST['nf_submission_type'] ) ? sanitize_key( wp_unslash( $_POST['nf_submission_type'] ) ) : 'general';
		update_post_meta( $post_id, self::META_FIELDS, $fields );
		update_post_meta( $post_id, self::META_TYPE, $type ?: 'general' );
	}

	public static function shortcode( array $atts ): string {
		wp_enqueue_style( 'native-forms' );
		$atts = shortcode_atts(
			array(
				'id'     => 0,
				'button' => __( 'Submit', 'native-forms' ),
				'sent'   => __( 'Thank you. Your submission has been recorded.', 'native-forms' ),
				'error'  => __( 'Please review the required fields and try again.', 'native-forms' ),
				'dir'    => 'auto',
				'select' => '',
				'yes'    => '',
			),
			$atts,
			'native_form'
		);
		$form_id = absint( $atts['id'] );
		if ( ! $form_id || 'nf_form' !== get_post_type( $form_id ) || 'publish' !== get_post_status( $form_id ) ) {
			return current_user_can( 'edit_posts' ) ? '<p>' . esc_html__( 'Select a published form.', 'native-forms' ) . '</p>' : '';
		}
		$fields = self::parse_fields( (string) get_post_meta( $form_id, self::META_FIELDS, true ) );
		if ( ! $fields ) {
			return '';
		}
		$direction = in_array( $atts['dir'], array( 'rtl', 'ltr' ), true ) ? $atts['dir'] : ( is_rtl() ? 'rtl' : 'ltr' );
		$is_rtl    = 'rtl' === $direction;
		$select    = $atts['select'] ?: ( $is_rtl ? 'انتخاب کنید' : __( 'Select', 'native-forms' ) );
		$yes       = $atts['yes'] ?: ( $is_rtl ? 'بله' : __( 'Yes', 'native-forms' ) );
		$status = isset( $_GET['nf_status'] ) ? sanitize_key( wp_unslash( $_GET['nf_status'] ) ) : '';
		ob_start();
		?>
		<div class="nf-form-wrap" dir="<?php echo esc_attr( $direction ); ?>">
			<?php if ( 'sent' === $status ) : ?><p class="nf-notice" role="status"><?php echo esc_html( (string) $atts['sent'] ); ?></p><?php endif; ?>
			<?php if ( 'error' === $status ) : ?><p class="nf-notice is-error" role="alert"><?php echo esc_html( (string) $atts['error'] ); ?></p><?php endif; ?>
			<form class="nf-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nf_submit">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( (string) $form_id ); ?>">
				<?php wp_nonce_field( 'nf_submit_' . $form_id, 'nf_nonce' ); ?>
				<?php foreach ( $fields as $field ) : self::render_field( $field, $select, $yes ); endforeach; ?>
				<label class="nf-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
				<button type="submit"><?php echo esc_html( (string) $atts['button'] ); ?></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function handle_submission(): void {
		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$referer = wp_get_referer() ?: home_url( '/' );
		if ( ! $form_id || 'nf_form' !== get_post_type( $form_id ) || 'publish' !== get_post_status( $form_id ) || ! isset( $_POST['nf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nf_nonce'] ) ), 'nf_submit_' . $form_id ) || ! empty( $_POST['website'] ) ) {
			self::redirect( $referer, 'error' );
		}
		$fields = self::parse_fields( (string) get_post_meta( $form_id, self::META_FIELDS, true ) );
		$data   = array();
		foreach ( $fields as $field ) {
			if ( 'section' === $field['type'] ) {
				continue;
			}
			$value = $_POST[ $field['name'] ] ?? '';
			$value = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : sanitize_textarea_field( wp_unslash( $value ) );
			if ( $field['required'] && ( '' === $value || array() === $value ) ) {
				self::redirect( $referer, 'error' );
			}
			if ( 'email' === $field['type'] && $value && ! is_email( $value ) ) {
				self::redirect( $referer, 'error' );
			}
			$data[ $field['name'] ] = $value;
		}
		$type = (string) get_post_meta( $form_id, self::META_TYPE, true );
		$id   = Submission_Manager::store( $type ?: 'general', get_the_title( $form_id ), $data, $form_id );
		self::redirect( $referer, $id ? 'sent' : 'error' );
	}

	public static function columns( array $columns ): array {
		$columns['nf_shortcode'] = __( 'Shortcode', 'native-forms' );
		return $columns;
	}

	public static function column_content( string $column, int $post_id ): void {
		if ( 'nf_shortcode' === $column ) {
			echo '<code>[native_form id="' . esc_html( (string) $post_id ) . '"]</code>';
		}
	}

	private static function parse_fields( string $definition ): array {
		$allowed = array( 'section', 'text', 'email', 'tel', 'number', 'textarea', 'select', 'radio', 'scale', 'checkbox' );
		$fields  = array();
		foreach ( preg_split( '/\R/u', $definition ) as $line ) {
			$line = trim( $line );
			if ( ! $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 6 ) );
			$type  = in_array( $parts[0] ?? '', $allowed, true ) ? $parts[0] : 'text';
			$name  = sanitize_key( $parts[1] ?? '' );
			if ( ! $name && 'section' !== $type ) {
				continue;
			}
			$fields[] = array(
				'type'     => $type,
				'name'     => $name,
				'label'    => sanitize_text_field( $parts[2] ?? $name ),
				'required' => 'required' === strtolower( $parts[3] ?? '' ),
				'options'  => array_filter( array_map( 'sanitize_text_field', explode( ',', $parts[4] ?? '' ) ) ),
				'help'     => sanitize_text_field( $parts[5] ?? '' ),
			);
		}
		return $fields;
	}

	private static function render_field( array $field, string $select_label, string $yes_label ): void {
		if ( 'section' === $field['type'] ) {
			echo '<section class="nf-section"><h2>' . esc_html( $field['label'] ) . '</h2>';
			if ( $field['help'] ) {
				echo '<p>' . esc_html( $field['help'] ) . '</p>';
			}
			echo '</section>';
			return;
		}
		$required = $field['required'] ? ' required' : '';
		$label    = $field['label'] . ( $field['required'] ? ' *' : '' );
		$class    = in_array( $field['type'], array( 'textarea', 'radio', 'scale', 'checkbox' ), true ) ? ' class="nf-field nf-field--wide"' : ' class="nf-field"';
		echo '<div' . $class . '><span class="nf-label">' . esc_html( $label ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( 'textarea' === $field['type'] ) {
			echo '<textarea name="' . esc_attr( $field['name'] ) . '" rows="5"' . $required . '></textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'select' === $field['type'] ) {
			echo '<select name="' . esc_attr( $field['name'] ) . '"' . $required . '><option value="">' . esc_html( $select_label ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			foreach ( $field['options'] as $option ) {
				echo '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
			}
			echo '</select>';
		} elseif ( in_array( $field['type'], array( 'radio', 'scale' ), true ) ) {
			echo '<div class="nf-choice-group' . ( 'scale' === $field['type'] ? ' nf-scale' : '' ) . '">';
			foreach ( $field['options'] as $option ) {
				echo '<label><input type="radio" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $option ) . '"' . $required . '><span>' . esc_html( $option ) . '</span></label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
		} elseif ( 'checkbox' === $field['type'] ) {
			echo '<label class="nf-checkbox"><input type="checkbox" name="' . esc_attr( $field['name'] ) . '" value="1"' . $required . '><span>' . esc_html( $yes_label ) . '</span></label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $field['name'] ) . '"' . $required . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $field['help'] ) {
			echo '<small class="nf-help">' . esc_html( $field['help'] ) . '</small>';
		}
		echo '</div>';
	}

	private static function redirect( string $url, string $status ): void {
		wp_safe_redirect( add_query_arg( 'nf_status', $status, remove_query_arg( 'nf_status', $url ) ) );
		exit;
	}
}
