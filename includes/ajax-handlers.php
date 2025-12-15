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

add_action('wp_ajax_em_search_events', 'em_ajax_search_events');
add_action('wp_ajax_nopriv_em_search_events', 'em_ajax_search_events');

/**
 * Rejestracja uczestnika w evencie.
 * Dane są zapisywane do post_meta: event_registrations (tablica wpisów).
 */
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

/**
 * Publiczna wyszukiwarka eventów (miasto + zakres dat).
 * Zwraca HTML gotowy do wstawienia w results container.
 */
function em_ajax_search_events() {
    // Nonce
    if ( ! check_ajax_referer( 'em_register_event', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
    }

    // Filtry z formularza.
    $city      = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to   = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

    // Tax query (miasto po slugu).
    $tax_query = [];
    if ($city !== '') {
        $tax_query[] = [
            'taxonomy' => 'city',
            'field'    => 'slug',
            'terms'    => $city,
        ];
    }

    // Meta query dla daty startu eventu.
    $meta_query = [];
    if ($date_from || $date_to) {
        $from = $date_from ? $date_from . ' 00:00:00' : null;
        $to   = $date_to   ? $date_to   . ' 23:59:59' : null;

        $range = [
            'key'     => 'event_start_datetime',
            'compare' => 'BETWEEN',
            'type'    => 'DATETIME',
            'value'   => [$from ?: '0000-00-00 00:00:00', $to ?: '9999-12-31 23:59:59'],
        ];

        $meta_query[] = $range;
    }

    // Query eventów.
    $args = [
        'post_type'      => 'event',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'orderby'        => 'meta_value',
        'meta_key'       => 'event_start_datetime',
        'order'          => 'ASC',
    ];

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    $q = new WP_Query($args);

    ob_start();

    if ($q->have_posts()) {
        echo '<ul class="em-results">';
        while ($q->have_posts()) {
            $q->the_post();
            $event_id = get_the_ID();

            $dt = get_field('event_start_datetime', $event_id);
            echo '<li class="em-result-item">';
            echo '<a href="' . esc_url(get_permalink($event_id)) . '"><strong>' . esc_html(get_the_title()) . '</strong></a>';
            if ($dt) {
                echo '<div class="em-meta">' . esc_html($dt) . '</div>';
            }
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p>No events found.</p>';
    }

    // Resetujemy globalny $post po zakończeniu WP_Query.
    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success(
        array(
            'html' => $html,
        )
    );
}
