<?php

/**
 * Class tgon_telegram_message
 * Handles the preparation and sending of order details to Telegram via a bot.
 */
class tgon_telegram_message
{
    public $order, $message_text;

    /**
     * Constructor to initialize the class with the order object.
     *
     * @param WC_Order $order WooCommerce order object
     */
    public function __construct($order)
    {
        $this->order = $order; // Assign the WooCommerce order object to the class property
    }

    /**
     * Prepares the message to be sent to Telegram by replacing placeholders with actual order details.
     *
     * This method fetches the message template from the plugin's settings, prepares the data, and formats
     * it according to the selected preferences (including Persian date conversion).
     */
    public function prepare_message()
    {
        // Default message template with placeholders for order details
        $default_message_template = esc_html__('Order {order_id} placed by {buyer_name} for a total of {total}.', 'telegram-order-notification');

        // Retrieve the custom message template from the plugin settings, if any
        $settings = get_option('tgon_settings', []);
        $message_template = $settings['message_template'] ?? $default_message_template;

        // Get the order object
        $order_obj = $this->order;

        // Add order status to the data array
        $order['{status}'] = $order_obj->get_status(); // Get the current status of the order

        // Set the order date to the payment date if available; otherwise, use the order creation date as a fallback.
        $order['{date}'] = $order_obj->get_date_paid() ?? $order_obj->get_date_created();

        // convert date timestamp to standard date format in local timezone.
        $order['{date}'] = wp_date('d-m-Y', $order['{date}']->getTimestamp(), wp_timezone());

        // Add a divider line for message readability
        $order['{divider}'] = "----------------------------------------------";

        // Add more order details to the array of placeholders
        $order = array_merge($order, [
            '{id}'              => $order_obj->get_id(), // Order ID
            '{items}'           => $order_obj->get_items(), // Order items
            '{buyer_name}'      => $order_obj->get_formatted_billing_full_name(), // Buyer name
            '{full_address}'    => $order_obj->get_shipping_state() . ". " . $order_obj->get_shipping_city() . ". " . $order_obj->get_shipping_address_1() . " . " . $order_obj->get_shipping_address_2(), // Full address
            '{postal_code}'     => $order_obj->get_shipping_postcode(), // Shipping postcode
            '{phone_number}'    => $order_obj->get_billing_phone(), // Buyer phone number
            '{payment_method}'  => $order_obj->get_payment_method_title(), // Payment method
            '{shipping_method}' => $order_obj->get_shipping_method(), // Shipping method
            '{customer_note}'   => $order_obj->get_customer_note(), // Customer note
            '{total}'           => $order_obj->get_total(), // Order total
            '{shipping_total}'  => $order_obj->get_shipping_total(), // Shipping total
        ]);

        // Prepare the items list for the message
        $order['{all_items}'] = "";
        foreach ($order['{items}'] as $item_id => $item) {
            $order['{all_items}'] .= $item->get_name() . " - x" . $item->get_quantity() . "\n"; // Format each item as "item name - quantity"
        }
        unset($order['{items}']); // Remove items placeholder after formatting the list

        // Replace placeholders in the message template with actual order data
        $this->message_text = str_replace(array_keys($order), array_values($order), $message_template);
    }

    /**
     * Sends the prepared message to the Telegram bot via the provided API.
     *
     * This function sends a POST request to the Telegram bot using the middleman endpoint, 
     * which processes and forwards the message to the designated Telegram chat.
     */
    public static function send_message($message_text)
    {
        // Retrieve necessary values from the plugin settings
        $settings = get_option('tgon_settings', []);

        $use_middleman = (isset($settings['use_middleman']) and in_array($settings['use_middleman'], [0, 1])) ? $settings['use_middleman'] : 0;
        $middleman_endpoint = $settings['middleman_endpoint'] ?? ''; // middleman endpoint URL
        $chat_id = $settings['chat_id'] ?? ''; // Telegram chat ID
        $api_token = $settings['api_token'] ?? ''; // Telegram bot API token

        // Ensure all required parameters are set before sending the message
        if (
            ($use_middleman === 1 and empty($middleman_endpoint)) or
            empty($chat_id) or
            empty($api_token) or
            empty($message_text)
        ) {
            return; // If any parameters are missing, do nothing
        }

        if ($use_middleman === 0) {
            $response = self::send_message_directly_to_telegram($api_token, $chat_id, $message_text);
            // echo "direct";
        } else {
            $response = self::send_message_using_middleman($api_token, $chat_id, $middleman_endpoint, $message_text);
            // echo "using middleman";
        }

        // Handle the response (error or success)
        if (is_wp_error($response)) {
            // $error_message = $response->get_error_message();
        } else {
            // $body = wp_remote_retrieve_body($response);
        }

        return;
    }

    private static function send_message_directly_to_telegram($api_token, $chat_id, $message_text)
    {
        // Prepare the payload for the POST request
        $Payloads = [
            "text" => $message_text,
            "parse_mode" => 'HTML',
            "chat_id" => $chat_id,
        ];

        $telegram_api_address = "https://api.telegram.org/bot{$api_token}/SendMessage";
        // Send the message to Telegram using the middleman endpoint
        $response = wp_remote_post($telegram_api_address, array(
            'method'      => 'POST',
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode($Payloads),
        ));

        return $response;
    }

    private static function send_message_using_middleman($api_token, $chat_id, $middleman_endpoint, $message_text)
    {
        // Prepare the payload for the POST request
        $Payloads = [
            "msg" => $message_text,
            "api_token" => $api_token,
            "chat_id" => $chat_id,
        ];
        // Send the message to Telegram using the middleman endpoint
        $response = wp_remote_post($middleman_endpoint, array(
            'method'      => 'POST',
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode($Payloads),
        ));

        return $response;
    }

    public static function test_message($text)
    {
        self::send_message($text);
        $response = array(
            'message' => 'test message sent',
        );

        // Return a JSON response
        wp_send_json_success($response);
    }
}
