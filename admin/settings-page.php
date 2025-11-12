<?php

// Add menu page
function wpt_add_settings_menu() {
    add_options_page(
        'WP Performance Toolkit',
        'WP Performance Toolkit',
        'manage_options',
        'wpt-settings',
        'wpt_settings_page'
    );
}
add_action('admin_menu', 'wpt_add_settings_menu');


// Register settings
function wpt_register_settings() {
    register_setting('wpt_settings_group', 'wpt_settings');
}
add_action('admin_init', 'wpt_register_settings');


// Settings Page UI
function wpt_settings_page() {

    $options = get_option('wpt_settings');

    ?>
    <div class="wrap">
        <h1>WP Performance Toolkit</h1>
        <p>Optimize your WordPress performance using lightweight, developer-friendly settings.</p>

        <form method="post" action="options.php">
            <?php settings_fields('wpt_settings_group'); ?>
            <?php do_settings_sections('wpt_settings_group'); ?>

            <table class="form-table">

                <tr valign="top">
                    <th scope="row">Disable Emojis</th>
                    <td>
                        <input type="checkbox" name="wpt_settings[disable_emojis]" value="1"
                        <?php checked(1, isset($options['disable_emojis']) ? $options['disable_emojis'] : 0); ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Enable Caching Headers</th>
                    <td>
                        <input type="checkbox" name="wpt_settings[enable_cache]" value="1"
                        <?php checked(1, isset($options['enable_cache']) ? $options['enable_cache'] : 0); ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Limit Heartbeat API</th>
                    <td>
                        <input type="checkbox" name="wpt_settings[limit_heartbeat]" value="1"
                        <?php checked(1, isset($options['limit_heartbeat']) ? $options['limit_heartbeat'] : 0); ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Enable Security Headers</th>
                    <td>
                        <input type="checkbox" name="wpt_settings[enable_security_headers]" value="1"
                        <?php checked(1, isset($options['enable_security_headers']) ? $options['enable_security_headers'] : 0); ?> />
                    </td>
                </tr>

            </table>

            <hr>

            <h2>Database Optimization</h2>
            <p>Preview and clean unnecessary data such as revisions, drafts, trashed posts, and expired transients.</p>

            <form method="post">
                <input type="hidden" name="wpt_preview_db" value="1" />
                <?php submit_button("Preview Optimization"); ?>
            </form>

            <?php
            if (isset($_POST['wpt_preview_db'])) {
                $preview = wpt_get_optimization_preview();

                echo '<div class="notice notice-info"><h3>Optimization Preview</h3>';
                echo '<p><strong>Post Revisions:</strong> ' . $preview['revisions'] . '</p>';
                echo '<p><strong>Auto Drafts:</strong> ' . $preview['auto_drafts'] . '</p>';
                echo '<p><strong>Trashed Posts:</strong> ' . $preview['trashed'] . '</p>';
                echo '<p><strong>Expired Transients:</strong> ' . $preview['expired_transients'] . '</p>';
                echo '<p><strong>Database Overhead:</strong> ' . $preview['overhead_mb'] . ' MB</p>';

                echo '</div>';

                echo '<form method="post">';
                echo '<input type="hidden" name="wpt_optimize_db" value="1" />';
                submit_button("Run Optimization Now", "primary");
                echo '</form>';
            }
            ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
