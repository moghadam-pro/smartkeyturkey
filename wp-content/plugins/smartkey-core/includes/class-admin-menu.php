<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Admin_Menu {
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'register_menu' ), 5 );
		add_action( 'admin_head', array( self::class, 'admin_styles' ) );
	}

	public static function register_menu(): void {
		add_menu_page( 'SmartKeyTurkey', 'SmartKey', 'edit_posts', 'smartkey', array( self::class, 'render_dashboard' ), get_site_icon_url( 32 ), 4 );
		add_submenu_page( 'smartkey', 'SmartKey Overview', 'Overview', 'edit_posts', 'smartkey', array( self::class, 'render_dashboard' ) );
		add_submenu_page( 'smartkey', 'Website Requests', 'Requests', 'manage_options', 'edit.php?post_type=skt_request' );
		add_submenu_page( 'smartkey', 'Add Petrochemical Product', 'Add Product', 'edit_posts', 'post-new.php?post_type=skt_product' );
		add_submenu_page( 'smartkey', 'Product Families', 'Product Families', 'manage_categories', 'edit-tags.php?taxonomy=skt_product_family&post_type=skt_product' );
		add_submenu_page( 'smartkey', 'Add Property', 'Add Property', 'edit_posts', 'post-new.php?post_type=skt_property' );
		add_submenu_page( 'smartkey', 'Property Cities', 'Property Cities', 'manage_categories', 'edit-tags.php?taxonomy=skt_property_city&post_type=skt_property' );
		add_submenu_page( 'smartkey', 'Property Types', 'Property Types', 'manage_categories', 'edit-tags.php?taxonomy=skt_property_type&post_type=skt_property' );
		add_submenu_page( 'smartkey', 'Attractions', 'Attractions', 'edit_posts', 'edit.php?post_type=skt_attraction' );
		add_submenu_page( 'smartkey', 'Attraction Cities', 'Attraction Cities', 'manage_categories', 'edit-tags.php?taxonomy=skt_attraction_city&post_type=skt_attraction' );
		add_submenu_page( 'smartkey', 'Editorial Posts', 'News & Insights', 'edit_posts', 'edit.php' );
		add_submenu_page( 'smartkey', 'Editorial Categories', 'Editorial Categories', 'manage_categories', 'edit-tags.php?taxonomy=category' );
	}

	public static function render_dashboard(): void {
		$product_count = wp_count_posts( 'skt_product' );
		$property_count = wp_count_posts( 'skt_property' );
		?>
		<div class="wrap skt-admin-hub"><h1>SmartKeyTurkey</h1><p class="description">Content operations for properties, petrochemical products and qualified inquiries.</p><div class="skt-admin-cards"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skt_property' ) ); ?>"><strong><?php echo esc_html( (int) ( $property_count->publish ?? 0 ) ); ?></strong><span>Published properties</span><small><?php echo esc_html( (int) ( $property_count->draft ?? 0 ) ); ?> drafts awaiting review</small></a><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skt_product' ) ); ?>"><strong><?php echo esc_html( (int) ( $product_count->publish ?? 0 ) ); ?></strong><span>Petrochemical products</span><small>Manage grades and technical content</small></a><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=skt_property_city&post_type=skt_property' ) ); ?>"><strong><?php echo esc_html( (int) wp_count_terms( array( 'taxonomy' => 'skt_property_city', 'hide_empty' => false ) ) ); ?></strong><span>Property cities</span><small>Istanbul, Ankara, Izmir and Antalya</small></a></div><div class="skt-admin-actions"><a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=skt_property' ) ); ?>">Add property</a><a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=skt_product' ) ); ?>">Add product</a><a class="button" href="<?php echo esc_url( home_url( '/properties/' ) ); ?>" target="_blank" rel="noopener">View property archive</a></div><p><strong>Editorial rule:</strong> clearly label sample records, verify direct-control status and availability before launch, and replace sample data with approved property information.</p></div>
		<?php
	}

	public static function admin_styles(): void {
		?><style>.toplevel_page_smartkey .wp-menu-image img{width:20px!important;height:20px!important;padding-top:7px!important}.skt-admin-hub{max-width:1050px}.skt-admin-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:26px 0}.skt-admin-cards a{display:grid;min-height:128px;grid-template-rows:auto auto 1fr;align-content:start;padding:24px;border:1px solid #dcdcde;border-radius:14px;background:#fff;color:#1d2327;text-decoration:none;box-shadow:0 8px 30px rgba(0,0,0,.05)}.skt-admin-cards strong{display:block;margin:0 0 12px;color:#659f29;font-size:2.3rem;line-height:1}.skt-admin-cards span{display:block;font-size:1.05rem;font-weight:700;line-height:1.35}.skt-admin-cards small{align-self:end;margin-top:20px;color:#646970;line-height:1.4}.skt-admin-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px}@media(max-width:800px){.skt-admin-cards{grid-template-columns:1fr}}</style><?php
	}
}
