<?php
/*
Plugin Name: AAWP-PCBuild
Description: A plugin for Amazon affiliate-based PC part selection and dynamic product display via shortcode.
Version: 1.3
Author: Md. Kamruzzaman
Author URI: https://sparktech.agency/
License: GPL2
Text Domain: aawp-pcbuild
*/

if (!defined('ABSPATH')) exit;

// ==========================
// Define Plugin Constants
// ==========================
define('AAWP_PCBUILD_PATH', plugin_dir_path(__FILE__));
define('AAWP_PCBUILD_URL', plugin_dir_url(__FILE__));

// For Cron Jobs
register_activation_hook(__FILE__, 'aawp_pcbuild_schedule_cron');
register_deactivation_hook(__FILE__, 'aawp_pcbuild_clear_cron');

// ==========================
// Include Required Files
// ==========================
require_once AAWP_PCBUILD_PATH . 'includes/api-handler.php';
require_once AAWP_PCBUILD_PATH . 'includes/cron-handler.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-cpu.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-cpu-cooler.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-motherboard.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-memory.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-storage.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-video-card.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-case.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-power-supply.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-operating-system.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-monitor.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-sound-cards.php';
require_once AAWP_PCBUILD_PATH . 'includes/admin-settings.php';
require_once AAWP_PCBUILD_PATH . 'includes/pc-builder-ui.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-wired-network.php';
require_once AAWP_PCBUILD_PATH . 'includes/shortcode-headphones.php'; // Include the new headphones shortcode 


// ==========================
// Enqueue Plugin Styles & Scripts
// ==========================
function aawp_pcbuild_enqueue_styles() {
    // Plugin CSS
    wp_enqueue_style(
        'aawp-pcbuild-style',
        AAWP_PCBUILD_URL . 'assets/css/style.css',
        array(),
        filemtime(AAWP_PCBUILD_PATH . 'assets/css/style.css')
    );

    // jQuery dependency is loaded by default in WP, but explicitly listed
    // Plugin Main JS
    wp_enqueue_script(
        'aawp-pcbuild-main', // unique handle
        AAWP_PCBUILD_URL . 'assets/js/mainScript.js',
        array('jquery'),
        filemtime(AAWP_PCBUILD_PATH . 'assets/js/mainScript.js'),
        true
    );

    // PCBuild Filters JS
    wp_enqueue_script(
        'aawp-pcbuild-filters', // unique handle
        AAWP_PCBUILD_URL . 'assets/js/pcbuild-filters.js',
        array('jquery'),
        filemtime(AAWP_PCBUILD_PATH . 'assets/js/pcbuild-filters.js'),
        true
    );

     // Get the uploads base URL
     $upload_dir = wp_get_upload_dir();

     wp_localize_script('aawp-pcbuild-main', 'pcbuild_ajax_object', array(
         'ajax_url'       => admin_url('admin-ajax.php'),
         'associate_tag'  => get_option('aawp_pcbuild_amazon_associate_tag'),
         'uploads_url'    => $upload_dir['baseurl'],
     ));

    /* wp_enqueue_style(
        'index-css',
        get_stylesheet_directory_uri() . '/assets/css/index.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/index.css')
    ); */
}
add_action('wp_enqueue_scripts', 'aawp_pcbuild_enqueue_styles');

// ==========================
// Rewrite Rules for Custom URL Routing
// ==========================
function aawp_pcbuild_add_rewrite_rule() {
    add_rewrite_rule(
        '^pcbuildparts/products/?$', // Match URL like /pcbuildparts/products/
        'index.php?pagename=products', // Route to the 'products' page
        'top'
    );
}
add_action('init', 'aawp_pcbuild_add_rewrite_rule');

// ==========================
// Register Custom Query Variable
// ==========================
function aawp_pcbuild_add_query_vars($vars) {
    $vars[] = 'category'; // Register 'category' query variable
    return $vars;
}
add_filter('query_vars', 'aawp_pcbuild_add_query_vars');

// ==========================
// Inject Dynamic Shortcode Based on Category in Content
// ==========================
function aawp_pcbuild_dynamic_shortcode_injection($content) {
    if (is_page('products')) {
        $category = get_query_var('category'); // Retrieve the 'category' query variable
        if (!empty($category)) {
            // Clean up and sanitize the category query variable
            $category = sanitize_title($category);

            // Remove any existing [pcbuild_parts] shortcode from the content
            $content = preg_replace('/\[pcbuild_parts[^\]]*\]/i', '', $content);

            // Append the dynamic shortcode for the given category
            $content .= do_shortcode('[pcbuild_parts category="' . esc_attr($category) . '"]');
        }
    }
    return $content;
}
add_filter('the_content', 'aawp_pcbuild_dynamic_shortcode_injection');


// Flush rewrite rules upon plugin activation
function aawp_pcbuild_activate_plugin() {
    aawp_pcbuild_add_rewrite_rule(); // Add custom rules
    flush_rewrite_rules(); // Refresh rewrite rules
}
register_activation_hook(__FILE__, 'aawp_pcbuild_activate_plugin');

// ==========================
// Plugin Activation Hook
// ==========================

function aawp_pcbuild_activate() {
    if (!current_user_can('activate_plugins')) return;

    // Add options upon activation
    add_option('aawp_pcbuild_amazon_access_key', '');
    add_option('aawp_pcbuild_amazon_secret_key', '');
    add_option('aawp_pcbuild_amazon_associate_tag', '');

    // Flush rewrite rules after adding custom ones
    aawp_pcbuild_add_rewrite_rule();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'aawp_pcbuild_activate');


// ==========================
// Plugin Deactivation Hook
// ==========================
/* function aawp_pcbuild_deactivate() {
    if (!current_user_can('activate_plugins')) return;
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'aawp_pcbuild_deactivate'); */

function aawp_pcbuild_deactivate() {
    if (!current_user_can('activate_plugins')) return;

    // Clear rewrite rules and cron
    flush_rewrite_rules();
    aawp_pcbuild_clear_cron();

    // Delete all transients set by this plugin
    global $wpdb;
    $wpdb->query("
        DELETE FROM $wpdb->options
        WHERE option_name LIKE '_transient_aawp_pcbuild_cache_%'
           OR option_name LIKE '_transient_timeout_aawp_pcbuild_cache_%'
    ");
}
register_deactivation_hook(__FILE__, 'aawp_pcbuild_deactivate');


// ==========================
// Plugin Uninstall Hook
// ==========================
/* function aawp_pcbuild_uninstall() {
    if (!current_user_can('activate_plugins')) return;

    delete_option('aawp_pcbuild_amazon_access_key');
    delete_option('aawp_pcbuild_amazon_secret_key');
    delete_option('aawp_pcbuild_amazon_associate_tag');
}
register_uninstall_hook(__FILE__, 'aawp_pcbuild_uninstall'); */

function aawp_pcbuild_uninstall() {
    if (!current_user_can('activate_plugins')) return;

    delete_option('aawp_pcbuild_amazon_access_key');
    delete_option('aawp_pcbuild_amazon_secret_key');
    delete_option('aawp_pcbuild_amazon_associate_tag');

    global $wpdb;
    $wpdb->query("
        DELETE FROM $wpdb->options
        WHERE option_name LIKE '_transient_aawp_pcbuild_cache_%'
           OR option_name LIKE '_transient_timeout_aawp_pcbuild_cache_%'
    ");
}
register_uninstall_hook(__FILE__, 'aawp_pcbuild_uninstall');