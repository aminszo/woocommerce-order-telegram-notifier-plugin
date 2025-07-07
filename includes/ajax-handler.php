<?php

add_action( 'wp_ajax_my_custom_action', 'handle_my_custom_action' );

function handle_my_custom_action() {

    tgon_telegram_message::test_message("Test from telegram-order-notification plugin");

    $response = array(
        'message' => 'Test message sent.',
    );

    // Return a JSON response
    wp_send_json_success( $response );

    wp_die();
}
