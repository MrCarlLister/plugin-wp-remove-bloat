<?php
/**
 * Plugin Name:  Remove WordPress Bloat
 * Plugin URI:   https://www.mrcarllister.co.uk/
 * Description:  Remove lots of needless functionality, css and js from WordPress.
 * Version:      1.0.0
 * Author:       Carl Lister
 * Author URI:   https://www.mrcarllister.co.uk/
 */

 // Add new constant that returns true if WooCommerce is active
define( 'IS_WOOCOMMERCE_ACTIVE', class_exists( 'WooCommerce' ) );

// ************* Remove default Posts type since no blog *************

// Remove side menu
add_action( 'admin_menu', 'remove_default_post_type' );

function remove_default_post_type() {
    remove_menu_page( 'edit.php' );
}

// Remove +New post in top Admin Menu Bar
add_action( 'admin_bar_menu', 'remove_default_post_type_menu_bar', 999 );

function remove_default_post_type_menu_bar( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'new-post' );
}

// Remove Quick Draft Dashboard Widget
add_action( 'wp_dashboard_setup', 'remove_draft_widget', 999 );

function remove_draft_widget(){
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}

// End remove post type

/**
 * REMOVE EMOJI SUPPORT
 */
add_action( 'init', function () {
    // Front-end
    remove_action( 'wp_head', 'wp-block-library' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    add_filter( 'emoji_svg_url', '__return_false' );
    // Admin
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    add_filter( 'tiny_mce_plugins', function ( $plugins ) {
      if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
      }
      return array();
    });
    // Feeds
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    // Emails
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  });


  /**
   * Remove feeds and wordpress-specific content that is generated on the wp_head hook.
   * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
   */
  add_action( 'init', function () {

      remove_action('wp_head','start_post_rel_link',10,0);
      remove_action('wp_head','index_rel_link');
      remove_action('wp_head','adjacent_posts_rel_link_wp_head', 10, 0 );
      remove_action('wp_head','wp_shortlink_wp_head', 10, 0 );
      remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
      remove_action('wp_head','qtranxf_wp_head_meta_generator');

    // Remove the Really Simple Discovery service link
    remove_action( 'wp_head', 'rsd_link' );
    // Remove the link to the Windows Live Writer manifest
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Remove the general feeds
    remove_action( 'wp_head', 'feed_links', 2 );
    // Remove the extra feeds, such as category feeds
    remove_action( 'wp_head','feed_links_extra', 3 );
    // Remove the displayed XHTML generator
    remove_action( 'wp_head', 'wp_generator' );
    // Remove the REST API link tag
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    // Remove oEmbed discovery links.
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
    // Remove rel next/prev links
    remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
  });

  // Disable support for comments and trackbacks in post types
  add_action( 'init', function () {
      $post_types = get_post_types();
      foreach ($post_types as $post_type) {
          if(post_type_supports($post_type, 'comments')) {
              remove_post_type_support($post_type, 'comments');
              remove_post_type_support($post_type, 'trackbacks');
          }
      }
  });




  // Close comments on the front-end
  function ee_mph__disable_comments_status() {
      return false;
  }
  add_filter('comments_open', 'ee_mph__disable_comments_status', 20, 2);
  add_filter('pings_open', 'ee_mph__disable_comments_status', 20, 2);


  // Hide existing comments
  function ee_mph__disable_comments_hide_existing_comments($comments) {
      $comments = array();
      return $comments;
  }
  add_filter('comments_array', 'ee_mph__disable_comments_hide_existing_comments', 10, 2);
  // Remove comments page in menu
  function ee_mph__disable_comments_admin_menu() {
      remove_menu_page('edit-comments.php');
  }
  add_action('admin_menu', 'ee_mph__disable_comments_admin_menu');
  // Redirect any user trying to access comments page
  function ee_mph__disable_comments_admin_menu_redirect() {
      global $pagenow;
      if ($pagenow === 'edit-comments.php') {
          wp_redirect(admin_url()); exit;
      }
  }
  add_action('admin_init', 'ee_mph__disable_comments_admin_menu_redirect');
  // Remove comments metabox from dashboard
  function ee_mph__disable_comments_dashboard() {
      remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  }
  add_action('admin_init', 'ee_mph__disable_comments_dashboard');
  // Remove comments links from admin bar
  function ee_mph__disable_comments_admin_bar() {
      if (is_admin_bar_showing()) {
          remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
      }
  }
  add_action('init', 'ee_mph__disable_comments_admin_bar');


  // Remove dashboard widgets
  function ee_mph__remove_dashboard_meta() {
      if ( ! current_user_can( 'manage_options' ) ) {
          remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
          remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
          remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
          remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
      }
  }
  add_action( 'admin_init', 'ee_mph__remove_dashboard_meta' ); 

  // Custom Admin footer
  function ee_mph__remove_footer_admin () {
      echo '<span id="footer-thankyou">Bespoke development by <a href="https://www.mrcarllister.co.uk/" target="_blank">Carl Lister</a></span>';
  }
  add_filter( 'admin_footer_text', 'ee_mph__remove_footer_admin' );

  function ee_mph__admin_bar_remove_logo() {
      global $wp_admin_bar;
      $wp_admin_bar->remove_menu( 'wp-logo' );
  }
  add_action( 'wp_before_admin_bar_render', 'ee_mph__admin_bar_remove_logo', 0 );


if ( IS_WOOCOMMERCE_ACTIVE ) {

  
//
// ─── ADD WOOCOMMERCE SUPPORT TO THEME ───────────────────────────────────────────
//

add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );

    
} );


//
// ─── REMOVE WOOCOMMERCE STYLING ─────────────────────────────────────────────────
//
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );


//
// ─── ADD SUPPORT FOR WOO LIBRARIES ──────────────────────────────────────────────
//
add_theme_support( 'wc-product-gallery-slider' );
add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );


//
// ─── REMOVE SHOP TITLE ──────────────────────────────────────────────────────────
//
add_filter( 'woocommerce_show_page_title', '__return_false' );


}