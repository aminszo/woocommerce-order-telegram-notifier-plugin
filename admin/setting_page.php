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
        $error_message = 'Invalid URL for the Pipedream endpoint. Please enter a valid URL.';
    } else {
        // If URL is valid, save the settings
        update_option('tgon_pipedream_endpoint', $pipedream_endpoint);
        update_option('tgon_chat_id', $chat_id);
        update_option('tgon_api_token', $api_token);
        echo '<div class="updated"><p>'.__('Settings saved!.').'</p></div>';
    }
}

// Retrieve existing values
$pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
$chat_id = get_option('tgon_chat_id', '');
$api_token = get_option('tgon_api_token', '');

?>
<div class="wrap">
    <h1><? echo __('Telegram Notifications Settings') ?></h1>

    <!-- Display error message if exists -->
    <?php if ($error_message): ?>
        <div class="error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Pipedream Endpoint</th>
                <td><input type="text" name="pipedream_endpoint" value="<?php echo esc_attr($pipedream_endpoint); ?>" size="50" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Telegram Bot API Token</th>
                <td><input type="text" name="api_token" value="<?php echo esc_attr($api_token); ?>" size="90" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Chat ID</th>
                <td><input type="text" name="chat_id" value="<?php echo esc_attr($chat_id); ?>" size="50" /></td>
            </tr>
        </table>
        <?php submit_button('Save Settings', 'primary', 'tgon_save_settings'); ?>
    </form>
</div>