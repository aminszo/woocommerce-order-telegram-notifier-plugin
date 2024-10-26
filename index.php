<?php
/*
Plugin Name: WooCommerce Telegram Order Notification
Description: Sends WooCommerce order details to Telegram when a new order is placed.
Version: 1.0
Author: Amin SalehiZade
*/

if (!defined('ABSPATH')) {
    exit;
}

// Hook into WooCommerce order processing
add_action('woocommerce_thankyou', 'send_order_to_telegram', 10, 1);

function send_order_to_telegram($order_id)
{
    if (!$order_id) return;

    // Get order details
    $order = wc_get_order($order_id);

    $order_id = $order->get_id();
    $date = $order->get_date_paid()->getTimestamp();
    $order_items = $order->get_items();
    $buyer = $order->get_formatted_billing_full_name();
    $full_address = $order->get_shipping_state() . " . " . $order->get_shipping_city() . " . " . $order->get_shipping_address_1() . " . " . $order->get_shipping_address_2();
    $postal_code = $order->get_shipping_postcode();
    $phone_number = $order->get_billing_phone();
    $shipping_method = $order->get_shipping_method();
    $customer_note = $order->get_customer_note();
    $total = $order->get_total();
    $shipping_total = $order->get_shipping_total();

    $all_items = "";
    foreach ($order_items as $item_id => $item) {
        $all_items .= $item->get_name() . " - x" . $item->get_quantity() . "\n";
    }

    $message_text = "
        سفارش $order_id
        تاریخ : $date\n
        کالا ها :
        $all_items
        مجموع پرداختی : $total تومان
        ----------------------------------------------
        خریدار : $buyer
        آدرس : $full_address \n
        کد پستی : $postal_code
        تلفن : $phone_number \n
        پست : $shipping_method - $shipping_total تومان \n
    ";

    if (strlen($customer_note) > 0) {
        $message_text .= "یادداشت مشتری : $customer_note \n";
    }

    $message_text .= "----------------------------------------------\n \u{1F7E2}";
}


// send order summary to telegram channel using bot api
function send_telegram_message($message)
{
    $pipedream_endpoint = "https://eo7yqs3zurbk8iy.m.pipedream.net";

    $Payloads = [
        "msg" => $message,
    ];

    $handler = curl_init();
    $curl_options = array(
        CURLOPT_URL => $pipedream_endpoint,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
        CURLOPT_POSTFIELDS => json_encode($Payloads),
        CURLOPT_RETURNTRANSFER => true,
    );
    curl_setopt_array($handler, $curl_options);
    curl_exec($handler);
}
