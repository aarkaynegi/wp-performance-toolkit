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

/* Add security headers */
if ( isset($options['enable_security_headers']) && $options['enable_security_headers'] == 1 ) {

    function wpt_add_security_headers() {

        // Prevent clickjacking
        header("X-Frame-Options: SAMEORIGIN");

        // Prevent MIME sniffing
        header("X-Content-Type-Options: nosniff");

        // XSS protection for old browsers
        header("X-XSS-Protection: 1; mode=block");

        // Referrer policy
        header("Referrer-Policy: no-referrer-when-downgrade");

        // Permissions Policy (block access to sensitive device APIs)
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

        // Enable Strict-Transport-Security (HTTPS only)
        if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }

    add_action('send_headers', 'wpt_add_security_headers');
}

/*
|--------------------------------------------------------------------------
| DATABASE OPTIMIZATION
|--------------------------------------------------------------------------
*/

function wpt_optimize_database() {

    global $wpdb;

    // 1. Delete post revisions
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");

    // 2. Delete auto-drafts
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'");

    // 3. Delete trashed posts
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'");

    // 4. Delete expired transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_timeout_%' 
         AND option_value < UNIX_TIMESTAMP()"
    );

    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_%' 
         AND option_name NOT LIKE '_transient_timeout_%'"
    );

    // 5. Optimize all tables
    $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

    foreach ($tables as $table) {
        $wpdb->query("OPTIMIZE TABLE {$table[0]}");
    }

    return true;
}

// Handle button action
if (isset($_POST['wpt_optimize_db']) && current_user_can('manage_options')) {
    wpt_optimize_database();
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>Database optimized successfully.</p></div>';
    });
}

/*
|--------------------------------------------------------------------------
| DATABASE OPTIMIZATION PREVIEW
|--------------------------------------------------------------------------
*/

function wpt_get_optimization_preview() {
    global $wpdb;
    $preview = [];

    // Count revisions
    $preview['revisions'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
    );

    // Count auto drafts
    $preview['auto_drafts'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
    );

    // Count trashed posts
    $preview['trashed'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'"
    );

    // Count expired transients
    $preview['expired_transients'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_timeout_%'
         AND option_value < UNIX_TIMESTAMP()"
    );

    // Estimate overhead (unused space)
    $overhead = $wpdb->get_results("SHOW TABLE STATUS");

    $total_overhead = 0;
    foreach ($overhead as $table) {
        $total_overhead += $table->Data_free;
    }

    $preview['overhead_mb'] = round($total_overhead / 1024 / 1024, 2);

    return $preview;
}
