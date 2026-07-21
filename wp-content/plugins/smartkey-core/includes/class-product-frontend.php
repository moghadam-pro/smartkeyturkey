<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Product_Frontend {
	private const ARCHIVE_TEMPLATE_TITLE = 'SmartKey — Petrochemical Product Archive';
	private const SINGLE_TEMPLATE_TITLE  = 'SmartKey — Petrochemical Single Product';
	private const TEMPLATE_VERSION       = '1.0.0';

	public static function init(): void {
		add_shortcode( 'skt_product_archive', array( self::class, 'render_archive' ) );
		add_shortcode( 'skt_product_single', array( self::class, 'render_single' ) );
		add_filter( 'template_include', array( self::class, 'route_templates' ), 99 );
		add_filter( 'wpcf7_form_tag', array( self::class, 'prefill_product_field' ) );
		add_filter( 'wpcf7_contact_form_properties', array( self::class, 'enable_product_shortcode_default' ), 10, 2 );
		add_filter( 'shortcode_atts_wpcf7', array( self::class, 'register_product_shortcode_attribute' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'init', array( self::class, 'maybe_seed_elementor_templates' ), 30 );
	}

	public static function enqueue_assets(): void {
		if ( is_singular( 'skt_product' ) || is_post_type_archive( 'skt_product' ) || is_tax( 'skt_product_family' ) ) {
			wp_enqueue_style(
				'smartkey-product-templates',
				plugins_url( 'assets/css/product-templates.css', SKT_CORE_FILE ),
				array( 'smartkey-design-tokens' ),
				SKT_CORE_VERSION
			);
		}
	}

	public static function route_templates( string $template ): string {
		if ( is_singular( 'skt_product' ) ) {
			return SKT_CORE_DIR . 'templates/single-skt_product.php';
		}

		if ( is_post_type_archive( 'skt_product' ) || is_tax( 'skt_product_family' ) ) {
			return SKT_CORE_DIR . 'templates/archive-skt_product.php';
		}

		return $template;
	}

	public static function render_elementor_template( string $title, string $fallback_shortcode ): void {
		$template_id = self::find_template_id( $title );
		if ( $template_id && class_exists( '\Elementor\Plugin' ) ) {
			$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true );
			if ( '' !== trim( (string) $content ) ) {
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor renders escaped widget output.
				return;
			}
		}

		echo do_shortcode( $fallback_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode methods escape their HTML.
	}

	public static function render_archive(): string {
		global $wp_query;

		$is_family = is_tax( 'skt_product_family' );
		$title     = $is_family ? single_term_title( '', false ) . ' Petrochemical Products' : 'Petrochemical Products & Polymer Grades';
		$intro     = $is_family
			? sprintf( 'Explore %s grades, applications and source-captured technical properties. Current specifications, availability and commercial terms are confirmed per inquiry.', single_term_title( '', false ) )
			: 'Explore petrochemical products, polymers and industrial grades available through SmartKeyTurkey. Compare source-captured specifications and request current commercial terms.';
		$families  = get_terms( array( 'taxonomy' => 'skt_product_family', 'hide_empty' => true ) );

		ob_start();
		?>
		<main class="skt-product-archive" id="main-content">
			<section class="skt-catalog-hero">
				<div class="skt-shell">
					<nav class="skt-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smartkey-core' ); ?></a><span aria-hidden="true">/</span><span><?php esc_html_e( 'Petrochemical Products', 'smartkey-core' ); ?></span></nav>
					<p class="skt-eyebrow"><?php esc_html_e( 'B2B sourcing catalogue', 'smartkey-core' ); ?></p>
					<h1><?php echo esc_html( $title ); ?></h1>
					<p class="skt-hero-copy"><?php echo esc_html( $intro ); ?></p>
					<a class="skt-button" href="#product-results"><?php esc_html_e( 'Explore products', 'smartkey-core' ); ?></a>
				</div>
			</section>

			<?php if ( ! is_wp_error( $families ) && $families ) : ?>
				<nav class="skt-family-nav skt-shell" aria-label="<?php esc_attr_e( 'Product families', 'smartkey-core' ); ?>">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"<?php echo $is_family ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'All products', 'smartkey-core' ); ?></a>
					<?php foreach ( $families as $family ) : ?>
						<a href="<?php echo esc_url( get_term_link( $family ) ); ?>"<?php echo $is_family && get_queried_object_id() === $family->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $family->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<section class="skt-shell skt-results" id="product-results" aria-labelledby="product-results-title">
				<div class="skt-section-heading"><div><p class="skt-eyebrow"><?php esc_html_e( 'Available catalogue', 'smartkey-core' ); ?></p><h2 id="product-results-title"><?php esc_html_e( 'Products and grades', 'smartkey-core' ); ?></h2></div><p><?php echo esc_html( sprintf( _n( '%s result', '%s results', (int) $wp_query->found_posts, 'smartkey-core' ), number_format_i18n( (int) $wp_query->found_posts ) ) ); ?></p></div>
				<?php if ( have_posts() ) : ?>
					<div class="skt-product-grid">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php $terms = get_the_terms( get_the_ID(), 'skt_product_family' ); ?>
							<article <?php post_class( 'skt-product-card' ); ?>>
								<a class="skt-card-image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s specifications', 'smartkey-core' ), get_the_title() ) ); ?>">
									<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); else : ?><span class="skt-image-placeholder" aria-hidden="true">SK</span><?php endif; ?>
								</a>
								<div class="skt-card-body">
									<?php if ( $terms && ! is_wp_error( $terms ) ) : ?><p class="skt-card-family"><?php echo esc_html( $terms[0]->name ); ?></p><?php endif; ?>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
									<a class="skt-text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View specifications', 'smartkey-core' ); ?> <span aria-hidden="true">→</span></a>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
					<nav class="skt-pagination" aria-label="<?php esc_attr_e( 'Product results pages', 'smartkey-core' ); ?>"><?php echo wp_kses_post( paginate_links( array( 'type' => 'list', 'prev_text' => '← ' . __( 'Previous', 'smartkey-core' ), 'next_text' => __( 'Next', 'smartkey-core' ) . ' →' ) ) ); ?></nav>
				<?php else : ?>
					<p><?php esc_html_e( 'No products were found in this selection.', 'smartkey-core' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="skt-rfq-banner"><div class="skt-shell"><div><p class="skt-eyebrow"><?php esc_html_e( 'Qualified B2B inquiries', 'smartkey-core' ); ?></p><h2><?php esc_html_e( 'Need a current specification or commercial offer?', 'smartkey-core' ); ?></h2><p><?php esc_html_e( 'Tell us the product, quantity and destination. SmartKey coordinates the inquiry with the relevant supplier.', 'smartkey-core' ); ?></p></div><a class="skt-button" href="<?php echo esc_url( home_url( '/contact/?inquiry=petrochemical' ) ); ?>"><?php esc_html_e( 'Start an RFQ', 'smartkey-core' ); ?></a></div></section>
		</main>
		<?php
		return (string) ob_get_clean();
	}

	public static function render_single(): string {
		if ( ! is_singular( 'skt_product' ) ) {
			return '';
		}

		$post_id      = get_queried_object_id();
		$terms        = get_the_terms( $post_id, 'skt_product_family' );
		$family       = $terms && ! is_wp_error( $terms ) ? $terms[0] : null;
		$grade        = (string) get_post_meta( $post_id, 'skt_grade', true );
		$availability = (string) get_post_meta( $post_id, 'skt_availability_status', true );
		$disclosure   = (string) get_post_meta( $post_id, 'skt_representative_disclosure', true );
		$disclosure   = $disclosure ?: 'SmartKeyTurkey acts as an authorized sales representative and sourcing coordinator; it is not the manufacturer. Current specifications, availability, pricing and final commercial terms are confirmed for each inquiry.';
		$reviewed     = (string) get_post_meta( $post_id, 'skt_last_reviewed_date', true );

		ob_start();
		?>
		<main class="skt-product-single" id="main-content">
			<section class="skt-single-hero"><div class="skt-shell">
				<nav class="skt-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smartkey-core' ); ?></a><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_post_type_archive_link( 'skt_product' ) ); ?>"><?php esc_html_e( 'Products', 'smartkey-core' ); ?></a><?php if ( $family ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_term_link( $family ) ); ?>"><?php echo esc_html( $family->name ); ?></a><?php endif; ?></nav>
				<div class="skt-product-hero-grid">
					<div class="skt-product-media"><?php echo get_the_post_thumbnail( $post_id, 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="skt-product-summary">
						<?php if ( $family ) : ?><p class="skt-eyebrow"><?php echo esc_html( $family->name ); ?></p><?php endif; ?>
						<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
						<p class="skt-product-excerpt"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
						<dl class="skt-product-facts"><div><dt><?php esc_html_e( 'Grade', 'smartkey-core' ); ?></dt><dd><?php echo esc_html( $grade ?: get_the_title( $post_id ) ); ?></dd></div><div><dt><?php esc_html_e( 'Availability', 'smartkey-core' ); ?></dt><dd><?php echo esc_html( $availability ?: __( 'Confirm by RFQ', 'smartkey-core' ) ); ?></dd></div><?php if ( $reviewed ) : ?><div><dt><?php esc_html_e( 'Source reviewed', 'smartkey-core' ); ?></dt><dd><?php echo esc_html( $reviewed ); ?></dd></div><?php endif; ?></dl>
						<a class="skt-button" href="#request-quote"><?php esc_html_e( 'Request current terms', 'smartkey-core' ); ?></a>
					</div>
				</div>
			</div></section>

			<section class="skt-shell skt-product-detail-grid">
				<article class="skt-product-content"><?php echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></article>
				<aside class="skt-product-aside" aria-label="<?php esc_attr_e( 'Product inquiry details', 'smartkey-core' ); ?>"><h2><?php esc_html_e( 'Before requesting a quote', 'smartkey-core' ); ?></h2><ul><li><?php esc_html_e( 'Required quantity and packaging', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Destination country or port', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Preferred Incoterm and delivery date', 'smartkey-core' ); ?></li><li><?php esc_html_e( 'Required TDS, SDS or certificates', 'smartkey-core' ); ?></li></ul><?php if ( $disclosure ) : ?><p class="skt-disclosure"><?php echo esc_html( $disclosure ); ?></p><?php endif; ?></aside>
			</section>

			<section class="skt-rfq-section" id="request-quote"><div class="skt-shell"><div class="skt-section-heading"><div><p class="skt-eyebrow"><?php esc_html_e( 'Request for quotation', 'smartkey-core' ); ?></p><h2><?php echo esc_html( sprintf( __( 'Request %s', 'smartkey-core' ), get_the_title( $post_id ) ) ); ?></h2></div><p><?php esc_html_e( 'The product field is filled automatically. Add your commercial and technical requirements below.', 'smartkey-core' ); ?></p></div><?php echo do_shortcode( sprintf( '[contact-form-7 title="Petrochemical RFQ" product-grade="%s"]', esc_attr( get_the_title( $post_id ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></section>
		</main>
		<?php
		return (string) ob_get_clean();
	}

	public static function prefill_product_field( $tag ) {
		if ( ! is_object( $tag ) || 'product-grade' !== ( $tag->name ?? '' ) ) {
			return $tag;
		}

		$value = '';
		if ( is_singular( 'skt_product' ) ) {
			$value = get_the_title( get_queried_object_id() );
		} elseif ( isset( $_GET['product'] ) ) {
			$value = sanitize_text_field( wp_unslash( $_GET['product'] ) );
		}

		if ( '' !== $value ) {
			$tag->values = array( $value );
			$tag->raw_values = array( $value );
		}

		return $tag;
	}

	public static function enable_product_shortcode_default( array $properties, $contact_form ): array {
		if ( ! is_object( $contact_form ) || ! method_exists( $contact_form, 'title' ) || 'Petrochemical RFQ' !== $contact_form->title() || empty( $properties['form'] ) ) {
			return $properties;
		}

		$properties['form'] = str_replace( '[text* product-grade]', '[text* product-grade default:shortcode_attr]', $properties['form'] );
		return $properties;
	}

	public static function register_product_shortcode_attribute( array $out, array $pairs, array $atts ): array {
		if ( isset( $atts['product-grade'] ) ) {
			$out['product-grade'] = sanitize_text_field( $atts['product-grade'] );
		}
		return $out;
	}

	public static function maybe_seed_elementor_templates(): void {
		if ( get_option( 'skt_elementor_product_templates_version' ) === self::TEMPLATE_VERSION || ! post_type_exists( 'elementor_library' ) ) {
			return;
		}

		self::seed_template( self::ARCHIVE_TEMPLATE_TITLE, 'archive', '[skt_product_archive]', 'a11c0a01', 'a11c0a02' );
		self::seed_template( self::SINGLE_TEMPLATE_TITLE, 'single-post', '[skt_product_single]', 'b22c0b01', 'b22c0b02' );
		update_option( 'skt_elementor_product_templates_version', self::TEMPLATE_VERSION, false );
	}

	private static function seed_template( string $title, string $type, string $shortcode, string $container_id, string $widget_id ): void {
		$post_id = self::find_template_id( $title );
		if ( ! $post_id ) {
			return;
		}

		$backup_key = 'skt_elementor_backup_' . $post_id;
		if ( false === get_option( $backup_key, false ) ) {
			update_option( $backup_key, get_post_meta( $post_id, '_elementor_data', true ), false );
		}

		$data = array(
			array(
				'id'       => $container_id,
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array( 'content_width' => 'full', 'width' => array( 'unit' => '%', 'size' => 100, 'sizes' => array() ), 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ) ),
				'elements' => array(
					array(
						'id'         => $widget_id,
						'elType'     => 'widget',
						'widgetType' => 'shortcode',
						'settings'   => array( 'shortcode' => $shortcode ),
						'elements'   => array(),
					),
				),
			),
		);

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
	}

	private static function find_template_id( string $title ): int {
		$posts = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => array( 'publish', 'draft' ), 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		return $posts ? (int) $posts[0] : 0;
	}
}
