<?php

// Variable to store error message
$error_message = '';

// Save settings if the form is submitted
if (isset($_POST['tgon_save_settings'])) {
    // Retrieve and sanitize input

    $this->retrieve_post_inputs(['pipedream_endpoint', 'api_token', 'chat_id']);
    $pipedream_endpoint = $this->input['pipedream_endpoint'];
    $chat_id = $this->input['chat_id'];
    $api_token = $this->input['api_token'];


    // Validate the URL
    if (!filter_var($pipedream_endpoint, FILTER_VALIDATE_URL)) {
        $error_message = __('Invalid URL for the Pipedream endpoint. Please enter a valid URL.', 'wc-tgon');
    } else {
        // If URL is valid, save the settings
        update_option('tgon_pipedream_endpoint', $pipedream_endpoint);
        update_option('tgon_chat_id', $chat_id);
        update_option('tgon_api_token', $api_token);
        echo '<div class="updated"><p>' . __('Settings saved!.', 'wc-tgon') . '</p></div>';
    }
}

// Retrieve existing values
$pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
$chat_id = get_option('tgon_chat_id', '');
$api_token = get_option('tgon_api_token', '');

?>
<div class="wrap">
    <h1><? echo __('Telegram Notifications Settings', 'wc-tgon') ?></h1>

    <!-- Display error message if exists -->
    <?php if ($error_message): ?>
        <div class="error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Pipedream Endpoint', 'wc-tgon') ?></th>
                <td><input type="text" dir='ltr' name="pipedream_endpoint" value="<?php echo esc_attr($pipedream_endpoint); ?>" size="50" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Telegram Bot API Token', 'wc-tgon') ?></th>
                <td><input type="text" dir='ltr' name="api_token" value="<?php echo esc_attr($api_token); ?>" size="90" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Chat ID', 'wc-tgon') ?></th>
                <td><input type="text" dir='ltr' name="chat_id" value="<?php echo esc_attr($chat_id); ?>" size="50" /></td>
            </tr>
        </table>
        <?php submit_button(__('Save Settings', 'wc-tgon'), 'primary', 'tgon_save_settings'); ?>
    </form>
</div>