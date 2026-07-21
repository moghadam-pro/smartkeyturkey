<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Dashboard {
	private const NOTES_OPTION       = 'skt_dashboard_notes';
	private const DAILY_VIEWS_OPTION = 'skt_product_views_daily';
	private const TOTAL_RFQ_OPTION   = 'skt_rfq_total';
	private const UNASSIGNED_OPTION  = 'skt_rfq_unassigned';

	public static function init(): void {
		add_action( 'wp_dashboard_setup', array( self::class, 'register_widgets' ) );
		add_action( 'admin_post_skt_add_dashboard_note', array( self::class, 'handle_add_note' ) );
		add_action( 'template_redirect', array( self::class, 'track_content_view' ), 20 );
		add_action( 'wpcf7_mail_sent', array( self::class, 'track_product_request' ) );
	}

	public static function register_widgets(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'skt_core_overview',
			__( 'SmartKey Core Overview', 'smartkey-core' ),
			array( self::class, 'render_overview' )
		);

		wp_add_dashboard_widget(
			'skt_product_engagement',
			__( 'Product Views & Requests', 'smartkey-core' ),
			array( self::class, 'render_engagement' )
		);

		wp_add_dashboard_widget(
			'skt_content_views',
			__( 'Most Viewed Site Content', 'smartkey-core' ),
			array( self::class, 'render_content_views' )
		);

		wp_add_dashboard_widget(
			'skt_dashboard_notes',
			__( 'SmartKey Dashboard Notes', 'smartkey-core' ),
			array( self::class, 'render_notes' )
		);
	}

	public static function render_overview(): void {
		$product_counts  = wp_count_posts( 'skt_product' );
		$product_total   = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;
		$family_count    = wp_count_terms( array( 'taxonomy' => 'skt_product_family', 'hide_empty' => false ) );
		$family_total    = is_wp_error( $family_count ) ? 0 : (int) $family_count;
		$total_views     = self::sum_content_views();
		$viewed_products = self::count_viewed_content();
		$total_requests  = (int) get_option( self::TOTAL_RFQ_OPTION, 0 );
		$latest_update   = self::latest_product_update();
		?>
		<style>
			.skt-dashboard-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:4px 0 12px}.skt-dashboard-stat{border:1px solid #dcdcde;border-radius:8px;padding:12px;background:#fff}.skt-dashboard-stat strong{display:block;font-size:22px;line-height:1.2;color:#2c3338}.skt-dashboard-stat span{color:#646970}.skt-dashboard-meta{margin:8px 0 0;color:#50575e}.skt-dashboard-table{width:100%;border-collapse:collapse}.skt-dashboard-table th,.skt-dashboard-table td{padding:8px 6px;border-bottom:1px solid #f0f0f1;text-align:left}.skt-dashboard-note{padding:10px 0;border-bottom:1px solid #f0f0f1}.skt-dashboard-note p{margin:0 0 4px;white-space:pre-wrap}.skt-dashboard-note small{color:#646970}@media(max-width:782px){.skt-dashboard-grid{grid-template-columns:1fr}}
		</style>
		<div class="skt-dashboard-grid">
			<div class="skt-dashboard-stat"><strong><?php echo esc_html( number_format_i18n( $product_total ) ); ?></strong><span><?php esc_html_e( 'Published products', 'smartkey-core' ); ?></span></div>
			<div class="skt-dashboard-stat"><strong><?php echo esc_html( number_format_i18n( $family_total ) ); ?></strong><span><?php esc_html_e( 'Product families', 'smartkey-core' ); ?></span></div>
			<div class="skt-dashboard-stat"><strong><?php echo esc_html( number_format_i18n( $total_views ) ); ?></strong><span><?php esc_html_e( 'Recorded content views', 'smartkey-core' ); ?></span></div>
			<div class="skt-dashboard-stat"><strong><?php echo esc_html( number_format_i18n( $total_requests ) ); ?></strong><span><?php esc_html_e( 'Recorded product requests', 'smartkey-core' ); ?></span></div>
		</div>
		<p class="skt-dashboard-meta">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: last product update, 2: viewed content count */
					__( 'Latest product update: %1$s · Pages with recorded views: %2$d', 'smartkey-core' ),
					$latest_update,
					$viewed_products
				)
			);
			?>
		</p>
		<p><small><?php echo esc_html( sprintf( __( 'SmartKey Core version %s', 'smartkey-core' ), SKT_CORE_VERSION ) ); ?></small></p>
		<?php
	}

	public static function render_engagement(): void {
		$product_ids = get_posts(
			array(
				'post_type'      => 'skt_product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$rows = array_map(
			static function ( int $post_id ): array {
				return array(
					'id'       => $post_id,
					'title'    => get_the_title( $post_id ),
					'views'    => (int) get_post_meta( $post_id, 'skt_view_count', true ),
					'requests' => (int) get_post_meta( $post_id, 'skt_rfq_count', true ),
				);
			},
			array_map( 'intval', $product_ids )
		);

		usort(
			$rows,
			static fn( array $a, array $b ): int => ( $b['views'] + ( 5 * $b['requests'] ) ) <=> ( $a['views'] + ( 5 * $a['requests'] ) )
		);

		$rows       = array_slice( $rows, 0, 12 );
		$unassigned = (int) get_option( self::UNASSIGNED_OPTION, 0 );
		?>
		<table class="skt-dashboard-table">
			<thead><tr><th><?php esc_html_e( 'Product', 'smartkey-core' ); ?></th><th><?php esc_html_e( 'Views', 'smartkey-core' ); ?></th><th><?php esc_html_e( 'Requests', 'smartkey-core' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php echo esc_html( $row['title'] ); ?></a></td>
					<td><?php echo esc_html( number_format_i18n( $row['views'] ) ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $row['requests'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><small><?php echo esc_html( sprintf( __( 'Unassigned RFQ submissions: %d', 'smartkey-core' ), $unassigned ) ); ?></small></p>
		<p><small><?php esc_html_e( 'Views start accumulating after this dashboard release. RFQ counts store totals only; no submitted contact details are copied into SmartKey Core.', 'smartkey-core' ); ?></small></p>
		<?php
	}

	public static function render_notes(): void {
		$notes = get_option( self::NOTES_OPTION, array() );
		$notes = is_array( $notes ) ? array_slice( $notes, 0, 10 ) : array();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="skt_add_dashboard_note">
			<?php wp_nonce_field( 'skt_add_dashboard_note' ); ?>
			<p><label for="skt_dashboard_note"><strong><?php esc_html_e( 'Add an internal dashboard note', 'smartkey-core' ); ?></strong></label></p>
			<textarea id="skt_dashboard_note" name="skt_dashboard_note" rows="4" maxlength="1000" class="widefat" required></textarea>
			<?php submit_button( __( 'Add Note', 'smartkey-core' ), 'secondary', 'submit', false ); ?>
		</form>
		<div aria-live="polite">
			<?php if ( empty( $notes ) ) : ?>
				<p><?php esc_html_e( 'No dashboard notes yet.', 'smartkey-core' ); ?></p>
			<?php else : ?>
				<?php foreach ( $notes as $note ) : ?>
					<div class="skt-dashboard-note">
						<p><?php echo esc_html( $note['text'] ?? '' ); ?></p>
						<small><?php echo esc_html( sprintf( '%1$s · %2$s', $note['author'] ?? '', $note['created'] ?? '' ) ); ?></small>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function render_content_views(): void {
		$content_ids = get_posts(
			array(
				'post_type'      => array( 'page', 'post', 'skt_product' ),
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'fields'         => 'ids',
				'meta_key'       => 'skt_view_count', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);
		?>
		<?php if ( empty( $content_ids ) ) : ?>
			<p><?php esc_html_e( 'No public content views have been recorded yet.', 'smartkey-core' ); ?></p>
		<?php else : ?>
			<table class="skt-dashboard-table">
				<thead><tr><th><?php esc_html_e( 'Content', 'smartkey-core' ); ?></th><th><?php esc_html_e( 'Type', 'smartkey-core' ); ?></th><th><?php esc_html_e( 'Views', 'smartkey-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $content_ids as $content_id ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $content_id ) ); ?>"><?php echo esc_html( get_the_title( $content_id ) ); ?></a></td>
						<td><?php echo esc_html( get_post_type_object( get_post_type( $content_id ) )->labels->singular_name ?? get_post_type( $content_id ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (int) get_post_meta( $content_id, 'skt_view_count', true ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<p><small><?php esc_html_e( 'Counts exclude logged-in editors, previews and common crawler user agents, and de-duplicate the same browser/content pair for 24 hours.', 'smartkey-core' ); ?></small></p>
		<?php
	}

	public static function handle_add_note(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You are not allowed to add dashboard notes.', 'smartkey-core' ) );
		}

		check_admin_referer( 'skt_add_dashboard_note' );
		$text = sanitize_textarea_field( wp_unslash( $_POST['skt_dashboard_note'] ?? '' ) );
		if ( '' !== $text ) {
			$user  = wp_get_current_user();
			$notes = get_option( self::NOTES_OPTION, array() );
			$notes = is_array( $notes ) ? $notes : array();
			array_unshift(
				$notes,
				array(
				'text'    => wp_html_excerpt( $text, 1000, '' ),
					'author'  => $user->display_name,
					'created' => current_time( 'M j, Y H:i' ),
				)
			);
			update_option( self::NOTES_OPTION, array_slice( $notes, 0, 50 ), false );
		}

		wp_safe_redirect( admin_url( 'index.php#skt_dashboard_notes' ) );
		exit;
	}

	public static function track_content_view(): void {
		if ( ! is_singular( array( 'page', 'post', 'skt_product' ) ) || is_preview() || is_feed() || is_robots() ) {
			return;
		}

		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return;
		}

		$user_agent = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ) );
		if ( '' === $user_agent || preg_match( '/bot|crawl|spider|slurp|preview|monitor|headless/', $user_agent ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( $post_id < 1 ) {
			return;
		}

		$cookie_name = 'skt_content_view_' . $post_id;
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return;
		}

		update_post_meta( $post_id, 'skt_view_count', (int) get_post_meta( $post_id, 'skt_view_count', true ) + 1 );
		self::increment_daily_view( $post_id );

		if ( ! headers_sent() ) {
			setcookie(
				$cookie_name,
				'1',
				array(
					'expires'  => time() + DAY_IN_SECONDS,
					'path'     => COOKIEPATH ?: '/',
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}
	}

	public static function track_product_request( $contact_form ): void {
		if ( ! class_exists( 'WPCF7_Submission' ) || ! is_object( $contact_form ) ) {
			return;
		}

		$form_title = method_exists( $contact_form, 'title' ) ? (string) $contact_form->title() : '';
		if ( 'Petrochemical RFQ' !== $form_title ) {
			return;
		}

		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return;
		}

		$posted = $submission->get_posted_data();
		$value  = '';
		foreach ( array( 'product-grade', 'product_grade', 'product', 'grade' ) as $field ) {
			if ( ! empty( $posted[ $field ] ) && is_scalar( $posted[ $field ] ) ) {
				$value = sanitize_text_field( (string) $posted[ $field ] );
				break;
			}
		}

		update_option( self::TOTAL_RFQ_OPTION, (int) get_option( self::TOTAL_RFQ_OPTION, 0 ) + 1, false );
		$product_id = self::find_product_id( $value );
		if ( $product_id ) {
			update_post_meta( $product_id, 'skt_rfq_count', (int) get_post_meta( $product_id, 'skt_rfq_count', true ) + 1 );
			update_post_meta( $product_id, 'skt_last_rfq_date', current_time( 'mysql' ) );
			return;
		}

		update_option( self::UNASSIGNED_OPTION, (int) get_option( self::UNASSIGNED_OPTION, 0 ) + 1, false );
	}

	private static function find_product_id( string $value ): int {
		if ( '' === $value ) {
			return 0;
		}

		$by_title = get_posts(
			array(
				'post_type'      => 'skt_product',
				'post_status'    => 'publish',
				'title'          => $value,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( $by_title ) {
			return (int) $by_title[0];
		}

		$by_grade = get_posts(
			array(
				'post_type'      => 'skt_product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => 'skt_grade', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		return $by_grade ? (int) $by_grade[0] : 0;
	}

	private static function increment_daily_view( int $post_id ): void {
		$daily = get_option( self::DAILY_VIEWS_OPTION, array() );
		$daily = is_array( $daily ) ? $daily : array();
		$today = current_time( 'Y-m-d' );
		$daily[ $today ]             = isset( $daily[ $today ] ) && is_array( $daily[ $today ] ) ? $daily[ $today ] : array();
		$daily[ $today ][ $post_id ] = (int) ( $daily[ $today ][ $post_id ] ?? 0 ) + 1;
		$cutoff = gmdate( 'Y-m-d', time() - ( 30 * DAY_IN_SECONDS ) );
		$daily  = array_filter( $daily, static fn( string $date ): bool => $date >= $cutoff, ARRAY_FILTER_USE_KEY );
		update_option( self::DAILY_VIEWS_OPTION, $daily, false );
	}

	private static function sum_content_views(): int {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type IN ('page','post','skt_product') AND p.post_status = 'publish'",
				'skt_view_count'
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	private static function count_viewed_content(): int {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND CAST(pm.meta_value AS UNSIGNED) > 0 AND p.post_type IN ('page','post','skt_product') AND p.post_status = 'publish'",
				'skt_view_count'
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	private static function latest_product_update(): string {
		$latest = get_posts(
			array(
				'post_type'      => 'skt_product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		return $latest ? get_the_modified_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $latest[0] ) : __( 'No products yet', 'smartkey-core' );
	}
}
