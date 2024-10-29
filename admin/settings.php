<?php

// Hook to add admin menu
add_action('admin_menu', 'tgon_add_admin_menu');

function tgon_add_admin_menu() {
    add_submenu_page(
        'woocommerce', // Parent slug (WooCommerce)
        'Telegram Notifications', // Page title
        'Telegram Notifications', // Menu title
        'manage_options', // Capability
        'telegram_notifications', // Menu slug
        'tgon_settings_page' // Function to display the settings page
    );
}

// Display the settings page
function tgon_settings_page() {
    // Check if the user is allowed to access this page
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form is submitted
    if (isset($_POST['tgon_save_settings'])) {
        update_option('tgon_pipedream_endpoint', sanitize_text_field($_POST['pipedream_endpoint']), false);
        update_option('tgon_chat_id', sanitize_text_field($_POST['chat_id']));
        echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    // Retrieve existing values
    $pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
    $chat_id = get_option('tgon_chat_id', '');

    ?>
    <div class="wrap">
        <h1>Telegram Notifications Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Pipedream Endpoint</th>
                    <td><input type="text" name="pipedream_endpoint" value="<?php echo esc_attr($pipedream_endpoint); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Chat ID</th>
                    <td><input type="text" name="chat_id" value="<?php echo esc_attr($chat_id); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button('Save Settings', 'primary', 'tgon_save_settings'); ?>
        </form>
    </div>
    <?php
}
