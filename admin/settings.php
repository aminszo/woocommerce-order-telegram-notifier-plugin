<?php

// Hook to add admin menu
add_action('admin_menu', 'tgon_add_admin_menu');

// add a sub-menu in the wp admin menu
function tgon_add_admin_menu()
{
    add_submenu_page(
        'woocommerce', // Parent slug (WooCommerce)
        __('Telegram Notifications'), // Page title
        __('Telegram Notifications'), // Menu title
        'manage_options', // Capability
        'wc_telegram_notifications', // Menu slug
        'tgon_settings_page' // Function to display the settings page
    );
}

// Display the settings page
function tgon_settings_page()
{
    $admin = new Tgon_admin();
    $admin->render_settings_page();
}

class Tgon_admin
{
    public $input = [];

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
