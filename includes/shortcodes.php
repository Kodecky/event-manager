<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode: [em_event_search]
 * Renderuje formularz wyszukiwarki eventów + kontener z wynikami AJAX.
 */
add_shortcode( 'em_event_search', 'em_render_event_search' );

function em_render_event_search() {
    // Unikalne ID, żeby shortcode mógł być użyty wiele razy na jednej stronie.
    $uid       = wp_unique_id( 'em-search-' );
    $form_id   = $uid . '-form';
    $results_id = $uid . '-results';

    $cities = get_terms( array(
        'taxonomy'   => 'city',
        'hide_empty' => false,
    ) );

    // Zabezpieczenie przed błędem WP_Error.
    if ( is_wp_error( $cities ) ) {
        $cities = array();
    }

    ob_start(); ?>
    <div class="em-search" data-em-search>
        <form id="<?php echo esc_attr( $form_id ); ?>" class="em-event-search-form" novalidate>
            <div class="em-search-row">
                <label>
                    City
                    <select name="city">
                        <option value="">All cities</option>
                        <?php foreach ( $cities as $city ) : ?>
                            <option value="<?php echo esc_attr( $city->slug ); ?>">
                                <?php echo esc_html( $city->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Date from
                    <input type="date" name="date_from">
                </label>

                <label>
                    Date to
                    <input type="date" name="date_to">
                </label>

                <button type="submit">Search</button>
            </div>
        </form>

        <div id="<?php echo esc_attr( $results_id ); ?>" class="em-event-search-results" role="status" aria-live="polite"></div>
    </div>
    <?php
    return ob_get_clean();
}
