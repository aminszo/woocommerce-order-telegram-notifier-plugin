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

defined('ABSPATH') || exit; // Ensures the file is not accessed directly

/**
 * Loads the plugin's text domain for translations
 *
 * This function is hooked to 'plugins_loaded' to ensure translations are available after WordPress is initialized.
 */
function tgon_load_textdomain()
{
    // Load the plugin's language files from the '/lang' directory
    load_plugin_textdomain('wc-tgon', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', 'tgon_load_textdomain');

require_once "admin/settings.php"; // Loads the settings page for the plugin in the admin panel
require_once "includes/class-telegram-message.php"; // Loads the class responsible for preparing and sending Telegram messages

/**
 * Sends order details to Telegram when an order's status matches the selected statuses.
 *
 * @param int $order_id The WooCommerce order ID
 */
function tgon_send_new_order_to_telegram($order_id)
{
    // Debug log to check if this function is firing
    // error_log("Telegram Notification: Order #$order_id");

    // Get the WooCommerce order object using the provided order ID
    $order = wc_get_order($order_id);

    // Retrieve selected order statuses from the plugin settings (defaults to 'on-hold' and 'processing')
    $default_acceptable_statuses = ['on-hold', 'processing', 'cancelled', 'failed'];

    $statuses = get_option('tgon_order_statuses', $default_acceptable_statuses);

    // If no statuses are set, use an empty array to prevent errors
    empty($statuses) && $statuses = [];

    // Check if the order's current status is in the list of selected statuses
    if (! in_array($order->get_status(), $statuses))
        return; // If not, exit the function without sending a Telegram message

    // Create a new instance of the Telegram message handler class
    $tg_message = new tgon_telegram_message($order);

    // Prepare the message by formatting the order details
    $tg_message->prepare_message();

    // Send the formatted message to the Telegram channel
    $tg_message->send_message();
}
// Hook into WooCommerce order status changes (on-hold, processing, cancelled, failed)
add_action('woocommerce_order_status_on-hold', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_processing', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_cancelled', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_failed', 'tgon_send_new_order_to_telegram');
// You could add additional status hooks here, such as 'completed', 'pending payment', etc.

// Uncomment the following line if you want to trigger the message on the Thank You page as well (for easy debugging and development)
// add_action('woocommerce_thankyou', 'tgon_send_new_order_to_telegram'); // Trigger message on 'thankyou' page (this can be problematic if the page is reloaded)
