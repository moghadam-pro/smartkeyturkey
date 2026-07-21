<?php
defined( 'ABSPATH' ) || exit;
get_header();
SmartKeyTurkey\Core\Product_Frontend::render_elementor_template( 'SmartKey — Petrochemical Single Product', '[skt_product_single]' );
get_footer();
