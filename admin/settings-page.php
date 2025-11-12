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

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
