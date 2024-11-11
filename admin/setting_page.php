<?php

// Variable to store error message
$error_message = '';

// Save settings if the form is submitted
if (isset($_POST['tgon_save_settings'])) {

    // Retrieve and sanitize input values from the form submission
    $this->retrieve_post_inputs([
        'pipedream_endpoint',
        'api_token',
        'chat_id',
        // 'message_template',
    ]);

    // Validate inputs to ensure they are correct
    $this->validate_inputs();

    // Check if there are no validation errors
    if (empty($this->error_message)) {
        // If inputs are valid save the settings to WordPress options table
        update_option('tgon_pipedream_endpoint',  $this->input['pipedream_endpoint']);
        update_option('tgon_chat_id', $this->input['chat_id']);
        update_option('tgon_api_token', $this->input['api_token']);
        update_option('tgon_order_statuses', $this->input['order_statuses']);
        update_option('tgon_message_template', $this->input['message_template']);
        update_option('tgon_enable_persian_date', $this->input['enable_persian_date']);

        // Display success message
        echo '<div class="updated"><p>' . __('Settings saved!.', 'wc-tgon') . '</p></div>';
    }
}

// Retrieve existing values from the options table to pre-populate the form
$pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
$chat_id = get_option('tgon_chat_id', ''); // Chat ID for Telegram
$api_token = get_option('tgon_api_token', ''); // Telegram API token
$options = get_option('tgon_order_statuses', []); // Order statuses that trigger messages

if (empty($options))
    $options = []; // Ensure options is an empty array if no values are found

$enable_jalali_date = get_option('tgon_enable_persian_date', false); // Whether to convert date to Persian (Jalali)
$template = get_option('tgon_message_template', "Order {order_id} placed by {buyer_name} for a total of {total}."); // message template


?>
<div class="wrap">
    <h1><? echo __('Telegram Notifications Settings', 'wc-tgon') ?></h1>

    <!-- Display error message if exists -->
    <?php if ($this->error_message): ?>
        <div class="error">
            <p><?php echo esc_html($this->error_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Form for saving the settings -->
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
            <tr valign="top">
                <th scope="row"><?php _e('Send message when order is :', 'wc-tgon') ?></th>
                <td>
                    <input type="checkbox" name="order_statuses[]" value="on-hold" <?php checked(in_array('on-hold', $options)); ?>><?php _e('On hold', 'wc-tgon')?><br>
                    <input type="checkbox" name="order_statuses[]" value="processing" <?php checked(in_array('processing', $options)); ?>><?php _e('Processing', 'wc-tgon')?><br>
                    <input type="checkbox" name="order_statuses[]" value="cancelled" <?php checked(in_array('cancelled', $options)); ?>><?php _e('Cancelled', 'wc-tgon')?><br>
                    <input type="checkbox" name="order_statuses[]" value="failed" <?php checked(in_array('failed', $options)); ?>><?php _e('Failed', 'wc-tgon')?><br>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Convert date to jalali :', 'wc-tgon') ?></th>
                <td>
                    <input type="checkbox" name="enable_persian_date" value="1" <?php checked(1, $enable_jalali_date, true); ?> />
                </td>

            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Message Template', 'wc-tgon') ?></th>
                <td>
                    <textarea name='message_template' rows='8' cols='50' class='large-text'><?php echo esc_attr($template); ?></textarea>
                    <p class='description'>
                        <?php
                        _e('Customize the message format that will be sent to Telegram. Use the following placeholders to insert order details dynamically:', 'wc-tgon');
                        echo "<br/><br/>";

                        // Render message template placeholders with descriptions
                        foreach ($this->template_placeholders as $placeholder => $description) {
                            echo "$placeholder : $description <br/>";
                        }
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Save Settings', 'wc-tgon'), 'primary', 'tgon_save_settings'); ?>
    </form>
</div>