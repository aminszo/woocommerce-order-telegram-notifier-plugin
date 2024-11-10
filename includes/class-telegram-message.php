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
        $default_message_template = "Order {order_id} placed by {buyer_name} for a total of {total}.";
        
        // Retrieve the custom message template from the plugin settings, if any
        $message_template = get_option('tgon_message_template', $default_message_template);

        // Get the order object
        $order_obj = $this->order;

        // Include the Jalali date conversion library
        require_once "jalali-date-v2.76.php";

        // Add order status to the data array
        $order['{status}'] = $order_obj->get_status(); // Get the current status of the order

        // Set the order date depending on the order status
        if ($order['{status}'] === 'on-hold') {
            $order['{date}'] = $order_obj->get_date_created();  // Order creation date
        } else {
            $order['{date}'] = $order_obj->get_date_paid();  // Order paid date
        }

        // Check if Persian (Jalali) date conversion is enabled
        $enable_persian_date = get_option('tgon_enable_persian_date', false);

        if ($enable_persian_date) {
            // Convert the date to Persian (Jalali) format
            $order['{date}'] = jdate("d-m-Y", $order['{date}']->getTimestamp());
        } else {
            // Use the standard date format
            $order['{date}'] = date("d-m-Y", $order['{date}']->getTimestamp());
        }

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
     * This function sends a POST request to the Telegram bot using the Pipedream endpoint, 
     * which processes and forwards the message to the designated Telegram chat.
     */
    public function send_message()
    {
        // Retrieve necessary values from the plugin settings
        $pipedream_endpoint = get_option('tgon_pipedream_endpoint', ''); // Pipedream endpoint URL
        $chat_id = get_option('tgon_chat_id', ''); // Telegram chat ID
        $api_token = get_option('tgon_api_token', ''); // Telegram bot API token

        // Ensure all required parameters are set before sending the message
        if (
            empty($pipedream_endpoint) or
            empty($chat_id) or
            empty($api_token) or
            empty($this->message_text)
        ) {
            return; // If any parameters are missing, do nothing
        }

        // Prepare the payload for the POST request
        $Payloads = [
            "msg" => $this->message_text,
            "api_token" => $api_token,
            "chat_id" => $chat_id,
        ];

        // Send the message to Telegram using the Pipedream endpoint
        $response = wp_remote_post($pipedream_endpoint, array(
            'method'      => 'POST',
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => json_encode($Payloads),
            'sslverify'   => false, // Bypass SSL verification (set to true in production)
        ));

        // Handle the response (error or success)
        if (is_wp_error($response)) {
            // If there was an error, you can log or handle the error message
            // $error_message = $response->get_error_message();
        } else {
            // If the request was successful, you can process the response if needed
            // $body = wp_remote_retrieve_body($response);
        }
    }
}
