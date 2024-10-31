<?php

/**
 * Plugin Name:  WooCommerce Telegram Order Notification
 * Plugin URI: https://github.com/aminszo/woocommerce-order-telegram-notifier-plugin/
 * Description: Sends WooCommerce order details to Telegram when a new order is placed.
 * Version: 0.1
 * Author: Amin SalehiZade
 * Author URI: https://aminlog.ir
 * Text Domain: wc-tgon
 * Domain Path: /lang
 */

defined('ABSPATH') || exit;

// === Plugin Initialization ===

// Add admin settings page

function tgon_load_textdomain()
{
    load_plugin_textdomain('wc-tgon', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', 'tgon_load_textdomain');

require_once "admin/settings.php";

// Hook into WooCommerce order processing
add_action('woocommerce_thankyou', 'tgon_send_new_order_to_telegram');

// === END of Plugin init ===


function tgon_send_new_order_to_telegram($order_id)
{

    require_once "includes/jalali-date-v2.76.php";
    $message = tgon_prepare_message($order_id);

    // if $message is false, means there is an error.
    if (!$message)
        return;

    tgon_send_telegram_message($message);
}

function tgon_prepare_message($order_id)
{
    if (!$order_id) return false;

    // Get the message template
    $template = get_option('tgon_message_template', "Order {order_id} placed by {buyer_name} for a total of {total}.");

    // Get order details
    $order_obj = wc_get_order($order_id);

    $order['{date}'] = $order_obj->get_date_paid()->getTimestamp();
    $order['{divider}'] = "----------------------------------------------";

    $order = [
        '{order_id}'        => $order_obj->get_id(),
        '{jalali_date}'     => jdate("d-m-Y", $order['{date}']),
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
    $message_text = str_replace(array_keys($order), array_values($order), $template);

    return $message_text;
}

// send order summary to telegram channel using bot api
function tgon_send_telegram_message($message)
{

    // Retrieve values from db
    $pipedream_endpoint = get_option('tgon_pipedream_endpoint', '');
    $chat_id = get_option('tgon_chat_id', '');
    $api_token = get_option('tgon_api_token', '');

    $Payloads = [
        "msg" => $message,
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
        $error_message = $response->get_error_message();
        // You can log or display the error as needed
    } else {
        // Success, process the response
        $body = wp_remote_retrieve_body($response);
        // Further processing of $body if needed
    }
}
