<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rejestracja hooków dla AJAX
 */
// Dla zalogowanych użytkowników
add_action( 'wp_ajax_register_event', 'em_handle_event_registration' );
// Celowo rejestrujemy nopriv, aby niezalogowani dostawali JSON (401) zamiast gołego "0".
add_action( 'wp_ajax_nopriv_register_event', 'em_handle_event_registration' );

function em_handle_event_registration() {

    // Nonce: ochrona przed CSRF (weryfikacja, że request pochodzi z naszej strony)
    if ( ! check_ajax_referer( 'em_register_event', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
    }

    // Permissions: zezwolenie na rejestrację tylko zalogowanym użytkownikom
    if ( ! is_user_logged_in() ) {
        wp_send_json_error(
            array( 'message' => 'You must be logged in to register for this event.' ),
            401
        );
    }
    
    
    // Pobranie i sanitizacja danych
    $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
    $name     = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

    if ( ! $event_id || empty( $name ) || empty( $email ) ) {
        wp_send_json_error( array(
            'message' => 'Please fill in all required fields.',
        ), 422 );
    }

    // Walidacja maila
    if ( ! is_email( $email ) ) {
        wp_send_json_error( array(
            'message' => 'Please provide a valid email address.',
        ), 422 );
    }

    // Sprawdzenie czy event istnieje i jest typu event
    if ( 'event' !== get_post_type( $event_id ) ) {
        wp_send_json_error(
            array( 'message' => 'Event not found.' ),
            404
        );
    }
    
    // Pobranie limitu i aktualnych rejestracji
    $limit         = (int) get_field( 'event_participant_limit', $event_id );
    $registrations = get_post_meta( $event_id, 'event_registrations', true );

    if ( ! is_array( $registrations ) ) {
        $registrations = array();
    }

    // Sprawdzenie czy event jest pełny
    $current_count = count( $registrations );

    if ( $limit && $current_count >= $limit ) {
        wp_send_json_error( array(
            'message' => 'This event is full.',
        ), 409 );
    }

    // Sprawdzenie czy email jest już zapisany
    $email_normalized = strtolower( $email );
    foreach ( $registrations as $registration ) {
        if ( isset( $registration['email'] ) && strtolower( $registration['email'] ) === $email_normalized ) {
            wp_send_json_error( array(
                'message' => 'This email is already registered for this event.',
            ), 409 );
        }
    }

    // Zapis nowej rejestracji
    $registrations[] = array(
        'name'  => $name,
        'email' => $email,
        'date'  => current_time( 'mysql' ),
    );

    $updated = update_post_meta( $event_id, 'event_registrations', $registrations );

    if ( false === $updated ) {
        wp_send_json_error(
            array( 'message' => 'Could not save registration.' ),
            500
        );
    }


    // Odpowiedź sukces
    wp_send_json_success( array(
        'message'        => 'Successfully registered!',
        'current_count'  => count( $registrations ),
        'limit'          => $limit,
    ) );
}
