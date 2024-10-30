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

function tgon_load_textdomain() {
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

    // Get order details

    $order_obj = wc_get_order($order_id);
    $order = [];

    $order['id'] = $order_obj->get_id();
    $order['date'] = $order_obj->get_date_paid()->getTimestamp();
    $order['jalali_date'] = jdate("d-m-Y", $order['date']);
    $order['items'] = $order_obj->get_items();
    $order['buyer'] = $order_obj->get_formatted_billing_full_name();
    $order['full_address'] = $order_obj->get_shipping_state() . ". " . $order_obj->get_shipping_city() . ". " . $order_obj->get_shipping_address_1() . " . " . $order_obj->get_shipping_address_2();
    $order['postal_code'] = $order_obj->get_shipping_postcode();
    $order['phone_number'] = $order_obj->get_billing_phone();
    $order['shipping_method'] = $order_obj->get_shipping_method();
    $order['customer_note'] = $order_obj->get_customer_note();
    $order['total'] = $order_obj->get_total();
    $order['shipping_total'] = $order_obj->get_shipping_total();

    $order['all_items'] = "";
    foreach ($order['items'] as $item_id => $item) {
        $order['all_items'] .= $item->get_name() . " - x" . $item->get_quantity() . "\n";
    }

    $divider = "----------------------------------------------";

    // a custom emoji in uft8 format
    $new_message_symbol = "\u{1F7E2}";

    $message_text = "
        سفارش {$order['id']}
        تاریخ : {$order['jalali_date']}\n
        کالا ها :
        {$order['all_items']}
        مجموع پرداختی : {$order['total']} تومان
        {$divider}
        خریدار : {$order['buyer']}
        آدرس : {$order['full_address']} \n
        کد پستی : {$order['postal_code']}
        تلفن : {$order['phone_number']} \n
        پست : {$order['shipping_method']} - {$order['shipping_total']} تومان \n
    ";

    strlen($order['customer_note']) < 0 ?: $message_text .= "یادداشت مشتری : {$order['customer_note']} \n";

    $message_text .= "$divider \n $new_message_symbol";

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
