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
        __('Telegram Notifications', 'wc-tgon'), // Page title
        __('Telegram Notifications', 'wc-tgon'), // Menu title
        'manage_options', // Required capability to access the page
        'wc_telegram_notifications', // Menu slug
        'tgon_settings_page_callback' // Function to display the settings page
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
    public $error_message = ''; // Holds any validation error messages

    // List of available placeholders for message template
    public $template_placeholders = [];

    /**
     * Tgon_admin constructor initializes the available placeholders for the message template, to show as a hint in the admin page.
     */
    public function __construct()
    {
        $this->template_placeholders =
            [
                '{order_id}'        => __('The unique ID of the order', 'wc-tgon'),
                '{order_date}'      => __('The date the order was placed', 'wc-tgon'),
                '{buyer_name}'      => __('The buyer\'s full name', 'wc-tgon'),
                '{full_address}'    => __('The full shipping address', 'wc-tgon'),
                '{postal_code}'     => __('The postal code for shipping', 'wc-tgon'),
                '{phone_number}'    => __('The buyer\'s phone number', 'wc-tgon'),
                '{shipping_method}' => __('The shipping method chosen', 'wc-tgon'),
                '{customer_note}'   => __('Any note added by the customer', 'wc-tgon'),
                '{total}'           => __('The total payment amount', 'wc-tgon'),
                '{shipping_total}'  => __('The total shipping cost', 'wc-tgon'),
                '{items}'           => __('A list of items ordered', 'wc-tgon'),
            ];
    }

    /**
     * Retrieves and sanitizes the user inputs from the POST request on the settings page.
     */
    public function retrieve_post_inputs($field_names)
    {
        // Loop through the list of field names and retrieve their values from the POST request
        foreach ($field_names as $field) {
            $this->input[$field] = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : null;
        }

        // Retrieve the message template and sanitize it
        $this->input['message_template'] = isset($_POST['message_template']) ? htmlspecialchars($_POST['message_template']) : null;

        // Retrieve the selected order statuses and enable Persian date flag
        $this->input['order_statuses'] = isset($_POST['order_statuses']) ? $_POST['order_statuses'] : null;
        $this->input['enable_persian_date'] = isset($_POST['enable_persian_date']) ? $_POST['enable_persian_date'] : null;
    }

    /**
     * Validates the inputs provided by the user on the settings page.
     * Currently, it checks if the Pipedream endpoint URL is valid.
     */
    public function validate_inputs()
    {
        // Check if the Pipedream endpoint URL is valid
        if (!filter_var($this->input['pipedream_endpoint'], FILTER_VALIDATE_URL)) {
            // Set error message if the URL is invalid
            $this->error_message = __('Invalid URL for the Pipedream endpoint. Please enter a valid URL.', 'wc-tgon');
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
