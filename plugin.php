<?php

/**
 * Plugin Name:        Telegram Order Notification
 * Plugin URI:         https://github.com/aminszo/woocommerce-order-telegram-notifier-plugin/
 * Description:        Sends WooCommerce order details to Telegram when a new order is placed.
 * Version:            1.0.0
 * Author:             Amin SalehiZade
 * Author URI:         https://aminlog.ir
 * Text Domain:        telegram-order-notification
 * Domain Path:        /languages
 * License:            GPLv3
 * License URI:        https://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') || exit; // Ensures the file is not accessed directly

// Define plugin constants
define('TGON_VERSION', '1.0.0');
define('TGON_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGON_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Loads the plugin's text domain for translations
 */
function tgon_load_textdomain()
{
    load_plugin_textdomain('telegram-order-notification', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'tgon_load_textdomain');


require_once "admin/settings.php"; // Loads the admin panel settings page for the plugin
require_once "includes/class-telegram-message.php"; // Loads the class responsible for handling Telegram messages
require_once "includes/ajax-handler.php";

/**
 * Sends order details to Telegram when an order's status matches the selected statuses.
 *
 * @param int $order_id The WooCommerce order ID
 */
function tgon_send_new_order_to_telegram($order_id)
{
    // Get the WooCommerce order object using the provided order ID
    $order = wc_get_order($order_id);

    $default_acceptable_statuses = ['on-hold', 'processing', 'cancelled', 'failed'];

    // Retrieve selected order statuses from the plugin settings
    $acceptable_statuses = get_option('tgon_order_statuses', $default_acceptable_statuses);

    // If no statuses are set (the value is an empty string), use an empty array to prevent errors
    empty($acceptable_statuses) && $acceptable_statuses = [];

    // Check if the order's current status is in the list of selected statuses
    if (! in_array($order->get_status(), $acceptable_statuses))
        return; // If not, exit the function without sending a Telegram message

    // Create a new instance of the Telegram message handler class
    $tg_message = new tgon_telegram_message($order);

    // Prepare the message by reading and formatting the order details
    $tg_message->prepare_message();

    // Send the formatted message to the Telegram channel
    $tg_message->send_message($tg_message->message_text);
}
// Hook into WooCommerce order status changes (on-hold, processing, cancelled, failed)
add_action('woocommerce_order_status_on-hold', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_processing', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_cancelled', 'tgon_send_new_order_to_telegram');
add_action('woocommerce_order_status_failed', 'tgon_send_new_order_to_telegram');
// You could add additional status hooks here, such as 'completed', 'pending payment', etc.

// Uncomment the following line if you want to trigger the message on the Thank You page as well (for easy debugging and development)
add_action('woocommerce_thankyou', 'tgon_send_new_order_to_telegram'); // Trigger message on 'thankyou' page (this can be problematic if the page is reloaded)


/**
 * Enqueue admin asset files in admin page
 */
function tgon_enqueue_admin_assets($hook_suffix)
{
    // Load admin assets only on plugin's admin pages
    if (strpos($hook_suffix, 'wc-telegram-order-notifications') === false) return;

    wp_enqueue_script(
        'tgon-admin-script',
        plugin_dir_url(__FILE__) . 'js/my-script.js',
        array('jquery'),
        null,
        true
    );

    wp_localize_script('tgon-admin-script', 'pluginData', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'tgon_enqueue_admin_assets');
