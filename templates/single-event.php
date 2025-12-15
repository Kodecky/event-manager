<?php
/**
 * Template: single event
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) : the_post();

    $event_id   = get_the_ID();
    $start      = get_field( 'event_start_datetime', $event_id );
    $limit      = (int) get_field( 'event_participant_limit', $event_id );
    $desc       = get_field( 'event_description', $event_id );

    $cities     = get_the_terms( $event_id, 'city' );

    // Rejestracje trzymamy w post_meta jako tablicę pod kluczem 'event_registrations'
    $registrations = get_post_meta( $event_id, 'event_registrations', true );
    if ( ! is_array( $registrations ) ) {
        $registrations = array();
    }
    $current_count = count( $registrations );
    $spots_left    = $limit ? max( $limit - $current_count, 0 ) : null;
    $is_full       = ( $limit && $current_count >= $limit );

    ?>
    <div class="em-single-event">

        <h1 class="em-title"><?php echo esc_html( get_the_title() ); ?></h1>

        <p class="em-meta">
            <?php if ( $start ) : ?>
                <strong>Date & time:</strong>
                <?php echo esc_html( $start ); ?><br>
            <?php endif; ?>

            <?php if ( $cities && ! is_wp_error( $cities ) ) : ?>
                <strong>City:</strong>
                <?php
                $city_names = wp_list_pluck( $cities, 'name' );
                echo esc_html( implode( ', ', $city_names ) );
                ?><br>
            <?php endif; ?>

            <?php if ( $limit ) : ?>
                <strong>Participants:</strong>
                <span class="em-current-count"><?php echo esc_html( $current_count ); ?></span>
                /
                <?php echo esc_html( $limit ); ?>
                <?php if ( ! $is_full ) : ?>
                    (<span class="em-spots-left"><?php echo esc_html( $spots_left ); ?></span> spots left)
                <?php else : ?>
                    (Event is full)
                <?php endif; ?>
            <?php endif; ?>
        </p>

        <?php 
        // Opcjonalnie: renderowanie treści z bloku Gutenberga zamiast description ACF
        ?>

        <?php if ( $desc ) : ?>
            <div class="em-description">
                <?php echo wp_kses_post( $desc ); ?>
            </div>
        <?php endif; ?>

        <hr>

        <h2 class="text-lg font-bold my-4">Register for this event</h2>

        <?php if ( $is_full ) : ?>

            <p>This event is full. Registration is closed.</p>

        <?php elseif ( ! is_user_logged_in() ) : ?>

            <p>
                You must be logged in to register.
                <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="text-blue-500 hover:text-blue-700 underline pl-2">Log in</a>
            </p>

        <?php else : ?>

            <form id="em-event-register-form" class="em-form" data-event-id="<?php echo esc_attr( $event_id ); ?>">

                <p>
                    <label for="em-name">Name *</label>
                    <input type="text" id="em-name" name="name" required>
                </p>

                <p>
                    <label for="em-email">Email *</label>
                    <input type="email" id="em-email" name="email" required>
                </p>

                <button type="submit">Register</button>

                <p class="em-message" aria-live="polite"></p>

            </form>

        <?php endif; ?>

    </div>

    <?php
endwhile;

get_footer();
