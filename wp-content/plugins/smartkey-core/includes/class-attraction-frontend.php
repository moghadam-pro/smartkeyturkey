<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Attraction_Frontend {
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ) );
		add_filter( 'template_include', array( self::class, 'template' ), 105 );
		add_filter( 'rank_math/frontend/title', array( self::class, 'seo_title' ) );
		add_filter( 'rank_math/frontend/description', array( self::class, 'seo_description' ) );
	}

	public static function seo_title( string $title ): string { return is_post_type_archive( 'skt_attraction' ) ? 'Turkey Attractions & City Guides | SmartKeyTurkey' : $title; }
	public static function seo_description( string $description ): string { return is_post_type_archive( 'skt_attraction' ) ? 'Explore practical English guides to attractions in Istanbul, Ankara, Izmir and Antalya, with official sources and dated visitor notes.' : $description; }

	public static function assets(): void {
		if ( is_post_type_archive( 'skt_attraction' ) || is_tax( 'skt_attraction_city' ) || is_singular( 'skt_attraction' ) ) { wp_enqueue_style( 'smartkey-attractions', plugins_url( 'assets/css/attraction-templates.css', SKT_CORE_FILE ), array( 'smartkey-design-tokens' ), SKT_CORE_VERSION ); }
	}

	public static function template( string $template ): string {
		if ( is_singular( 'skt_attraction' ) ) { return SKT_CORE_DIR . 'templates/single-skt_attraction.php'; }
		if ( is_post_type_archive( 'skt_attraction' ) || is_tax( 'skt_attraction_city' ) ) { return SKT_CORE_DIR . 'templates/archive-skt_attraction.php'; }
		return $template;
	}

	public static function archive(): string {
		global $wp_query;
		$cities = get_terms( array( 'taxonomy' => 'skt_attraction_city', 'hide_empty' => true ) );
		ob_start(); ?>
		<main class="skt-attractions" id="main-content"><section class="skt-attraction-hero"><div class="skt-attraction-shell"><nav class="skt-attraction-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><span>Turkey Attractions</span></nav><p class="skt-attraction-eyebrow">Explore Türkiye</p><h1><?php echo is_tax() ? esc_html( single_term_title( '', false ) . ' attractions' ) : 'Places that add context to every journey.'; ?></h1><p>English-first city guides built from official destination sources, with practical planning notes and clearly dated reviews.</p></div></section>
		<div class="skt-attraction-shell skt-attraction-layout"><aside class="skt-attraction-filter"><strong>Cities</strong><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_attraction' ) ); ?>"<?php echo is_tax() ? '' : ' aria-current="page"'; ?>>All cities</a><?php if ( ! is_wp_error( $cities ) ) : foreach ( $cities as $city ) : ?><a href="<?php echo esc_url( get_term_link( $city ) ); ?>"<?php echo is_tax() && get_queried_object_id() === $city->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $city->name ); ?></a><?php endforeach; endif; ?></aside><section class="skt-attraction-results" aria-label="Attraction results"><div class="skt-attraction-heading"><div><p class="skt-attraction-eyebrow">Curated starting points</p><h2>Attractions and historic places</h2></div><p><?php echo esc_html( number_format_i18n( (int) $wp_query->found_posts ) ); ?> guides</p></div><div class="skt-attraction-grid"><?php while ( have_posts() ) : the_post(); $city = get_the_terms( get_the_ID(), 'skt_attraction_city' ); ?><article><a class="skt-attraction-card-media" href="<?php the_permalink(); ?>"><span><?php echo esc_html( $city && ! is_wp_error( $city ) ? $city[0]->name : 'Türkiye' ); ?></span><b aria-hidden="true">SK</b></a><div><p><?php echo esc_html( (string) get_post_meta( get_the_ID(), 'skt_attraction_type', true ) ); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( get_the_excerpt() ); ?></p><a href="<?php the_permalink(); ?>">Plan your visit →</a></div></article><?php endwhile; ?></div><?php if ( $wp_query->max_num_pages > 1 ) : ?><nav class="skt-attraction-pagination" aria-label="Attraction pages"><?php echo wp_kses_post( paginate_links( array( 'type' => 'list' ) ) ); ?></nav><?php endif; ?></section></div></main>
		<?php return (string) ob_get_clean();
	}

	public static function single(): string {
		$post_id = get_queried_object_id(); $city = get_the_terms( $post_id, 'skt_attraction_city' ); $source = (string) get_post_meta( $post_id, 'skt_attraction_source_url', true ); $lat = (string) get_post_meta( $post_id, 'skt_attraction_latitude', true ); $lng = (string) get_post_meta( $post_id, 'skt_attraction_longitude', true );
		ob_start(); ?>
		<main class="skt-attraction-single" id="main-content"><section class="skt-attraction-hero"><div class="skt-attraction-shell"><nav class="skt-attraction-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_attraction' ) ); ?>">Attractions</a><span>/</span><span><?php the_title(); ?></span></nav><p class="skt-attraction-eyebrow"><?php echo esc_html( $city && ! is_wp_error( $city ) ? $city[0]->name : 'Türkiye' ); ?></p><h1><?php the_title(); ?></h1><p><?php echo esc_html( get_the_excerpt() ); ?></p></div></section><div class="skt-attraction-shell skt-attraction-detail"><article><?php the_content(); ?><div class="skt-attraction-source"><strong>Source and freshness</strong><p>Editorial summary based on an official GoTürkiye destination source. Visitor arrangements can change; confirm current details before travelling.</p><?php if ( $source ) : ?><a href="<?php echo esc_url( $source ); ?>" target="_blank" rel="noopener noreferrer">Review official source ↗</a><?php endif; ?></div></article><aside><h2>Visitor snapshot</h2><dl><?php foreach ( array( 'district' => 'Area', 'type' => 'Type', 'duration' => 'Suggested duration', 'best_for' => 'Best for', 'reviewed' => 'Last reviewed' ) as $key => $label ) : $value = get_post_meta( $post_id, 'skt_attraction_' . $key, true ); if ( $value ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( (string) $value ); ?></dd></div><?php endif; endforeach; ?></dl><?php if ( $lat && $lng ) : ?><a class="skt-attraction-map" href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $lat . ',' . $lng ) ); ?>" target="_blank" rel="noopener noreferrer">Open location in Google Maps ↗</a><?php endif; ?></aside></div></main>
		<?php return (string) ob_get_clean();
	}
}
