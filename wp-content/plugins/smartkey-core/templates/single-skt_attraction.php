<?php
defined( 'ABSPATH' ) || exit;
get_header();
echo SmartKeyTurkey\Core\Attraction_Frontend::single(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
