<?php

// Save settings if the form is submitted
if (isset($_POST['tgon_save_settings'])) {


    if (!check_admin_referer('tgon_save_settings_action', 'tgon_save_settings_nonce')) {
        wp_die(esc_html__('Security check failed!', 'telegram-order-notification'));
    }

    // Retrieve and sanitize input values from the form submission
    $this->retrieve_post_inputs([
        'middleman_endpoint',
        'api_token',
        'chat_id',
    ]);

    // Validate inputs to ensure they are correct
    $this->validate_inputs();

    // if there are no validation errors, save the settings to WordPress options table.
    if (empty($this->error_messages)) {

        $old_settings = get_option('tgon_settings', []);

        $middleman_endpoint = $old_settings['middleman_endpoint'] ?? null;

        // update the middleman endpoint value only id use_middelman is selected. otherwise, use the old value.
        if ($this->input['use_middleman'] === 1) {
            $middleman_endpoint = $this->input['middleman_endpoint'];
        }

        $tgon_settings = [
            'use_middleman' => $this->input['use_middleman'],
            'middleman_endpoint' =>  $middleman_endpoint,
            'chat_id' => $this->input['chat_id'],
            'api_token' => $this->input['api_token'],
            'acceptable_order_statuses' => $this->input['acceptable_order_statuses'],
            'message_template' => $this->input['message_template'],
        ];

        update_option('tgon_settings', $tgon_settings);

        // Display success message
        echo '<div class="updated"><p>' . esc_html__('Settings saved!', 'telegram-order-notification') . '</p></div>';
    }
}

// Retrieve existing values from the options table to pre-populate the form
$saved_settings = get_option('tgon_settings', []);

$use_middleman = (isset($saved_settings['use_middleman']) and in_array($saved_settings['use_middleman'], [0, 1])) ? $saved_settings['use_middleman'] : 0;
$middleman_endpoint = $saved_settings['middleman_endpoint'] ?? '';
$chat_id = $saved_settings['chat_id'] ?? ''; // Chat ID for Telegram
$api_token = $saved_settings['api_token'] ?? ''; // Telegram API token
$order_status_options = $saved_settings['acceptable_order_statuses'] ?? []; // Order statuses that trigger messages

// If no statuses are set (the value is an empty string), use an empty array to prevent errors
empty($order_status_options) && $order_status_options = [];

$template = $saved_settings['message_template'] ?? esc_html__('Order {order_id} placed by {buyer_name} for a total of {total}.', 'telegram-order-notification'); // message template
?>
<div class="wrap">
    <h1><?php esc_html_e('Telegram Notifications Settings', 'telegram-order-notification') ?></h1>

    <!-- Display error message if exists -->
    <?php if ($this->error_messages): ?>
        <div class="error">
            <?php
            foreach ($this->error_messages as $error) {
                echo "<p>" . esc_html($error) . '</p>';
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Form for saving the settings -->
    <form method="post" action="">
        <?php
        // Add nonce field to form
        wp_nonce_field('tgon_save_settings_action', 'tgon_save_settings_nonce');
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Telegram Bot API Token', 'telegram-order-notification') ?></th>
                <td><input type="text" dir='ltr' name="api_token" value="<?php echo esc_attr($api_token); ?>" size="60" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Telegram Chat ID', 'telegram-order-notification') ?></th>
                <td><input type="text" dir='ltr' name="chat_id" value="<?php echo esc_attr($chat_id); ?>" size="50" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Send message when order is :', 'telegram-order-notification') ?></th>
                <td>
                    <input type="checkbox" name="acceptable_order_statuses[]" value="on-hold" <?php checked(in_array('on-hold', $order_status_options)); ?>><?php esc_html_e('On hold', 'telegram-order-notification') ?><br>
                    <input type="checkbox" name="acceptable_order_statuses[]" value="processing" <?php checked(in_array('processing', $order_status_options)); ?>><?php esc_html_e('Processing', 'telegram-order-notification') ?><br>
                    <input type="checkbox" name="acceptable_order_statuses[]" value="cancelled" <?php checked(in_array('cancelled', $order_status_options)); ?>><?php esc_html_e('Cancelled', 'telegram-order-notification') ?><br>
                    <input type="checkbox" name="acceptable_order_statuses[]" value="failed" <?php checked(in_array('failed', $order_status_options)); ?>><?php esc_html_e('Failed', 'telegram-order-notification') ?><br>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Send request to telegram api using a middleman :', 'telegram-order-notification') ?></th>
                <td>
                    <input type="checkbox" name="use_middleman" id="use-middleman-checkbox" <?php checked(1, $use_middleman) ?>>
                    <p class="description">
                        <?php
                        esc_html_e("This option allows you to route requests to the Telegram API through an intermediary service (middleman).", 'telegram-order-notification');
                        echo "<br />";
                        esc_html_e("Enabling this can be useful if direct access to the Telegram API is restricted in your server.", 'telegram-order-notification');
                        echo "<br />";
                        esc_html_e("After enabling, you will need to specify the middleman endpoint you want to use.", 'telegram-order-notification');
                        ?>
                    </p>
                </td>
            </tr>
            <tr valign="top" id="middleman-endpoint-row" style="display: none;">
                <th scope="row"><?php esc_html_e('Middleman Endpoint', 'telegram-order-notification') ?></th>
                <td><input type="text" dir='ltr' name="middleman_endpoint" value="<?php echo esc_attr($middleman_endpoint); ?>" size="50" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Message Template', 'telegram-order-notification') ?></th>
                <td>
                    <textarea name='message_template' rows='8' cols='50' class='large-text'><?php echo esc_attr($template); ?></textarea>
                    <p class='description'>
                        <?php
                        esc_html_e('Customize the message format that will be sent to Telegram. Use the following placeholders to insert order details dynamically:', 'telegram-order-notification');
                        echo "<br/><br/>";

                        // Render message template placeholders with descriptions
                        foreach ($this->template_placeholders as $placeholder => $description) {
                            echo esc_html($placeholder) . " : " . esc_html($description) . "<br/>";
                        }
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(esc_html__('Save Settings', 'telegram-order-notification'), 'primary', 'tgon_save_settings'); ?>
    </form>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the checkbox and the middleman endpoint row
        var checkbox = document.getElementById('use-middleman-checkbox');
        var middlemanEndpointRow = document.getElementById('middleman-endpoint-row');

        // Toggle visibility based on checkbox state
        checkbox.addEventListener('change', function() {
            if (checkbox.checked) {
                middlemanEndpointRow.style.display = 'table-row';
            } else {
                middlemanEndpointRow.style.display = 'none';
            }
        });

        // Set initial visibility based on checkbox state
        if (checkbox.checked) {
            middlemanEndpointRow.style.display = 'table-row';
        } else {
            middlemanEndpointRow.style.display = 'none';
        }
    });
</script>