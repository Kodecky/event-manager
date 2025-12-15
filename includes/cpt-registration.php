<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rejestracja Custom Post Type "event".
 */
add_action( 'init', 'em_register_event_cpt' );

function em_register_event_cpt() {

    $labels = array(
        'name'               => __( 'Events', 'event-manager' ),
        'singular_name'      => __( 'Event', 'event-manager' ),
        'add_new'            => __( 'Add New Event', 'event-manager' ),
        'add_new_item'       => __( 'Add New Event', 'event-manager' ),
        'edit_item'          => __( 'Edit Event', 'event-manager' ),
        'new_item'           => __( 'New Event', 'event-manager' ),
        'view_item'          => __( 'View Event', 'event-manager' ),
        'search_items'       => __( 'Search Events', 'event-manager' ),
        'not_found'          => __( 'No events found', 'event-manager' ),
        'not_found_in_trash' => __( 'No events found in Trash', 'event-manager' ),
        'menu_name'          => __( 'Events', 'event-manager' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'exclude_from_search'=> false,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'events' ),
        // Editor pozostawiony świadomie – może być używany obok ACF.
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'event', $args );
}

/**
 * Rejestracja taxonomii "city" przypisanej do CPT event.
 */
add_action( 'init', 'em_register_city_taxonomy' );

function em_register_city_taxonomy() {

    $labels = array(
        'name'          => __( 'Cities', 'event-manager' ),
        'singular_name' => __( 'City', 'event-manager' ),
        'search_items'  => __( 'Search Cities', 'event-manager' ),
        'all_items'     => __( 'All Cities', 'event-manager' ),
        'edit_item'     => __( 'Edit City', 'event-manager' ),
        'update_item'   => __( 'Update City', 'event-manager' ),
        'add_new_item'  => __( 'Add New City', 'event-manager' ),
        'new_item_name' => __( 'New City Name', 'event-manager' ),
        'menu_name'     => __( 'Cities', 'event-manager' ),
    );

    $args = array(
        // hierarchical => true użyte celowo – zapewnia checkboxowy UI,
        // który jest czytelniejszy w edycji eventu niż UI tagów.
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'city' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'city', array( 'event' ), $args );
}
