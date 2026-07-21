<?php
defined( 'ABSPATH' ) || exit;
get_header();
echo SmartKeyTurkey\Core\Editorial::single(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
