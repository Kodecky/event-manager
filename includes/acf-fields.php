<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Rejestracja pól ACF dla eventów

add_action( 'acf/init', 'em_register_acf_fields' );

function em_register_acf_fields() {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key' => 'group_event_details',
        'title' => 'Event Details',
        'fields' => array(
            array(
                'key' => 'field_event_start_datetime',
                'label' => 'Event start date & time',
                'name' => 'event_start_datetime',
                'type' => 'date_time_picker',
                'required' => true,
                'wrapper' => array(
                  'width' => 25,
                ),
            ),
            array(
                'key' => 'field_event_participant_limit',
                'label' => 'Event participant limit',
                'name' => 'event_participant_limit',
                'type' => 'number',
                'required' => true,
                'min' => 1,
                'step' => 1,
                'wrapper' => array(
                  'width' => 25,
                ),
            ),
            array(
                'key' => 'field_event_description',
                'label' => 'Event description',
                'name' => 'event_description',
                'type' => 'wysiwyg',
                'required' => false,
                'wrapper' => array(
                  'width' => 50,
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'event',
                ),
            ),
        ),
    ) );
}
