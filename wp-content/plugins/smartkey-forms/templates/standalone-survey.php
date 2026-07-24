<?php

defined( 'ABSPATH' ) || exit;

$is_persian = \SmartKeyTurkey\Forms\Survey_Manager::is_persian();

?><!doctype html>
<html lang="<?php echo esc_attr( $is_persian ? 'fa-IR' : 'en-US' ); ?>" dir="<?php echo esc_attr( $is_persian ? 'rtl' : 'ltr' ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'skf-standalone-survey' . ( $is_persian ? ' skf-survey-fa' : '' ) ); ?>>
	<main class="skf-survey-shell">
		<div class="skf-survey-topbar">
			<a class="skf-survey-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SmartKeyTurkey home', 'smartkey-forms' ); ?>">
				<span class="skf-brand-mark">SK</span><span>SmartKeyTurkey</span>
			</a>
			<nav class="skf-language-switcher" aria-label="Survey language">
				<a href="<?php echo esc_url( home_url( '/experience-survey/' ) ); ?>"<?php echo $is_persian ? '' : ' aria-current="page"'; ?>>English</a>
				<a href="<?php echo esc_url( add_query_arg( 'lang', 'fa', home_url( '/experience-survey/' ) ) ); ?>"<?php echo $is_persian ? ' aria-current="page"' : ''; ?>>فارسی</a>
			</nav>
		</div>
		<?php
		while ( have_posts() ) {
			the_post();
			echo do_shortcode( \SmartKeyTurkey\Forms\Survey_Manager::render() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<p class="skf-privacy-note"><?php echo esc_html( $is_persian ? 'پاسخ‌ها به‌صورت خصوصی در پنل مدیریت SmartKeyTurkey ذخیره می‌شوند و با ایمیل ارسال نخواهند شد.' : 'Responses are stored privately in SmartKeyTurkey’s WordPress administration and are not emailed.' ); ?></p>
	</main>
</body>
</html>
