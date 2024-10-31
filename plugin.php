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

// load plugin translated strings
function tgon_load_textdomain()
{
    load_plugin_textdomain('wc-tgon', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', 'tgon_load_textdomain');

// Add admin settings page
require_once "admin/settings.php";
require_once "includes/class-telegram-message.php";


function tgon_send_new_order_to_telegram($order_id)
{
    $tg_message = new tgon_telegram_message($order_id);
    $tg_message->prepare_message();
    $tg_message->send_message();
}
// Hook into WooCommerce order processing
add_action('woocommerce_thankyou', 'tgon_send_new_order_to_telegram');
