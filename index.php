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
    $order_data = $order->get_data();

    $items = $order->get_items();
    $product_list = '';
    foreach ($items as $item) {
        $product_list .= $item->get_name() . ' - Quantity: ' . $item->get_quantity() . "\n";
    }

    // Prepare message to send
    $message = "New Order Received!\n";
    $message .= "Order ID: " . $order_id . "\n";
    $message .= "Customer: " . $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'] . "\n";
    $message .= "Email: " . $order_data['billing']['email'] . "\n";
    $message .= "Total: " . $order_data['total'] . "\n";
    $message .= "Products: \n" . $product_list;

    // Send message to Telegram
    send_telegram_message($message);
}

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
