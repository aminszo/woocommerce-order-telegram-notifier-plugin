<?php

/**
 * Plugin Name:  WooCommerce Telegram Order Notification
 * Plugin URI: https://github.com/aminszo/woocommerce-order-telegram-notifier-plugin/
 * Description: Sends WooCommerce order details to Telegram when a new order is placed.
 * Version: 1.0.0
 * Author: Amin SalehiZade
 * Author URI: https://aminlog.ir
 * Text Domain: wc-tgon
 * Domain Path: /lang
 */

defined('ABSPATH') || exit;

// load plugin translated strings
function tgon_load_textdomain()
{
    load_plugin_textdomain('wc-tgon', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', 'tgon_load_textdomain');

// load admin settings page
require_once "admin/settings.php";

require_once "includes/class-telegram-message.php";

function tgon_send_new_order_to_telegram($order_id)
{
    // Debug log to check if this function is firing
    // error_log("Telegram Notification: Order #$order_id processing.");

    $order = wc_get_order($order_id);

    $statuses = get_option('tgon_order_statuses', ['on-hold', 'processing']);

    if (empty($statuses))
        $statuses = [];

    if ( ! in_array($order->get_status(), $statuses) )
        return;

    $tg_message = new tgon_telegram_message($order);
    $tg_message->prepare_message();
    $tg_message->send_message();
}
// Hook into WooCommerce order status changes
add_action('woocommerce_order_status_on-hold', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_processing', 'tgon_send_new_order_to_telegram');
// add_action('woocommerce_thankyou', 'tgon_send_new_order_to_telegram');
