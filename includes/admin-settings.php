<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add Admin Menu
function aawp_pcbuild_admin_menu() {
    add_menu_page(
        __('PC Build Plugin', 'aawp-pcbuild'),
        __('PC Build Settings', 'aawp-pcbuild'),
        'manage_options',
        'aawp_pcbuild',
        'aawp_pcbuild_settings_page',
        'dashicons-admin-generic',
        80
    );
}
add_action('admin_menu', 'aawp_pcbuild_admin_menu');

// Display Settings Page
function aawp_pcbuild_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Amazon API Settings', 'aawp-pcbuild'); ?></h1>

        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved successfully!', 'aawp-pcbuild'); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('aawp_pcbuild_options');
            do_settings_sections('aawp_pcbuild');
            wp_nonce_field('aawp_pcbuild_save_settings', 'aawp_pcbuild_nonce');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Amazon Access Key', 'aawp-pcbuild'); ?></th>
                    <td>
                        <input type="text" name="aawp_pcbuild_amazon_access_key" class="regular-text" value="<?php echo esc_attr(get_option('aawp_pcbuild_amazon_access_key')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Amazon Secret Key', 'aawp-pcbuild'); ?></th>
                    <td>
                        <input type="password" name="aawp_pcbuild_amazon_secret_key" class="regular-text" value="<?php echo esc_attr(get_option('aawp_pcbuild_amazon_secret_key')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Amazon Associate Tag', 'aawp-pcbuild'); ?></th>
                    <td>
                        <input type="text" name="aawp_pcbuild_amazon_associate_tag" class="regular-text" value="<?php echo esc_attr(get_option('aawp_pcbuild_amazon_associate_tag')); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'aawp-pcbuild')); ?>
        </form>
    </div>
    <?php
}

// Register Plugin Settings
function aawp_pcbuild_register_settings() {
    register_setting('aawp_pcbuild_options', 'aawp_pcbuild_amazon_access_key', 'sanitize_text_field');
    register_setting('aawp_pcbuild_options', 'aawp_pcbuild_amazon_secret_key', 'sanitize_text_field');
    register_setting('aawp_pcbuild_options', 'aawp_pcbuild_amazon_associate_tag', 'sanitize_text_field');
}
add_action('admin_init', 'aawp_pcbuild_register_settings');
