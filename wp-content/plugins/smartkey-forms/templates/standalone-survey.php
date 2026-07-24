<?php

defined( 'ABSPATH' ) || exit;

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'skf-standalone-survey' ); ?>>
	<main class="skf-survey-shell">
		<a class="skf-survey-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-forms' ); ?>">
			<span class="skf-brand-mark">SK</span><span>SmartKeyTurkey</span>
		</a>
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
		<p class="skf-privacy-note">Responses are stored privately in SmartKeyTurkey’s WordPress administration and are not emailed.</p>
	</main>
</body>
</html>
