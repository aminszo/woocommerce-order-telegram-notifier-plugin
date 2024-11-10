<?php

class tgon_telegram_message
{
    public $order_id, $message_text;

    public function __construct($orderID)
    {
        $this->order_id = $orderID;
    }

    public function prepare_message()
    {
        // Get the message template
        $default_message_template = "Order {order_id} placed by {buyer_name} for a total of {total}.";
        $message_template = get_option('tgon_message_template', $default_message_template);

        // Get order object
        $order_obj = wc_get_order($this->order_id);

        require_once "jalali-date-v2.76.php";

        // Get order details
        $order['{date}'] = $order_obj->get_date_paid()->getTimestamp();
        $order['{divider}'] = "----------------------------------------------";

        $order = [
            '{order_id}'        => $order_obj->get_id(),
            '{order_date}'     => jdate("d-m-Y", $order['{date}']),
            '{items}'           => $order_obj->get_items(),
            '{buyer_name}'      => $order_obj->get_formatted_billing_full_name(),
            '{full_address}'    => $order_obj->get_shipping_state() . ". " . $order_obj->get_shipping_city() . ". " . $order_obj->get_shipping_address_1() . " . " . $order_obj->get_shipping_address_2(),
            '{postal_code}'     => $order_obj->get_shipping_postcode(),
            '{phone_number}'    => $order_obj->get_billing_phone(),
            '{shipping_method}' => $order_obj->get_shipping_method(),
            '{customer_note}'   => $order_obj->get_customer_note(),
            '{total}'           => $order_obj->get_total(),
            '{shipping_total}'  => $order_obj->get_shipping_total(),
        ];

        $order['{all_items}'] = "";
        foreach ($order['{items}'] as $item_id => $item) {
            $order['{all_items}'] .= $item->get_name() . " - x" . $item->get_quantity() . "\n";
        }

        // Replace placeholders in template with actual order data
        $this->message_text = str_replace(array_keys($order), array_values($order), $message_template);
    }

    // send order summary to telegram channel using bot api
    public function send_message()
    {

        // Retrieve values from db
        $pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
        $chat_id = get_option('tgon_chat_id', '');
        $api_token = get_option('tgon_api_token', '');

        if (
            empty($pipedream_endpoint) or
            empty($chat_id) or
            empty($api_token) or
            empty($this->message_text)
        ) {
            return; // do nothing and return.
            // wp_die(__('invalid parameters for sending order info to telegram', 'wc-tgon'));
        }

        $Payloads = [
            "msg" => $this->message_text,
            "api_token" => $api_token,
            "chat_id" => $chat_id,
        ];

        $response = wp_remote_post($pipedream_endpoint, array(
            'method'      => 'POST',
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => json_encode($Payloads),
            'sslverify'   => false, // bypass SSL check; set to true in production
        ));

        if (is_wp_error($response)) {
            // Handle error
            // $error_message = $response->get_error_message();
            // You can log or display the error as needed
        } else {
            // Success, process the response
            // $body = wp_remote_retrieve_body($response);
        }
    }
}
