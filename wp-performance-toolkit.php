<?php
/*
Plugin Name: WP Performance Toolkit
Description: Lightweight performance plugin for caching, cleanup, and Heartbeat optimization.
Version: 1.0
Author: Rohit Kumar
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Include settings page
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';

/*
|--------------------------------------------------------------------------
| SETTINGS HANDLERS
|--------------------------------------------------------------------------
*/

$options = get_option('wpt_settings');

/* Disable emojis */
if ( isset($options['disable_emojis']) && $options['disable_emojis'] == 1 ) {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}

/* Add caching headers */
if ( isset($options['enable_cache']) && $options['enable_cache'] == 1 ) {
    function wpt_add_cache_headers() {
        if ( !is_user_logged_in() ) {
            header("Cache-Control: public, max-age=31536000");
        }
    }
    add_action('send_headers', 'wpt_add_cache_headers');
}

/* Limit WP Heartbeat */
if ( isset($options['limit_heartbeat']) && $options['limit_heartbeat'] == 1 ) {
    function wpt_heartbeat_settings( $settings ) {
        $settings['interval'] = 60;
        return $settings;
    }
    add_filter( 'heartbeat_settings', 'wpt_heartbeat_settings' );
}
