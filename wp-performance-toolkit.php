<?php
/*
Plugin Name: WP Performance Toolkit
Description: Lightweight performance plugin for caching, cleanup, and Core Web Vitals improvements.
Version: 1.0
Author: Rohit Kumar
*/

// Disable emojis
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Add caching headers
function wpt_add_cache_headers() {
    if (!is_user_logged_in()) {
        header("Cache-Control: public, max-age=31536000");
    }
}
add_action('send_headers', 'wpt_add_cache_headers');

// Limit WP Heartbeat
function wpt_heartbeat_settings($settings) {
    $settings['interval'] = 60;
    return $settings;
}
add_filter('heartbeat_settings', 'wpt_heartbeat_settings');
