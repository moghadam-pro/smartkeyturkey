<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Attraction_Catalog {
	private const SEED_VERSION = '1.0.0';
	private const FIELDS = array( 'skt_attraction_district', 'skt_attraction_type', 'skt_attraction_duration', 'skt_attraction_best_for', 'skt_attraction_source_url', 'skt_attraction_latitude', 'skt_attraction_longitude', 'skt_attraction_reviewed' );

	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 11 );
		add_action( 'init', array( self::class, 'seed' ), 49 );
		add_action( 'add_meta_boxes_skt_attraction', array( self::class, 'meta_box' ) );
		add_action( 'save_post_skt_attraction', array( self::class, 'save' ) );
	}

	public static function register(): void {
		register_post_type( 'skt_attraction', array(
			'labels' => array( 'name' => 'Attractions', 'singular_name' => 'Attraction', 'add_new_item' => 'Add Attraction', 'edit_item' => 'Edit Attraction' ),
			'public' => true, 'show_in_rest' => true, 'show_in_menu' => false, 'has_archive' => 'turkey-attractions', 'rewrite' => array( 'slug' => 'turkey-attractions' ),
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ), 'menu_icon' => 'dashicons-location-alt',
		) );
		register_taxonomy( 'skt_attraction_city', 'skt_attraction', array(
			'labels' => array( 'name' => 'Attraction Cities', 'singular_name' => 'Attraction City' ), 'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
			'rewrite' => array( 'slug' => 'turkey-attractions/city' ),
		) );
	}

	public static function meta_box(): void {
		add_meta_box( 'skt_attraction_details', 'Visitor and source details', array( self::class, 'render_meta_box' ), 'skt_attraction', 'normal', 'high' );
	}

	public static function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'skt_attraction_save', 'skt_attraction_nonce' );
		$labels = array( 'district' => 'District / area', 'type' => 'Attraction type', 'duration' => 'Suggested duration', 'best_for' => 'Best for', 'source_url' => 'Official source URL', 'latitude' => 'Latitude', 'longitude' => 'Longitude', 'reviewed' => 'Last reviewed (YYYY-MM-DD)' );
		echo '<table class="form-table"><tbody>';
		foreach ( $labels as $key => $label ) { $name = 'skt_attraction_' . $key; echo '<tr><th><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) get_post_meta( $post->ID, $name, true ) ) . '"></td></tr>'; }
		echo '</tbody></table>';
	}

	public static function save( int $post_id ): void {
		if ( ! isset( $_POST['skt_attraction_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['skt_attraction_nonce'] ) ), 'skt_attraction_save' ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
		foreach ( self::FIELDS as $field ) { if ( isset( $_POST[ $field ] ) ) { $value = 'skt_attraction_source_url' === $field ? esc_url_raw( wp_unslash( $_POST[ $field ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $field ] ) ); update_post_meta( $post_id, $field, $value ); } }
	}

	public static function seed(): void {
		if ( self::SEED_VERSION === get_option( 'skt_attraction_seed_version' ) ) { return; }
		$records = array(
			array( 'title' => 'Hagia Sophia', 'city' => 'Istanbul', 'district' => 'Sultanahmet, Fatih', 'type' => 'Architecture & heritage', 'duration' => '60–90 minutes', 'best_for' => 'History, architecture and first-time Istanbul visits', 'source' => 'https://goturkiye.com/istanbul/hagia-sophia', 'lat' => '41.008583', 'lng' => '28.980175', 'excerpt' => 'A defining Istanbul landmark where Eastern Roman architecture and Ottoman additions meet.', 'content' => '<p>Hagia Sophia is one of Istanbul’s most recognizable landmarks and a central stop in the Sultanahmet historic area. Its vast dome, layered architectural history, mosaics, calligraphy and later minarets reflect major chapters in the city’s past.</p><h2>Plan your visit</h2><p>Allow time for security and respect the requirements of an active place of worship. Opening arrangements and visitor access can change, so confirm current information with the official destination before travelling.</p>' ),
			array( 'title' => 'Anıtkabir', 'city' => 'Ankara', 'district' => 'Çankaya', 'type' => 'Memorial & museum', 'duration' => '90–120 minutes', 'best_for' => 'Modern history, architecture and civic culture', 'source' => 'https://goturkiye.com/ankara/see', 'lat' => '39.925054', 'lng' => '32.836943', 'excerpt' => 'The mausoleum of Mustafa Kemal Atatürk and a major landmark of modern Ankara.', 'content' => '<p>Anıtkabir is the mausoleum of Mustafa Kemal Atatürk, founder of the Republic of Türkiye. The monumental complex on Rasattepe includes the ceremonial Lion Road and the Atatürk and War of Independence Museum.</p><h2>Plan your visit</h2><p>The complex is extensive and includes indoor museum areas as well as broad outdoor ceremonial spaces. Check current visiting information and ceremonies before arrival.</p>' ),
			array( 'title' => 'Ephesus Ancient City', 'city' => 'Izmir', 'district' => 'Selçuk', 'type' => 'Archaeological site', 'duration' => '2–3 hours', 'best_for' => 'Archaeology, ancient cities and cultural routes', 'source' => 'https://goturkiye.com/izmir/routes', 'lat' => '37.939010', 'lng' => '27.341010', 'excerpt' => 'A major archaeological site near Selçuk and a cornerstone of İzmir’s history routes.', 'content' => '<p>Ephesus is a major archaeological destination near Selçuk in İzmir Province. Its monumental streets and surviving civic, religious and residential structures make it one of the region’s essential cultural stops.</p><h2>Plan your visit</h2><p>The site involves substantial outdoor walking over historic surfaces. Comfortable shoes, water and seasonal sun protection are useful. Verify current admission and opening information before setting out.</p>' ),
			array( 'title' => 'Kaleiçi Old Town', 'city' => 'Antalya', 'district' => 'Muratpaşa', 'type' => 'Historic district', 'duration' => '2–4 hours', 'best_for' => 'Architecture, walking, cafés and coastal views', 'source' => 'https://goturkiye.com/antalya/see', 'lat' => '36.884140', 'lng' => '30.705630', 'excerpt' => 'Antalya’s walled historic center, linking traditional streets, Hadrian’s Gate and the old marina.', 'content' => '<p>Kaleiçi is Antalya’s historic center, with narrow streets, traditional houses, museums, cafés and access to the old marina. The area reflects layers of Hellenistic, Roman, Byzantine, Seljuk and Ottoman history.</p><h2>Plan your visit</h2><p>Explore on foot and allow extra time for Hadrian’s Gate, the marina and nearby viewpoints. Access and venue hours vary across the district, so check individual sites where necessary.</p>' ),
		);
		foreach ( $records as $record ) {
			$existing = get_page_by_path( sanitize_title( $record['title'] ), OBJECT, 'skt_attraction' );
			$post_id = $existing ? (int) $existing->ID : (int) wp_insert_post( array( 'post_type' => 'skt_attraction', 'post_status' => 'publish', 'post_title' => $record['title'], 'post_excerpt' => $record['excerpt'], 'post_content' => $record['content'] ) );
			if ( ! $post_id ) { continue; }
			wp_set_object_terms( $post_id, $record['city'], 'skt_attraction_city' );
			foreach ( array( 'district', 'type', 'duration', 'best_for' ) as $key ) { update_post_meta( $post_id, 'skt_attraction_' . $key, $record[ $key ] ); }
			update_post_meta( $post_id, 'skt_attraction_source_url', $record['source'] ); update_post_meta( $post_id, 'skt_attraction_latitude', $record['lat'] ); update_post_meta( $post_id, 'skt_attraction_longitude', $record['lng'] ); update_post_meta( $post_id, 'skt_attraction_reviewed', '2026-07-21' );
			update_post_meta( $post_id, 'rank_math_title', '%title% | Turkey Attractions | SmartKeyTurkey' ); update_post_meta( $post_id, 'rank_math_description', $record['excerpt'] . ' Read practical planning notes and verify current visitor information.' );
		}
		update_option( 'skt_attraction_seed_version', self::SEED_VERSION, false ); flush_rewrite_rules( false );
	}

}
