<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

/** Private Telegram operations bot. The long-polling worker calls run(). */
final class Telegram_Bot {
	private const STATE_PREFIX = 'skt_tg_state_';
	private const QUEUE_OPTION = 'skt_tg_request_queue';
	private const OFFSET_OPTION = 'skt_tg_update_offset';
	private const SUBSCRIBERS_OPTION = 'skt_tg_subscribers';
	private const COVER_FILE_ID_OPTION = 'skt_tg_request_cover_file_id';
	private const MAX_DELIVERY_ATTEMPTS = 5;
	private const VIDEO_MAX_BYTES = 20 * 1024 * 1024;
	private const VIDEO_MAX_SECONDS = 59;

	public static function init(): void { add_action( 'skt_request_created', array( self::class, 'queue_request' ) ); }

	public static function queue_request( int $request_id ): void {
		$subscribers = self::subscribers();
		if ( ! $subscribers ) { return; }
		$queue = get_option( self::QUEUE_OPTION, array() );
		$queue = is_array( $queue ) ? $queue : array();
		$queue[] = array( 'request_id' => $request_id, 'pending' => array_values( $subscribers ), 'attempts' => array() );
		update_option( self::QUEUE_OPTION, $queue, false );
	}

	public static function run(): void {
		if ( ! self::token() ) { throw new \RuntimeException( 'SMARTKEY_TELEGRAM_BOT_TOKEN is not configured.' ); }
		while ( true ) {
			self::deliver_request_queue();
			$result = self::api( 'getUpdates', array( 'offset' => (int) get_option( self::OFFSET_OPTION, 0 ), 'timeout' => 25, 'allowed_updates' => wp_json_encode( array( 'message', 'callback_query' ) ) ) );
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
			self::handle_callback( $chat_id, $user_id, (string) ( $callback['data'] ?? '' ) );
			return;
		}
		$text = trim( (string) ( $message['text'] ?? $message['caption'] ?? '' ) );
		if ( '/start' === $text ) { self::subscribe( $user_id, $chat_id ); self::clear_state( $user_id ); self::menu( $chat_id ); return; }
		if ( '/cancel' === $text || 'Cancel' === $text ) { self::clear_state( $user_id ); self::menu( $chat_id ); return; }
		if ( in_array( $text, array( 'Add New Property', '➕ Add New Property' ), true ) ) { self::start_property( $chat_id, $user_id ); return; }
		if ( in_array( $text, array( 'Show Draft Properties', '📝 Show Draft Properties' ), true ) ) { self::show_drafts( $chat_id ); return; }
		if ( in_array( $text, array( 'Find Property', '🔎 Find Property' ), true ) ) { self::start_find( $chat_id, $user_id ); return; }
		$state = self::state( $user_id );
		if ( empty( $state ) ) { self::menu( $chat_id ); return; }
		if ( 'find_property' === ( $state['mode'] ?? '' ) ) { self::find_property( $chat_id, $user_id, $text, (int) ( $message['message_id'] ?? 0 ) ); return; }
		if ( 'property' !== ( $state['mode'] ?? '' ) ) { return; }
		if ( 'media' === ( $state['step'] ?? '' ) ) { self::handle_media( $chat_id, $user_id, $message, $text, $state ); return; }
		if ( 'skt_property_latitude' === ( $state['step'] ?? '' ) && ! empty( $message['location'] ) ) { self::accept_location( $chat_id, $user_id, $message, $state ); return; }
		self::accept_text_answer( $chat_id, $user_id, $text, (int) ( $message['message_id'] ?? 0 ), $state );
	}

	private static function menu( int $chat_id ): void {
		self::send( $chat_id, "🏠 SmartKeyTurkey Operations\nChoose an action:", array( 'keyboard' => array( array( array( 'text' => '➕ Add New Property' ) ), array( array( 'text' => '📝 Show Draft Properties' ), array( 'text' => '🔎 Find Property' ) ) ), 'resize_keyboard' => true ) );
	}

	private static function subscribe( int $user_id, int $chat_id ): void {
		$items = get_option( self::SUBSCRIBERS_OPTION, array() ); $items = is_array( $items ) ? $items : array();
		$items[ (string) $user_id ] = $chat_id; update_option( self::SUBSCRIBERS_OPTION, $items, false );
	}

	private static function subscribers(): array {
		$items = get_option( self::SUBSCRIBERS_OPTION, array() ); $items = is_array( $items ) ? $items : array();
		return array_values( array_intersect( array_map( 'absint', $items ), self::allowed_users() ) );
	}

	private static function start_find( int $chat_id, int $user_id ): void {
		self::set_state( $user_id, array( 'mode' => 'find_property' ) );
		self::send( $chat_id, '🔎 Enter the property number. You do not need to type #.', self::cancel_keyboard() );
	}

	private static function find_property( int $chat_id, int $user_id, string $text, int $message_id ): void {
		self::delete_message( $chat_id, $message_id ); self::clear_state( $user_id );
		$id = absint( $text ); $post = $id ? get_post( $id ) : null;
		if ( ! $post || 'skt_property' !== $post->post_type ) { self::send( $chat_id, '⚠️ Property not found.' ); self::menu( $chat_id ); return; }
		$markup = 'publish' === $post->post_status ? array( 'inline_keyboard' => array( array( array( 'text' => 'Set to Draft', 'callback_data' => 'status:' . $id . ':draft' ) ) ) ) : null;
		self::send( $chat_id, sprintf( "#%d — %s\nStatus: %s", $id, $post->post_title, ucfirst( $post->post_status ) ), $markup ); self::menu( $chat_id );
	}

	private static function show_drafts( int $chat_id ): void {
		$posts = get_posts( array( 'post_type' => 'skt_property', 'post_status' => 'draft', 'numberposts' => 20, 'orderby' => 'modified', 'order' => 'DESC' ) );
		if ( ! $posts ) { self::send( $chat_id, '✅ No draft properties found.' ); return; }
		foreach ( $posts as $post ) {
			$cities = get_the_terms( $post->ID, 'skt_property_city' ); $types = get_the_terms( $post->ID, 'skt_property_type' );
			$city = $cities && ! is_wp_error( $cities ) ? implode( ', ', wp_list_pluck( $cities, 'name' ) ) : '—'; $type = $types && ! is_wp_error( $types ) ? implode( ', ', wp_list_pluck( $types, 'name' ) ) : '—';
			self::send( $chat_id, sprintf( "📝 #%d — %s\n📍 City: %s\n🏢 Type: %s", $post->ID, $post->post_title, $city, $type ), array( 'inline_keyboard' => array( array( array( 'text' => '✅ Set to Publish', 'callback_data' => 'status:' . $post->ID . ':publish' ), array( 'text' => '👁 Show Full Detail', 'callback_data' => 'property_details:' . $post->ID ) ) ) ) );
		}
		if ( 20 === count( $posts ) ) { self::send( $chat_id, 'Showing the 20 most recently updated drafts. Use Find Property for a specific ID.' ); }
	}

	private static function questions(): array {
		return array(
			'title' => array( 'label' => 'Title', 'question' => 'Enter the property title.', 'type' => 'text', 'required' => true ),
			'summary' => array( 'label' => 'Summary', 'question' => 'Enter a short public summary.', 'type' => 'text', 'required' => true ),
			'description' => array( 'label' => 'Description', 'question' => 'Enter the full property description.', 'type' => 'text', 'required' => true ),
			'city' => array( 'label' => 'City', 'question' => 'Select the city.', 'type' => 'taxonomy', 'taxonomy' => 'skt_property_city', 'required' => true ),
			'property_type' => array( 'label' => 'Property type', 'question' => 'Select the property type.', 'type' => 'taxonomy', 'taxonomy' => 'skt_property_type', 'required' => true ),
			'skt_property_reference' => array( 'label' => 'Reference', 'question' => 'Enter the listing reference.', 'type' => 'text', 'required' => true ),
			'skt_property_district' => array( 'label' => 'District', 'question' => 'Enter the public district / area.', 'type' => 'text', 'required' => true ),
			'skt_property_transaction_type' => array( 'label' => 'Transaction', 'question' => 'Select the transaction type.', 'type' => 'choice', 'choices' => array( 'sale' => 'For Sale', 'rent' => 'For Rent' ), 'required' => true ),
			'skt_property_listing_status' => array( 'label' => 'Listing status', 'question' => 'Select the listing status.', 'type' => 'choice', 'choices' => array( 'available' => 'Available', 'sold' => 'Sold', 'rented' => 'Rented' ), 'required' => true ),
			'skt_property_rooms' => array( 'label' => 'Rooms', 'question' => 'Select or enter the room configuration.', 'type' => 'choice_text', 'choices' => array( '1+0' => '1+0', '1+1' => '1+1', '2+1' => '2+1', '3+1' => '3+1', '4+1' => '4+1' ), 'required' => true ),
			'skt_property_bathrooms' => array( 'label' => 'Bathrooms', 'question' => 'Select the number of bathrooms.', 'type' => 'choice', 'choices' => array( '1' => '1', '2' => '2', '3' => '3', '4+' => '4+' ), 'required' => true ),
			'skt_property_gross_area' => array( 'label' => 'Gross area', 'question' => 'Enter gross area as a number only.', 'type' => 'area', 'required' => true ),
			'skt_property_net_area' => array( 'label' => 'Net area', 'question' => 'Enter net area as a number only.', 'type' => 'area', 'required' => true ),
			'skt_property_new_build' => array( 'label' => 'New build', 'question' => 'Is this a new build?', 'type' => 'yes_no_skip', 'required' => false ),
			'skt_property_construction_year' => array( 'label' => 'Construction year', 'question' => 'Enter the construction year.', 'type' => 'number', 'required' => false ),
			'skt_property_floor' => array( 'label' => 'Floor', 'question' => 'Enter the floor.', 'type' => 'text', 'required' => false ),
			'skt_property_parking' => array( 'label' => 'Parking', 'question' => 'Is parking available?', 'type' => 'yes_no_skip', 'required' => false ),
			'skt_property_furnished' => array( 'label' => 'Furnished', 'question' => 'Is the property furnished?', 'type' => 'yes_no_skip', 'required' => false ),
			'skt_property_amenities' => array( 'label' => 'Amenities', 'question' => 'Enter amenities, separated by commas.', 'type' => 'text', 'required' => false ),
			'skt_property_developer' => array( 'label' => 'Developer', 'question' => 'Enter the developer.', 'type' => 'text', 'required' => false ),
			'skt_property_payment_terms' => array( 'label' => 'Payment terms', 'question' => 'Enter payment terms.', 'type' => 'text', 'required' => false ),
			'skt_property_delivery_date' => array( 'label' => 'Delivery date', 'question' => 'Enter the delivery date.', 'type' => 'text', 'required' => false ),
			'skt_property_completion_status' => array( 'label' => 'Completion', 'question' => 'Select completion status.', 'type' => 'choice', 'choices' => array( 'Completed' => 'Completed', 'Under construction' => 'Under Construction', 'Planned' => 'Planned' ), 'required' => false ),
			'skt_property_title_status' => array( 'label' => 'Title status', 'question' => 'Enter title / document status.', 'type' => 'text', 'required' => false ),
			'skt_property_citizenship_review' => array( 'label' => 'Citizenship review', 'question' => 'Enter citizenship review status.', 'type' => 'text', 'required' => false ),
			'skt_property_latitude' => array( 'label' => 'Location', 'question' => 'Send a Telegram location, or enter latitude manually.', 'type' => 'decimal', 'required' => false ),
			'skt_property_longitude' => array( 'label' => 'Longitude', 'question' => 'Enter longitude.', 'type' => 'decimal', 'required' => false ),
			'skt_property_source' => array( 'label' => 'Source', 'question' => 'Enter source / property file.', 'type' => 'text', 'required' => true ),
			'skt_property_verification_status' => array( 'label' => 'Verification', 'question' => 'Enter verification status.', 'type' => 'text', 'required' => true ),
			'skt_property_last_reviewed_date' => array( 'label' => 'Last reviewed', 'question' => 'Enter last reviewed date (YYYY-MM-DD).', 'type' => 'date', 'required' => true ),
			'skt_property_control_disclosure' => array( 'label' => 'Disclosure', 'question' => 'Enter the direct-control disclosure.', 'type' => 'text', 'required' => true ),
		);
	}

	private static function start_property( int $chat_id, int $user_id ): void {
		$remove = self::send( $chat_id, '➕ Starting a new property…', array( 'remove_keyboard' => true ) );
		self::delete_message( $chat_id, (int) ( $remove['result']['message_id'] ?? 0 ) );
		$first = array_key_first( self::questions() );
		$result = self::send( $chat_id, self::form_text( array(), $first ), self::question_markup( $first ) );
		$message_id = (int) ( $result['result']['message_id'] ?? 0 );
		self::set_state( $user_id, array( 'mode' => 'property', 'step' => $first, 'data' => array(), 'media' => array(), 'prompt_message_id' => $message_id ) );
	}

	private static function accept_text_answer( int $chat_id, int $user_id, string $text, int $message_id, array $state ): void {
		$step = (string) $state['step']; $questions = self::questions(); $question = $questions[ $step ] ?? null;
		if ( ! $question ) { return; }
		if ( in_array( $question['type'], array( 'choice', 'taxonomy', 'yes_no_skip' ), true ) ) { self::send( $chat_id, 'Please use one of the buttons in the form.' ); return; }
		$value = self::validate_answer( $text, $question );
		if ( is_wp_error( $value ) ) { self::send( $chat_id, $value->get_error_message() ); return; }
		self::delete_message( $chat_id, $message_id ); self::store_answer_and_advance( $chat_id, $user_id, $state, $step, (string) $value );
	}

	private static function accept_location( int $chat_id, int $user_id, array $message, array $state ): void {
		$latitude = (string) ( $message['location']['latitude'] ?? '' ); $longitude = (string) ( $message['location']['longitude'] ?? '' );
		if ( '' === $latitude || '' === $longitude ) { self::send( $chat_id, '⚠️ The location could not be read. Please try again.' ); return; }
		self::delete_message( $chat_id, (int) ( $message['message_id'] ?? 0 ) );
		$state['data']['skt_property_latitude'] = array( 'label' => 'Latitude', 'value' => $latitude );
		$state['data']['skt_property_longitude'] = array( 'label' => 'Longitude', 'value' => $longitude );
		$state['step'] = 'skt_property_source'; self::set_state( $user_id, $state ); self::render_form( $chat_id, $state );
	}

	private static function validate_answer( string $text, array $question ) {
		if ( 'Skip' === $text && empty( $question['required'] ) ) { return ''; }
		if ( '' === $text ) { return new \WP_Error( 'empty', 'Please enter a value.' ); }
		if ( 'area' === $question['type'] ) { $normalized = str_replace( ',', '.', $text ); if ( ! is_numeric( $normalized ) ) { return new \WP_Error( 'number', '⚠️ Enter the area as a number only. The bot adds m² automatically.' ); } return ( false !== strpos( $normalized, '.' ) ? rtrim( rtrim( $normalized, '0' ), '.' ) : $normalized ) . ' m²'; }
		if ( 'number' === $question['type'] && ! ctype_digit( $text ) ) { return new \WP_Error( 'number', 'Please enter numbers only.' ); }
		if ( 'decimal' === $question['type'] && ! is_numeric( str_replace( ',', '.', $text ) ) ) { return new \WP_Error( 'number', 'Please enter a valid number.' ); }
		if ( 'date' === $question['type'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $text ) ) { return new \WP_Error( 'date', 'Use YYYY-MM-DD format.' ); }
		return sanitize_textarea_field( $text );
	}

	private static function store_answer_and_advance( int $chat_id, int $user_id, array $state, string $step, string $value ): void {
		$questions = self::questions(); $question = $questions[ $step ];
		$state['data'][ $step ] = array( 'label' => $question['label'], 'value' => $value );
		$keys = array_keys( $questions ); $position = array_search( $step, $keys, true ); $next = $keys[ $position + 1 ] ?? 'media';
		if ( 'skt_property_new_build' === $step && 'Yes' === $value ) { $next = 'skt_property_floor'; $state['data']['skt_property_construction_year'] = array( 'label' => 'Construction year', 'value' => (string) current_time( 'Y' ) ); }
		$state['step'] = $next; self::set_state( $user_id, $state ); self::render_form( $chat_id, $state );
	}

	private static function render_form( int $chat_id, array $state ): void {
		$text = self::form_text( $state['data'], $state['step'], count( $state['media'] ?? array() ) );
		if ( 'media' === $state['step'] ) { $rows = array(); if ( self::has_photo( $state['media'] ?? array() ) ) { $rows[] = array( array( 'text' => '✅ Finish Media', 'callback_data' => 'finish_media' ) ); } $rows[] = array( array( 'text' => '↩️ Cancel / Main Menu', 'callback_data' => 'cancel_form' ) ); $markup = array( 'inline_keyboard' => $rows ); } else { $markup = self::question_markup( $state['step'] ); }
		self::api( 'editMessageText', array( 'chat_id' => $chat_id, 'message_id' => (int) $state['prompt_message_id'], 'text' => $text, 'reply_markup' => wp_json_encode( $markup ) ) );
	}

	private static function form_text( array $data, string $step, int $media_count = 0 ): string {
		$lines = array( '🏠 NEW PROPERTY', '' ); foreach ( $data as $item ) { if ( '' !== $item['value'] ) { $lines[] = '• ' . $item['label'] . ': ' . ( $item['display'] ?? $item['value'] ); } }
		$lines[] = ''; if ( 'media' === $step ) { $lines[] = '📎 Photos / videos received: ' . $media_count; $lines[] = 'Send photos or videos now. The first photo is the featured image. Videos: maximum 59 seconds and 20 MB.'; } else { $lines[] = '➡️ NEXT'; $lines[] = self::questions()[ $step ]['question']; }
		return mb_substr( implode( "\n", $lines ), 0, 4090 );
	}

	private static function question_markup( string $step ): array {
		$q = self::questions()[ $step ]; $buttons = array();
		if ( 'taxonomy' === $q['type'] ) { $terms = get_terms( array( 'taxonomy' => $q['taxonomy'], 'hide_empty' => false, 'orderby' => 'name' ) ); foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) { $buttons[] = array( 'text' => $term->name, 'callback_data' => 'answer:' . $step . ':term_' . $term->term_id ); } }
		if ( in_array( $q['type'], array( 'choice', 'choice_text' ), true ) ) { foreach ( $q['choices'] as $value => $label ) { $buttons[] = array( 'text' => $label, 'callback_data' => 'answer:' . $step . ':' . rawurlencode( $value ) ); } }
		if ( 'yes_no_skip' === $q['type'] ) { foreach ( array( 'Yes', 'No', 'Skip' ) as $value ) { $buttons[] = array( 'text' => strtoupper( $value ), 'callback_data' => 'answer:' . $step . ':' . $value ); } }
		$rows = array_chunk( $buttons, 2 ); if ( empty( $q['required'] ) && 'yes_no_skip' !== $q['type'] ) { $rows[] = array( array( 'text' => '⏭ SKIP', 'callback_data' => 'answer:' . $step . ':Skip' ) ); } $rows[] = array( array( 'text' => '↩️ Cancel / Main Menu', 'callback_data' => 'cancel_form' ) );
		return array( 'inline_keyboard' => $rows );
	}

	private static function handle_media( int $chat_id, int $user_id, array $message, string $text, array $state ): void {
		$media = null; $photos = $message['photo'] ?? array();
		if ( $photos ) { $largest = end( $photos ); $media = array( 'type' => 'photo', 'file_id' => (string) $largest['file_id'], 'size' => (int) ( $largest['file_size'] ?? 0 ) ); }
		if ( ! empty( $message['video'] ) ) {
			$video = $message['video']; $duration = (int) ( $video['duration'] ?? 0 ); $size = (int) ( $video['file_size'] ?? 0 );
			if ( $duration > self::VIDEO_MAX_SECONDS ) { self::send_reply( $chat_id, (int) ( $message['message_id'] ?? 0 ), '⚠️ Video rejected: duration must not exceed 59 seconds.' ); return; }
			if ( $size > self::VIDEO_MAX_BYTES ) { self::send_reply( $chat_id, (int) ( $message['message_id'] ?? 0 ), '⚠️ Video rejected: Telegram Cloud Bot API allows bots to download files only up to 20 MB.' ); return; }
			$media = array( 'type' => 'video', 'file_id' => (string) $video['file_id'], 'size' => $size, 'duration' => $duration );
		}
		if ( ! $media ) { self::send( $chat_id, '📎 Send a photo or video.' ); return; }
		$state['media'][] = $media; self::set_state( $user_id, $state ); self::send_reply( $chat_id, (int) ( $message['message_id'] ?? 0 ), '✅ Received' ); self::render_form( $chat_id, $state );
	}

	private static function create_property( int $chat_id, int $user_id, array $state ): void {
		$flat = array(); foreach ( $state['data'] as $key => $item ) { $flat[ $key ] = $item['value']; }
		$post_id = wp_insert_post( array( 'post_type' => 'skt_property', 'post_status' => 'draft', 'post_title' => $flat['title'], 'post_excerpt' => $flat['summary'], 'post_content' => wpautop( esc_html( $flat['description'] ) ) ), true );
		if ( is_wp_error( $post_id ) ) { self::send( $chat_id, 'The property could not be created.' ); return; }
		wp_set_object_terms( $post_id, absint( $flat['city'] ), 'skt_property_city' ); wp_set_object_terms( $post_id, absint( $flat['property_type'] ), 'skt_property_type' );
		foreach ( $flat as $key => $value ) { if ( str_starts_with( $key, 'skt_property_' ) ) { update_post_meta( $post_id, $key, $value ); } }
		$year = (int) ( $flat['skt_property_construction_year'] ?? 0 ); update_post_meta( $post_id, 'skt_property_new_build', 'Yes' === ( $flat['skt_property_new_build'] ?? '' ) ? '1' : '0' ); update_post_meta( $post_id, 'skt_property_building_age', 'Yes' === ( $flat['skt_property_new_build'] ?? '' ) ? 'New' : ( $year ? (string) max( 0, (int) current_time( 'Y' ) - $year ) : '' ) );
		$photos = array(); $videos = array(); foreach ( $state['media'] as $item ) { $id = self::import_media( $item, (int) $post_id ); if ( $id ) { 'photo' === $item['type'] ? $photos[] = $id : $videos[] = $id; } }
		if ( $photos ) { set_post_thumbnail( $post_id, $photos[0] ); update_post_meta( $post_id, 'skt_property_gallery_ids', implode( ',', $photos ) ); } if ( $videos ) { update_post_meta( $post_id, 'skt_property_video_ids', implode( ',', $videos ) ); }
		self::clear_state( $user_id ); self::send( $chat_id, sprintf( '🎉 Property #%d was created successfully as Draft with %d photo(s) and %d video(s).', $post_id, count( $photos ), count( $videos ) ) ); self::menu( $chat_id );
	}

	private static function import_media( array $item, int $post_id ): int {
		$file = self::api( 'getFile', array( 'file_id' => $item['file_id'] ) ); $path = (string) ( $file['result']['file_path'] ?? '' ); if ( ! $path ) { return 0; }
		require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = download_url( 'https://api.telegram.org/file/bot' . self::token() . '/' . ltrim( $path, '/' ), 90 ); if ( is_wp_error( $tmp ) ) { return 0; }
		$id = media_handle_sideload( array( 'name' => sanitize_file_name( basename( $path ) ), 'tmp_name' => $tmp ), $post_id ); if ( is_wp_error( $id ) ) { @unlink( $tmp ); return 0; } return (int) $id;
	}

	private static function handle_callback( int $chat_id, int $user_id, string $data ): void {
		if ( 'cancel_form' === $data ) { self::clear_state( $user_id ); self::menu( $chat_id ); return; }
		if ( 'finish_media' === $data ) { $state = self::state( $user_id ); if ( ! self::has_photo( $state['media'] ?? array() ) ) { self::send( $chat_id, '⚠️ Please send the featured photo first.' ); return; } self::create_property( $chat_id, $user_id, $state ); return; }
		if ( preg_match( '/^answer:([^:]+):(.+)$/', $data, $m ) ) {
			$state = self::state( $user_id ); $step = sanitize_key( $m[1] ); if ( $step !== ( $state['step'] ?? '' ) ) { return; } $raw = rawurldecode( $m[2] ); $value = $raw;
			if ( str_starts_with( $raw, 'term_' ) ) { $term = get_term( absint( substr( $raw, 5 ) ) ); if ( ! $term || is_wp_error( $term ) ) { return; } $value = (string) $term->term_id; $state['data'][ $step ] = array( 'label' => self::questions()[ $step ]['label'], 'value' => $value, 'display' => $term->name ); $display_state = $state; $display_state['data'][ $step ]['value'] = $term->name; $keys = array_keys( self::questions() ); $position = array_search( $step, $keys, true ); $state['step'] = $keys[ $position + 1 ] ?? 'media'; self::set_state( $user_id, $state ); $display_state['step'] = $state['step']; self::render_form( $chat_id, array_merge( $state, array( 'data' => array_map( static fn( $item ) => isset( $item['display'] ) ? array( 'label' => $item['label'], 'value' => $item['display'] ) : $item, $state['data'] ) ) ) ); return; }
			self::store_answer_and_advance( $chat_id, $user_id, $state, $step, $value ); return;
		}
		if ( preg_match( '/^request:(\d+)$/', $data, $m ) ) { self::send( $chat_id, self::request_full_text( (int) $m[1] ) ); return; }
		if ( preg_match( '/^property_details:(\d+)$/', $data, $m ) ) { self::show_property_details( $chat_id, (int) $m[1] ); return; }
		if ( preg_match( '/^status:(\d+):(publish|draft)$/', $data, $m ) ) { $id = (int) $m[1]; if ( 'skt_property' !== get_post_type( $id ) ) { return; } $result = wp_update_post( array( 'ID' => $id, 'post_status' => $m[2] ), true ); self::send( $chat_id, is_wp_error( $result ) ? 'Status update failed.' : sprintf( 'Property #%d is now %s.', $id, ucfirst( $m[2] ) ) ); }
	}

	private static function show_property_details( int $chat_id, int $id ): void {
		$post = get_post( $id ); if ( ! $post || 'skt_property' !== $post->post_type ) { self::send( $chat_id, '⚠️ Property not found.' ); return; }
		$cities = get_the_terms( $id, 'skt_property_city' ); $types = get_the_terms( $id, 'skt_property_type' );
		$gallery = array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $id, 'skt_property_gallery_ids', true ) ) ) ) );
		$videos = array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $id, 'skt_property_video_ids', true ) ) ) ) );
		$caption = sprintf( "🏠 #%d — %s\n📍 %s · 🏢 %s\n📷 %d photo(s) · 🎬 %d video(s)", $id, $post->post_title, $cities && ! is_wp_error( $cities ) ? implode( ', ', wp_list_pluck( $cities, 'name' ) ) : '—', $types && ! is_wp_error( $types ) ? implode( ', ', wp_list_pluck( $types, 'name' ) ) : '—', count( $gallery ), count( $videos ) );
		$thumbnail = get_the_post_thumbnail_url( $id, 'large' ); if ( $thumbnail ) { self::api( 'sendPhoto', array( 'chat_id' => $chat_id, 'photo' => $thumbnail, 'caption' => $caption ) ); } else { self::send( $chat_id, $caption . "\n⚠️ No featured image." ); }
		$lines = array( '📋 FULL PROPERTY DETAILS', 'Status: ' . ucfirst( $post->post_status ), 'Summary: ' . $post->post_excerpt, 'Description: ' . wp_strip_all_tags( $post->post_content ) );
		foreach ( self::questions() as $key => $question ) { if ( ! str_starts_with( $key, 'skt_property_' ) ) { continue; } $value = (string) get_post_meta( $id, $key, true ); if ( '' !== $value ) { $lines[] = $question['label'] . ': ' . $value; } }
		self::send( $chat_id, mb_substr( implode( "\n", $lines ), 0, 4090 ) );
	}

	private static function deliver_request_queue(): void {
		$queue = get_option( self::QUEUE_OPTION, array() ); if ( ! is_array( $queue ) || ! $queue ) { return; } $remaining = array();
		foreach ( $queue as $entry ) {
			if ( is_numeric( $entry ) ) { continue; } $id = absint( $entry['request_id'] ?? 0 ); $pending = array(); $attempts = is_array( $entry['attempts'] ?? null ) ? $entry['attempts'] : array();
			foreach ( (array) ( $entry['pending'] ?? array() ) as $chat_id ) {
				$chat_id = (int) $chat_id; $result = self::send_request_card( $chat_id, $id );
				if ( empty( $result['ok'] ) ) {
					$count = absint( $attempts[ (string) $chat_id ] ?? 0 ) + 1;
					if ( $count < self::MAX_DELIVERY_ATTEMPTS ) { $pending[] = $chat_id; $attempts[ (string) $chat_id ] = $count; }
					else { error_log( sprintf( '[SmartKey Telegram] Dropped request #%d notification for chat %d after %d failed attempts.', $id, $chat_id, $count ) ); }
				}
			}
			if ( $pending ) { $remaining[] = array( 'request_id' => $id, 'pending' => $pending, 'attempts' => $attempts ); }
		}
		update_option( self::QUEUE_OPTION, $remaining, false );
	}

	private static function send_request_card( int $chat_id, int $id ): array {
		$post = get_post( $id ); if ( ! $post ) { return array( 'ok' => true ); } $data = self::request_data( $id );
		$caption = sprintf( "🔔 New request for %s\n🆔 Request #%d\n✉️ Email: %s\n📅 Received: %s", self::request_subject( $id ), $id, $data['email'], get_the_date( 'Y-m-d H:i', $id ) );
		$markup = array( 'inline_keyboard' => array( array( array( 'text' => 'Show Full Request', 'callback_data' => 'request:' . $id ) ) ) );
		$cover = (string) get_option( self::COVER_FILE_ID_OPTION, '' ); if ( ! $cover ) { $cover = plugins_url( 'assets/images/request-cover.jpg', SKT_CORE_FILE ); }
		$result = self::api( 'sendPhoto', array( 'chat_id' => $chat_id, 'photo' => $cover, 'caption' => $caption, 'reply_markup' => wp_json_encode( $markup ) ) );
		if ( ! empty( $result['ok'] ) ) {
			$photos = $result['result']['photo'] ?? array(); if ( $photos ) { $largest = end( $photos ); if ( ! empty( $largest['file_id'] ) ) { update_option( self::COVER_FILE_ID_OPTION, sanitize_text_field( (string) $largest['file_id'] ), false ); } }
			return $result;
		}
		error_log( sprintf( '[SmartKey Telegram] Request #%d photo card failed for chat %d; attempting text fallback.', $id, $chat_id ) );
		return self::send( $chat_id, $caption . "\n\n⚠️ Cover image unavailable for this notification.", $markup );
	}

	private static function request_data( int $id ): array {
		$data = get_post_meta( $id, 'skt_request_data', true ); $data = is_array( $data ) ? $data : array();
		$email = (string) ( $data['email'] ?? $data['business-email'] ?? $data['your-email'] ?? $data['Email'] ?? '' ); return array( 'raw' => $data, 'email' => $email ?: 'Not provided' );
	}

	private static function request_subject( int $id ): string {
		$related = absint( get_post_meta( $id, 'skt_request_related_id', true ) ); if ( $related ) { return get_the_title( $related ); }
		return 'General ' . ucfirst( (string) get_post_meta( $id, 'skt_request_type', true ) ) . ' inquiry';
	}

	private static function request_full_text( int $id ): string {
		$post = get_post( $id ); if ( ! $post ) { return 'Request not found.'; } $item = self::request_data( $id );
		$lines = array( 'REQUEST #' . $id, 'Subject: ' . self::request_subject( $id ), 'Type: ' . ucfirst( (string) get_post_meta( $id, 'skt_request_type', true ) ), 'Received: ' . get_the_date( 'Y-m-d H:i', $id ) );
		foreach ( $item['raw'] as $key => $value ) { $lines[] = ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) ) . ': ' . ( is_array( $value ) ? implode( ', ', $value ) : $value ); }
		$related = absint( get_post_meta( $id, 'skt_request_related_id', true ) ); if ( $related && get_permalink( $related ) ) { $lines[] = 'Page: ' . get_permalink( $related ); }
		return mb_substr( implode( "\n", $lines ), 0, 4090 );
	}

	private static function api( string $method, array $body = array() ): array {
		$response = wp_remote_post( 'https://api.telegram.org/bot' . self::token() . '/' . $method, array( 'timeout' => 35, 'body' => $body ) );
		if ( is_wp_error( $response ) ) { error_log( sprintf( '[SmartKey Telegram] %s transport error: %s', sanitize_key( $method ), $response->get_error_message() ) ); return array( 'ok' => false, 'description' => $response->get_error_message() ); }
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true ); $decoded = is_array( $decoded ) ? $decoded : array( 'ok' => false, 'description' => 'Invalid JSON response' );
		if ( empty( $decoded['ok'] ) ) { error_log( sprintf( '[SmartKey Telegram] %s failed: HTTP %d, error_code %d, description: %s', sanitize_key( $method ), wp_remote_retrieve_response_code( $response ), absint( $decoded['error_code'] ?? 0 ), sanitize_text_field( (string) ( $decoded['description'] ?? 'Unknown error' ) ) ) ); }
		return $decoded;
	}
	private static function send( int $chat_id, string $text, ?array $markup = null ): array { $body = array( 'chat_id' => $chat_id, 'text' => $text ); if ( $markup ) { $body['reply_markup'] = wp_json_encode( $markup ); } return self::api( 'sendMessage', $body ); }
	private static function send_reply( int $chat_id, int $message_id, string $text ): array { return self::api( 'sendMessage', array( 'chat_id' => $chat_id, 'text' => $text, 'reply_parameters' => wp_json_encode( array( 'message_id' => $message_id ) ) ) ); }
	private static function has_photo( array $media ): bool { foreach ( $media as $item ) { if ( 'photo' === ( $item['type'] ?? '' ) ) { return true; } } return false; }
	private static function delete_message( int $chat_id, int $message_id ): void { if ( $message_id ) { self::api( 'deleteMessage', array( 'chat_id' => $chat_id, 'message_id' => $message_id ) ); } }
	private static function token(): string { return trim( (string) getenv( 'SMARTKEY_TELEGRAM_BOT_TOKEN' ) ); }
	private static function allowed_users(): array { return array_values( array_filter( array_map( 'absint', explode( ',', (string) ( getenv( 'SMARTKEY_TELEGRAM_ALLOWED_IDS' ) ?: '55906253,499185195,85074725' ) ) ) ) ); }
	private static function state( int $id ): array { $value = get_transient( self::STATE_PREFIX . $id ); return is_array( $value ) ? $value : array(); }
	private static function set_state( int $id, array $state ): void { set_transient( self::STATE_PREFIX . $id, $state, DAY_IN_SECONDS ); }
	private static function clear_state( int $id ): void { delete_transient( self::STATE_PREFIX . $id ); }
	private static function cancel_keyboard(): array { return array( 'keyboard' => array( array( array( 'text' => 'Cancel' ) ) ), 'resize_keyboard' => true ); }
}
