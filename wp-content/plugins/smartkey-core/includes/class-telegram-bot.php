<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

/** Private Telegram operations bot. The long-polling worker calls run(). */
final class Telegram_Bot {
	private const STATE_PREFIX = 'skt_tg_state_';
	private const QUEUE_OPTION = 'skt_tg_request_queue';
	private const OFFSET_OPTION = 'skt_tg_update_offset';
	private const META_FIELDS = array(
		array( 'reference', 'Listing reference', 'skt_property_reference', true ),
		array( 'district', 'District / public area', 'skt_property_district', true ),
		array( 'transaction', 'Transaction type: sale or rent', 'skt_property_transaction_type', true ),
		array( 'listing_status', 'Listing status: available, sold or rented', 'skt_property_listing_status', true ),
		array( 'rooms', 'Rooms (for example 2+1)', 'skt_property_rooms', true ),
		array( 'bathrooms', 'Number of bathrooms', 'skt_property_bathrooms', true ),
		array( 'gross_area', 'Gross area (for example 115 m²)', 'skt_property_gross_area', true ),
		array( 'net_area', 'Net area (for example 92 m²)', 'skt_property_net_area', true ),
		array( 'new_build', 'Is it a new build? Reply Yes or No', 'skt_property_new_build', true ),
		array( 'construction_year', 'Construction year', 'skt_property_construction_year', false ),
		array( 'floor', 'Floor', 'skt_property_floor', false ),
		array( 'parking', 'Parking status', 'skt_property_parking', false ),
		array( 'furnished', 'Furnished status', 'skt_property_furnished', false ),
		array( 'amenities', 'Amenities (comma-separated)', 'skt_property_amenities', false ),
		array( 'developer', 'Developer', 'skt_property_developer', false ),
		array( 'payment_terms', 'Payment terms', 'skt_property_payment_terms', false ),
		array( 'delivery_date', 'Delivery date', 'skt_property_delivery_date', false ),
		array( 'completion_status', 'Completion status', 'skt_property_completion_status', false ),
		array( 'title_status', 'Title / document status', 'skt_property_title_status', false ),
		array( 'citizenship_review', 'Citizenship review status', 'skt_property_citizenship_review', false ),
		array( 'latitude', 'Latitude', 'skt_property_latitude', false ),
		array( 'longitude', 'Longitude', 'skt_property_longitude', false ),
		array( 'source', 'Source / property file', 'skt_property_source', true ),
		array( 'verification', 'Verification status', 'skt_property_verification_status', true ),
		array( 'reviewed', 'Last reviewed date (YYYY-MM-DD)', 'skt_property_last_reviewed_date', true ),
		array( 'disclosure', 'Direct-control disclosure', 'skt_property_control_disclosure', true ),
	);

	public static function init(): void {
		add_action( 'skt_request_created', array( self::class, 'queue_request' ) );
	}

	public static function queue_request( int $request_id ): void {
		$queue = get_option( self::QUEUE_OPTION, array() );
		$queue = is_array( $queue ) ? $queue : array();
		$queue[] = $request_id;
		update_option( self::QUEUE_OPTION, array_values( array_unique( array_map( 'absint', $queue ) ) ), false );
	}

	public static function run(): void {
		if ( ! self::token() ) { throw new \RuntimeException( 'SMARTKEY_TELEGRAM_BOT_TOKEN is not configured.' ); }
		while ( true ) {
			self::deliver_request_queue();
			$offset = (int) get_option( self::OFFSET_OPTION, 0 );
			$result = self::api( 'getUpdates', array( 'offset' => $offset, 'timeout' => 25, 'allowed_updates' => wp_json_encode( array( 'message', 'callback_query' ) ) ) );
			foreach ( (array) ( $result['result'] ?? array() ) as $update ) {
				update_option( self::OFFSET_OPTION, (int) $update['update_id'] + 1, false );
				self::handle_update( $update );
			}
		}
	}

	private static function handle_update( array $update ): void {
		$callback = $update['callback_query'] ?? null;
		$message = $callback['message'] ?? ( $update['message'] ?? array() );
		$chat_id = (int) ( $message['chat']['id'] ?? 0 );
		$user_id = (int) ( $callback['from']['id'] ?? $message['from']['id'] ?? 0 );
		if ( ! $chat_id || ! in_array( $user_id, self::allowed_users(), true ) ) { return; }
		if ( $callback ) {
			self::api( 'answerCallbackQuery', array( 'callback_query_id' => $callback['id'] ) );
			self::handle_callback( $chat_id, (string) ( $callback['data'] ?? '' ) );
			return;
		}
		$text = trim( (string) ( $message['text'] ?? $message['caption'] ?? '' ) );
		if ( '/start' === $text || '/cancel' === $text || 'Cancel' === $text ) { self::clear_state( $user_id ); self::menu( $chat_id ); return; }
		if ( 'Requests' === $text ) { self::show_requests( $chat_id ); return; }
		if ( 'Add New Property' === $text ) { self::start_property( $chat_id, $user_id ); return; }
		if ( 'Property Status' === $text ) { self::show_properties( $chat_id ); return; }
		$state = self::state( $user_id );
		if ( empty( $state ) ) { self::menu( $chat_id ); return; }
		if ( 'photos' === ( $state['step'] ?? '' ) ) { self::handle_photos( $chat_id, $user_id, $message, $text, $state ); return; }
		self::handle_field( $chat_id, $user_id, $text, $state );
	}

	private static function menu( int $chat_id ): void {
		self::send( $chat_id, 'SmartKeyTurkey Operations', array( 'keyboard' => array( array( array( 'text' => 'Requests' ), array( 'text' => 'Add New Property' ) ), array( array( 'text' => 'Property Status' ) ) ), 'resize_keyboard' => true ) );
	}

	private static function start_property( int $chat_id, int $user_id ): void {
		$state = array( 'step' => 'title', 'data' => array(), 'photos' => array() );
		self::set_state( $user_id, $state );
		self::send( $chat_id, 'Enter the property title.', self::cancel_keyboard() );
	}

	private static function handle_field( int $chat_id, int $user_id, string $text, array $state ): void {
		if ( '' === $text ) { self::send( $chat_id, 'Please send a text value.' ); return; }
		$step = (string) $state['step'];
		if ( 'title' === $step ) { $state['data']['title'] = sanitize_text_field( $text ); $state['step'] = 'summary'; self::set_state( $user_id, $state ); self::send( $chat_id, 'Enter a short public summary.' ); return; }
		if ( 'summary' === $step ) { $state['data']['summary'] = sanitize_textarea_field( $text ); $state['step'] = 'description'; self::set_state( $user_id, $state ); self::send( $chat_id, 'Enter the full property description.' ); return; }
		if ( 'description' === $step ) { $state['data']['description'] = sanitize_textarea_field( $text ); $state['step'] = 'city'; self::set_state( $user_id, $state ); self::send( $chat_id, 'Enter the city.' ); return; }
		if ( 'city' === $step ) { $state['data']['city'] = sanitize_text_field( $text ); $state['step'] = 'property_type'; self::set_state( $user_id, $state ); self::send( $chat_id, 'Enter the property type (for example Apartment or Villa).' ); return; }
		if ( 'property_type' === $step ) { $state['data']['property_type'] = sanitize_text_field( $text ); $state['step'] = 'meta_0'; self::set_state( $user_id, $state ); self::ask_meta( $chat_id, 0 ); return; }
		if ( str_starts_with( $step, 'meta_' ) ) {
			$index = (int) substr( $step, 5 ); $field = self::META_FIELDS[ $index ] ?? null;
			if ( ! $field ) { return; }
			if ( 'Skip' === $text && ! $field[3] ) { $value = ''; } else { $value = sanitize_textarea_field( $text ); }
			if ( $field[3] && '' === $value ) { self::send( $chat_id, 'This field is required.' ); return; }
			if ( 'new_build' === $field[0] ) { $value = in_array( strtolower( $value ), array( 'yes', 'y', '1', 'true' ), true ) ? '1' : '0'; }
			$state['data'][ $field[2] ] = $value; $index++;
			if ( $index < count( self::META_FIELDS ) ) { $state['step'] = 'meta_' . $index; self::set_state( $user_id, $state ); self::ask_meta( $chat_id, $index ); return; }
			$state['step'] = 'photos'; self::set_state( $user_id, $state );
			self::send( $chat_id, 'Send all property photos now. You may select and send multiple photos at once. Tap Finish Photos when done.', array( 'keyboard' => array( array( array( 'text' => 'Finish Photos' ) ), array( array( 'text' => 'Cancel' ) ) ), 'resize_keyboard' => true ) );
		}
	}

	private static function ask_meta( int $chat_id, int $index ): void {
		$field = self::META_FIELDS[ $index ]; $markup = $field[3] ? null : array( 'keyboard' => array( array( array( 'text' => 'Skip' ) ), array( array( 'text' => 'Cancel' ) ) ), 'resize_keyboard' => true );
		self::send( $chat_id, $field[1] . ( $field[3] ? '' : ' (optional)' ), $markup );
	}

	private static function handle_photos( int $chat_id, int $user_id, array $message, string $text, array $state ): void {
		if ( 'Finish Photos' === $text ) {
			if ( empty( $state['photos'] ) ) { self::send( $chat_id, 'Please send at least one photo.' ); return; }
			self::create_property( $chat_id, $user_id, $state ); return;
		}
		$photos = $message['photo'] ?? array();
		if ( empty( $photos ) ) { self::send( $chat_id, 'Please send photos, or tap Finish Photos.' ); return; }
		$largest = end( $photos ); $state['photos'][] = sanitize_text_field( (string) $largest['file_id'] );
		$state['photos'] = array_values( array_unique( $state['photos'] ) ); self::set_state( $user_id, $state );
		self::send( $chat_id, sprintf( '%d photo(s) received.', count( $state['photos'] ) ) );
	}

	private static function create_property( int $chat_id, int $user_id, array $state ): void {
		$data = $state['data'];
		$post_id = wp_insert_post( array( 'post_type' => 'skt_property', 'post_status' => 'draft', 'post_title' => $data['title'], 'post_excerpt' => $data['summary'], 'post_content' => wpautop( esc_html( $data['description'] ) ) ), true );
		if ( is_wp_error( $post_id ) ) { self::send( $chat_id, 'The property could not be created. Please try again.' ); return; }
		wp_set_object_terms( $post_id, $data['city'], 'skt_property_city' ); wp_set_object_terms( $post_id, $data['property_type'], 'skt_property_type' );
		foreach ( self::META_FIELDS as $field ) { if ( array_key_exists( $field[2], $data ) ) { update_post_meta( $post_id, $field[2], $data[ $field[2] ] ); } }
		$year = (int) ( $data['skt_property_construction_year'] ?? 0 ); update_post_meta( $post_id, 'skt_property_building_age', ! empty( $data['skt_property_new_build'] ) ? 'New' : ( $year ? (string) max( 0, (int) current_time( 'Y' ) - $year ) : '' ) );
		$attachment_ids = array(); foreach ( $state['photos'] as $file_id ) { $id = self::import_photo( $file_id, (int) $post_id ); if ( $id ) { $attachment_ids[] = $id; } }
		if ( $attachment_ids ) { set_post_thumbnail( $post_id, $attachment_ids[0] ); update_post_meta( $post_id, 'skt_property_gallery_ids', implode( ',', $attachment_ids ) ); }
		self::clear_state( $user_id );
		self::send( $chat_id, sprintf( "Property #%d was created as Draft with %d photo(s).", $post_id, count( $attachment_ids ) ), array( 'inline_keyboard' => array( array( array( 'text' => 'Publish', 'callback_data' => 'status:' . $post_id . ':publish' ) ) ) ) ); self::menu( $chat_id );
	}

	private static function import_photo( string $file_id, int $post_id ): int {
		$file = self::api( 'getFile', array( 'file_id' => $file_id ) ); $path = (string) ( $file['result']['file_path'] ?? '' ); if ( ! $path ) { return 0; }
		$url = 'https://api.telegram.org/file/bot' . self::token() . '/' . ltrim( $path, '/' );
		require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = download_url( $url, 60 ); if ( is_wp_error( $tmp ) ) { return 0; }
		$name = sanitize_file_name( basename( $path ) ?: 'telegram-property.jpg' );
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), $post_id ); if ( is_wp_error( $id ) ) { @unlink( $tmp ); return 0; } return (int) $id;
	}

	private static function show_requests( int $chat_id ): void {
		$posts = get_posts( array( 'post_type' => 'skt_request', 'post_status' => 'publish', 'numberposts' => 10, 'orderby' => 'date', 'order' => 'DESC' ) );
		if ( ! $posts ) { self::send( $chat_id, 'No requests found.' ); return; }
		foreach ( $posts as $post ) { self::send( $chat_id, self::request_text( $post->ID ) ); }
	}

	private static function deliver_request_queue(): void {
		$queue = get_option( self::QUEUE_OPTION, array() ); if ( ! is_array( $queue ) || ! $queue ) { return; }
		$remaining = array(); foreach ( $queue as $id ) { foreach ( self::allowed_users() as $chat_id ) { $result = self::send( $chat_id, "New website request\n\n" . self::request_text( (int) $id ) ); if ( empty( $result['ok'] ) ) { $remaining[] = (int) $id; break; } } }
		update_option( self::QUEUE_OPTION, array_values( array_unique( $remaining ) ), false );
	}

	private static function request_text( int $id ): string {
		$post = get_post( $id ); if ( ! $post ) { return 'Request not found.'; }
		$data = get_post_meta( $id, 'skt_request_data', true ); $lines = array( '#' . $id . ' — ' . $post->post_title, 'Type: ' . ucfirst( (string) get_post_meta( $id, 'skt_request_type', true ) ), 'Received: ' . get_the_date( 'Y-m-d H:i', $id ) );
		foreach ( is_array( $data ) ? $data : array() as $key => $value ) { $lines[] = ucwords( str_replace( array( '-', '_' ), ' ', $key ) ) . ': ' . ( is_array( $value ) ? implode( ', ', $value ) : $value ); }
		return mb_substr( implode( "\n", $lines ), 0, 4000 );
	}

	private static function show_properties( int $chat_id ): void {
		$posts = get_posts( array( 'post_type' => 'skt_property', 'post_status' => array( 'publish', 'draft' ), 'numberposts' => 15, 'orderby' => 'modified', 'order' => 'DESC' ) );
		if ( ! $posts ) { self::send( $chat_id, 'No properties found.' ); return; }
		foreach ( $posts as $post ) { $next = 'publish' === $post->post_status ? 'draft' : 'publish'; self::send( $chat_id, '#' . $post->ID . ' — ' . $post->post_title . "\nStatus: " . ucfirst( $post->post_status ), array( 'inline_keyboard' => array( array( array( 'text' => 'Set to ' . ucfirst( $next ), 'callback_data' => 'status:' . $post->ID . ':' . $next ) ) ) ) ); }
	}

	private static function handle_callback( int $chat_id, string $data ): void {
		if ( preg_match( '/^status:(\d+):(publish|draft)$/', $data, $m ) ) { $id = (int) $m[1]; if ( 'skt_property' !== get_post_type( $id ) ) { return; } $result = wp_update_post( array( 'ID' => $id, 'post_status' => $m[2] ), true ); self::send( $chat_id, is_wp_error( $result ) ? 'Status update failed.' : sprintf( 'Property #%d is now %s.', $id, ucfirst( $m[2] ) ) ); }
	}

	private static function api( string $method, array $body = array() ): array {
		$response = wp_remote_post( 'https://api.telegram.org/bot' . self::token() . '/' . $method, array( 'timeout' => 35, 'body' => $body ) );
		if ( is_wp_error( $response ) ) { return array( 'ok' => false, 'description' => $response->get_error_message() ); }
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true ); return is_array( $decoded ) ? $decoded : array( 'ok' => false );
	}

	private static function send( int $chat_id, string $text, ?array $markup = null ): array {
		$body = array( 'chat_id' => $chat_id, 'text' => $text ); if ( $markup ) { $body['reply_markup'] = wp_json_encode( $markup ); } return self::api( 'sendMessage', $body );
	}

	private static function token(): string { return trim( (string) getenv( 'SMARTKEY_TELEGRAM_BOT_TOKEN' ) ); }
	private static function allowed_users(): array { $raw = (string) ( getenv( 'SMARTKEY_TELEGRAM_ALLOWED_IDS' ) ?: '55906253,499185195,85074725' ); return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ); }
	private static function state( int $id ): array { $value = get_transient( self::STATE_PREFIX . $id ); return is_array( $value ) ? $value : array(); }
	private static function set_state( int $id, array $state ): void { set_transient( self::STATE_PREFIX . $id, $state, DAY_IN_SECONDS ); }
	private static function clear_state( int $id ): void { delete_transient( self::STATE_PREFIX . $id ); }
	private static function cancel_keyboard(): array { return array( 'keyboard' => array( array( array( 'text' => 'Cancel' ) ) ), 'resize_keyboard' => true ); }
}
