<?php

// Hook to add admin menu
add_action('admin_menu', 'tgon_add_admin_menu');

// add a sub-menu in the wp admin menu
function tgon_add_admin_menu()
{
    add_submenu_page(
        'woocommerce', // Parent slug (WooCommerce)
        __('Telegram Notifications', 'wc-tgon'), // Page title
        __('Telegram Notifications', 'wc-tgon'), // Menu title
        'manage_options', // Capability
        'wc_telegram_notifications', // Menu slug
        'tgon_settings_page_callback' // Function to display the settings page
    );
}

// Display the settings page
function tgon_settings_page_callback()
{
    $admin = new Tgon_admin();
    $admin->render_settings_page();
}

class Tgon_admin
{
    public $input = [];

    // List of available placeholders for message template
    public $template_placeholders = [];

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

    public function retrieve_post_inputs($field_names)
    {
        foreach ($field_names as $field) {
            $this->input[$field] = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : null;
        }
    }


    public function render_settings_page()
    {
        // Check if the user is allowed to access this page
        if (!current_user_can('manage_options')) {
            return;
        }

        require_once 'setting_page.php';
    }
}
