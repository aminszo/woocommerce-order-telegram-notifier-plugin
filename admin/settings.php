<?php

// Hook to add admin menu in the WooCommerce admin panel
add_action('admin_menu', 'tgon_add_admin_menu');

/**
 * Adds a sub-menu under WooCommerce for the Telegram Notifications settings page.
 */
function tgon_add_admin_menu()
{
    // Add a submenu to the WooCommerce menu
    add_submenu_page(
        'woocommerce', // Parent slug (WooCommerce)
        __('Telegram Notifications', 'telegram-order-notification'), // Page title
        __('Telegram Notifications', 'telegram-order-notification'), // Menu title
        'manage_options', // Required capability to access the page
        'wc-telegram-order-notifications', // Menu slug
        'tgon_settings_page_callback', // Function to display the settings page
    );
}

/**
 * Callback function to display the settings page for Telegram notifications.
 */
function tgon_settings_page_callback()
{
    // Create an instance of the Tgon_admin class and render the settings page
    $admin = new Tgon_admin();
    $admin->render_settings_page();
}

/**
 * Tgon_admin class responsible for managing the settings page and handling user inputs.
 */
class Tgon_admin
{
    public $input = []; // Holds the user inputs from the settings page
    public $error_messages = []; // Holds any validation error messages

    // List of available placeholders for message template
    public $template_placeholders = [];

    /**
     * Tgon_admin constructor initializes the available placeholders for the message template, to show as a hint in the admin page.
     */
    public function __construct()
    {
        $this->template_placeholders =
            [
                '{status}'        => __('The status of the order', 'telegram-order-notification'),
                '{id}'        => __('The unique ID of the order', 'telegram-order-notification'),
                '{date}'      => __('The date the order was placed', 'telegram-order-notification'),
                '{buyer_name}'      => __('The buyer\'s full name', 'telegram-order-notification'),
                '{full_address}'    => __('The full shipping address', 'telegram-order-notification'),
                '{postal_code}'     => __('The postal code for shipping', 'telegram-order-notification'),
                '{phone_number}'    => __('The buyer\'s phone number', 'telegram-order-notification'),
                '{payment_method}'    => __('The payment method used to pay this order', 'telegram-order-notification'),
                '{shipping_method}' => __('The shipping method chosen', 'telegram-order-notification'),
                '{customer_note}'   => __('Any note added by the customer', 'telegram-order-notification'),
                '{total}'           => __('The total payment amount', 'telegram-order-notification'),
                '{shipping_total}'  => __('The total shipping cost', 'telegram-order-notification'),
                '{items}'           => __('A list of items ordered', 'telegram-order-notification'),
                '{divider}'         => __('A divider line (multiple dashes) for message readability', 'telegram-order-notification'),
            ];
    }

    /**
     * Retrieves and sanitizes the user inputs from the POST request on the settings page.
     */
    public function retrieve_post_inputs($field_names)
    {

        if (!check_admin_referer('tgon_save_settings_action', 'tgon_save_settings_nonce')) {
            wp_die(esc_html__('Security check failed!', 'telegram-order-notification'));
        }
        // Loop through the list of field names and retrieve their values from the POST request
        foreach ($field_names as $field) {
            $this->input[$field] = isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : null;
        }

        // Retrieve the message template and sanitize it
        $this->input['message_template'] = isset($_POST['message_template']) ? sanitize_textarea_field(wp_unslash($_POST['message_template'])) : null;

        // Retrieve the selected order statuses and enable Persian date flag
        $this->input['acceptable_order_statuses'] = isset($_POST['acceptable_order_statuses']) ? array_map('sanitize_text_field', wp_unslash($_POST['acceptable_order_statuses'])) : [];

        $this->input['use_middleman'] = isset($_POST['use_middleman']) ? 1 : 0;
    }

    /**
     * Validates the inputs provided by the user on the settings page.
     * Currently, it checks if the middleman endpoint URL is valid.
     */
    public function validate_inputs()
    {
        // Check if the middleman endpoint URL is valid. check the URL vlaue only if use_middleman is checked. otherwise we do not update the endpoint url.
        if ($this->input['use_middleman'] === 1 and !filter_var($this->input['middleman_endpoint'], FILTER_VALIDATE_URL)) {
            // Set error message if the URL is invalid
            $this->error_messages[] = __('Invalid URL for the middleman endpoint. Please enter a valid URL.', 'telegram-order-notification');
        }

        // Check if the selected order status options are valid
        $safe_list = ['on-hold', 'processing', 'cancelled', 'failed'];
        if (!empty(array_diff($this->input['acceptable_order_statuses'], $safe_list))) {
            $this->error_messages[] = __('Invalid options for order statuses.', 'telegram-order-notification');
        }
    }


    /**
     * Renders the settings page HTML for the plugin.
     * This page allows the admin to configure the Telegram notifications.
     */
    public function render_settings_page()
    {
        // Check if the user has permission to manage options (access the settings page)
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include the settings page template file (setting_page.php)
        require_once 'setting_page.php';
    }
}
