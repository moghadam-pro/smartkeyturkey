<?php

namespace SmartKeyTurkey\Core;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Product_Importer {
	private const DATASET_OPTION = 'skt_product_import_dataset';
	private const CURSOR_OPTION  = 'skt_product_import_cursor';
	private const NOTICE_OPTION  = 'skt_product_import_notice';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ) );
		add_action( 'admin_post_skt_upload_product_dataset', array( self::class, 'handle_upload' ) );
		add_action( 'admin_post_skt_import_product_batch', array( self::class, 'handle_import' ) );
		add_action( 'admin_post_skt_reset_product_cursor', array( self::class, 'handle_reset' ) );
	}

	public static function register_page(): void {
		add_management_page(
			__( 'SmartKey Product Importer', 'smartkey-core' ),
			__( 'SmartKey Product Importer', 'smartkey-core' ),
			'manage_options',
			'skt-product-importer',
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$dataset = self::get_dataset();
		$cursor  = (int) get_option( self::CURSOR_OPTION, 0 );
		$notice  = get_option( self::NOTICE_OPTION, array() );
		delete_option( self::NOTICE_OPTION );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SmartKey Product Importer', 'smartkey-core' ); ?></h1>
			<p><?php esc_html_e( 'Imports source-reviewed products as drafts. Existing products are updated by source URL; duplicate posts are not created.', 'smartkey-core' ); ?></p>

			<?php if ( ! empty( $notice['message'] ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ?? 'info' ); ?> is-dismissible"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( '1. Upload dataset', 'smartkey-core' ); ?></h2>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="skt_upload_product_dataset">
				<?php wp_nonce_field( 'skt_upload_product_dataset' ); ?>
				<input type="file" name="product_dataset" accept="application/json,.json" required>
				<?php submit_button( __( 'Upload and validate JSON', 'smartkey-core' ), 'secondary', 'submit', false ); ?>
			</form>

			<h2><?php esc_html_e( '2. Import controlled batch', 'smartkey-core' ); ?></h2>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: dataset count, 2: cursor */
						__( 'Validated dataset: %1$d products. Current cursor: %2$d.', 'smartkey-core' ),
						count( $dataset ),
						$cursor
					)
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="skt_import_product_batch">
				<?php wp_nonce_field( 'skt_import_product_batch' ); ?>
				<label for="batch_size"><?php esc_html_e( 'Batch size', 'smartkey-core' ); ?></label>
				<input id="batch_size" name="batch_size" type="number" min="1" max="20" value="5">
				<label><input type="checkbox" name="import_images" value="1"> <?php esc_html_e( 'Import authorized featured images', 'smartkey-core' ); ?></label>
				<?php
				submit_button(
					__( 'Import next batch as drafts', 'smartkey-core' ),
					'primary',
					'submit',
					false,
					empty( $dataset ) ? array( 'disabled' => 'disabled' ) : array()
				);
				?>
			</form>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="skt_reset_product_cursor">
				<?php wp_nonce_field( 'skt_reset_product_cursor' ); ?>
				<?php submit_button( __( 'Reset cursor to 0', 'smartkey-core' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_upload(): void {
		self::guard( 'skt_upload_product_dataset' );

		if ( empty( $_FILES['product_dataset']['tmp_name'] ) ) {
			self::redirect_with_notice( 'error', __( 'No JSON file was received.', 'smartkey-core' ) );
		}

		$file = $_FILES['product_dataset'];
		if ( ! empty( $file['error'] ) || (int) $file['size'] > 5 * MB_IN_BYTES ) {
			self::redirect_with_notice( 'error', __( 'The JSON upload failed or exceeded 5 MB.', 'smartkey-core' ) );
		}

		$json = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( (string) $json, true );
		if ( ! is_array( $data ) || empty( $data ) ) {
			self::redirect_with_notice( 'error', __( 'The file is not a valid non-empty JSON product array.', 'smartkey-core' ) );
		}

		foreach ( $data as $index => $product ) {
			if ( ! is_array( $product ) || empty( $product['product_name'] ) || empty( $product['slug'] ) || empty( $product['source_url'] ) ) {
				self::redirect_with_notice( 'error', sprintf( __( 'Product row %d is missing a required field.', 'smartkey-core' ), $index + 1 ) );
			}
		}

		update_option( self::DATASET_OPTION, wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), false );
		update_option( self::CURSOR_OPTION, 0, false );
		self::redirect_with_notice( 'success', sprintf( __( 'Validated %d products. The import cursor was reset.', 'smartkey-core' ), count( $data ) ) );
	}

	public static function handle_import(): void {
		self::guard( 'skt_import_product_batch' );

		$dataset      = self::get_dataset();
		$cursor       = (int) get_option( self::CURSOR_OPTION, 0 );
		$batch_size   = max( 1, min( 20, absint( $_POST['batch_size'] ?? 5 ) ) );
		$with_images  = isset( $_POST['import_images'] );
		$batch        = array_slice( $dataset, $cursor, $batch_size );
		$created      = 0;
		$updated      = 0;
		$image_errors = 0;

		foreach ( $batch as $product ) {
			$result = self::upsert_product( $product, $with_images );
			if ( is_wp_error( $result ) ) {
				continue;
			}
			$created      += (int) $result['created'];
			$updated      += (int) $result['updated'];
			$image_errors += (int) $result['image_error'];
		}

		$new_cursor = min( count( $dataset ), $cursor + count( $batch ) );
		update_option( self::CURSOR_OPTION, $new_cursor, false );
		self::redirect_with_notice(
			'success',
			sprintf(
				__( 'Batch complete: %1$d created, %2$d updated, %3$d image errors. Cursor: %4$d/%5$d.', 'smartkey-core' ),
				$created,
				$updated,
				$image_errors,
				$new_cursor,
				count( $dataset )
			)
		);
	}

	public static function handle_reset(): void {
		self::guard( 'skt_reset_product_cursor' );
		update_option( self::CURSOR_OPTION, 0, false );
		self::redirect_with_notice( 'success', __( 'The import cursor was reset to 0.', 'smartkey-core' ) );
	}

	private static function upsert_product( array $product, bool $with_images ): array|WP_Error {
		$existing = get_posts(
			array(
				'post_type'      => 'skt_product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'skt_source_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => esc_url_raw( $product['source_url'] ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		$post_data = array(
			'ID'           => $existing ? (int) $existing[0] : 0,
			'post_type'    => 'skt_product',
			'post_status'  => 'draft',
			'post_title'   => sanitize_text_field( $product['product_name'] ),
			'post_name'    => sanitize_title( $product['slug'] ),
			'post_excerpt' => sanitize_textarea_field( $product['short_description'] ?? '' ),
			'post_content' => self::build_content( $product ),
		);

		$post_id = wp_insert_post( wp_slash( $post_data ), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$family = sanitize_text_field( $product['product_family'] ?? 'Uncategorized' );
		wp_set_object_terms( $post_id, $family, 'skt_product_family', false );

		$meta = array(
			'skt_grade'                     => $product['grade'] ?? '',
			'skt_source_url'                => $product['source_url'] ?? '',
			'skt_source_image_url'          => $product['source_image_url'] ?? '',
			'skt_image_rights_status'       => 'Authorized for SmartKey publication — owner confirmed 2026-07-21',
			'skt_applications'              => implode( "\n", array_map( 'sanitize_text_field', $product['applications'] ?? array() ) ),
			'skt_technical_properties'      => wp_json_encode( $product['properties'] ?? array(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
			'skt_verification_status'       => 'Source captured; technical review pending',
			'skt_availability_status'       => 'Confirm by RFQ',
			'skt_representative_disclosure' => $product['intermediary_disclosure'] ?? '',
			'skt_last_reviewed_date'        => $product['last_reviewed_date'] ?? '2026-07-21',
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$image_error = 0;
		if ( $with_images && ! has_post_thumbnail( $post_id ) && ! empty( $product['source_image_url'] ) ) {
			$image_result = self::import_image( $post_id, esc_url_raw( $product['source_image_url'] ), $product['product_name'] );
			$image_error  = is_wp_error( $image_result ) ? 1 : 0;
		}

		return array(
			'created'     => $existing ? 0 : 1,
			'updated'     => $existing ? 1 : 0,
			'image_error' => $image_error,
		);
	}

	private static function build_content( array $product ): string {
		$content  = '<p>' . esc_html( $product['short_description'] ?? '' ) . '</p>';
		$content .= '<p><strong>' . esc_html__( 'Representative disclosure:', 'smartkey-core' ) . '</strong> ' . esc_html( $product['intermediary_disclosure'] ?? '' ) . '</p>';
		$content .= '<h2>' . esc_html__( 'Applications', 'smartkey-core' ) . '</h2><ul>';
		foreach ( $product['applications'] ?? array() as $application ) {
			$content .= '<li>' . esc_html( $application ) . '</li>';
		}
		$content .= '</ul>';
		$content .= '<h2>' . esc_html__( 'Technical properties', 'smartkey-core' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'Technical values are source-captured and pending confirmation against current supplier documentation.', 'smartkey-core' ) . '</p>';
		$content .= '<div class="skt-product-properties"><table><tbody>';
		foreach ( $product['properties'] ?? array() as $property ) {
			$content .= '<tr>';
			foreach ( $property as $key => $value ) {
				$content .= '<th>' . esc_html( $key ) . '</th><td>' . esc_html( $value ) . '</td>';
			}
			$content .= '</tr>';
		}
		$content .= '</tbody></table></div>';
		return wp_kses_post( $content );
	}

	private static function import_image( int $post_id, string $url, string $title ): int|WP_Error {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_sideload_image( $url, $post_id, sanitize_text_field( $title ), 'id' );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $title . ' product image' ) );
		update_post_meta( $attachment_id, 'skt_source_image_url', $url );
		update_post_meta( $attachment_id, 'skt_image_rights_status', 'Authorized for SmartKey publication — owner confirmed 2026-07-21' );
		set_post_thumbnail( $post_id, $attachment_id );
		return (int) $attachment_id;
	}

	private static function get_dataset(): array {
		$data = json_decode( (string) get_option( self::DATASET_OPTION, '[]' ), true );
		return is_array( $data ) ? $data : array();
	}

	private static function guard( string $nonce_action ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to run this importer.', 'smartkey-core' ) );
		}
		check_admin_referer( $nonce_action );
	}

	private static function redirect_with_notice( string $type, string $message ): void {
		update_option( self::NOTICE_OPTION, array( 'type' => $type, 'message' => $message ), false );
		wp_safe_redirect( admin_url( 'tools.php?page=skt-product-importer' ) );
		exit;
	}
}
