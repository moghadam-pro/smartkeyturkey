<?php
defined( 'ABSPATH' ) || exit;
get_header();
echo SmartKeyTurkey\Core\Editorial::archive(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
