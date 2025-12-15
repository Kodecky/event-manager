<?php
/**
 * Plugin Name: Event Manager
 * Description: Wtyczka stworzona do zarządzania wydarzeniami.
 * Version: 1.0.0
 * Author: Damian Pawela
 * Text Domain: event-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Inicjalizacja pluginu.
 */
add_action( 'plugins_loaded', 'em_init_plugin' );

function em_init_plugin() {
    // Sprawdzenie czy ACF jest aktywny
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        add_action( 'admin_notices', 'em_acf_missing_notice' );
        return; // nie ładujemy reszty pluginu jeśli ACF nie jest aktywny
    }

    require_once EM_PLUGIN_DIR . 'includes/cpt-registration.php';
    require_once EM_PLUGIN_DIR . 'includes/acf-fields.php';
    require_once EM_PLUGIN_DIR . 'includes/ajax-handlers.php';
    require_once EM_PLUGIN_DIR . 'includes/shortcodes.php';

    add_action( 'wp_enqueue_scripts', 'em_enqueue_assets' );
    add_filter( 'single_template', 'em_single_event_template' );
}

/**
 * Wyświetlenie komunikatu o braku ACF.
 */
function em_acf_missing_notice() {
    echo '<div class="notice notice-error"><p><strong>' .
        esc_html__( 'Event Manager', 'event-manager' ) .
        '</strong> ' .
        esc_html__( 'requires Advanced Custom Fields (ACF) plugin to be installed and active.', 'event-manager' ) .
        '</p></div>';
}

/**
 * Aktualizacja reguł rewrite podczas aktywacji wtyczki.
 */

// Aktywacja wtyczki
register_activation_hook( __FILE__, 'em_activate_plugin' );

function em_activate_plugin() {

  require_once EM_PLUGIN_DIR . 'includes/cpt-registration.php';

  // Rejestracja CPT i taxonomii.
  em_register_event_cpt();
  em_register_city_taxonomy();
  // Aktualizacja reguł rewrite.
  flush_rewrite_rules();
}

// Dezaktywacja wtyczki
register_deactivation_hook( __FILE__, 'em_deactivate_plugin' );

function em_deactivate_plugin() {
    flush_rewrite_rules();
}


//  Rejestracja assetów CSS/JS
function em_enqueue_assets() {
  $css_rel = 'assets/build/css/style.css';
  $js_rel  = 'assets/build/js/event-register.js';
  $js_search_rel = 'assets/build/js/event-search.js';

  $css_path = EM_PLUGIN_DIR . $css_rel;
  $js_path  = EM_PLUGIN_DIR . $js_rel;
  $js_search_path = EM_PLUGIN_DIR . $js_search_rel;

  wp_enqueue_style(
      'em-styles',
      EM_PLUGIN_URL . $css_rel,
      array(),
      file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
  );

  wp_enqueue_script(
      'em-register',
      EM_PLUGIN_URL . $js_rel,
      array(),
      file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
      true
  );

  wp_enqueue_script(
    'em-search',
    EM_PLUGIN_URL . $js_search_rel,
    array(),
    file_exists( $js_search_path ) ? filemtime( $js_search_path ) : '1.0.0',
    true
  );

  // Przekazywanie danych do JavaScript.
  wp_localize_script( 'em-register', 'EM_AJAX', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce'    => wp_create_nonce( 'em_register_event' ),
  ) );

}

// Podpięcie single event template dla CPT event
function em_single_event_template( $single ) {

    global $post;

    if ( $post && $post->post_type === 'event' ) {
        $template = EM_PLUGIN_DIR . 'templates/single-event.php';
        if ( file_exists( $template ) ) {
            return $template;
        }
    }

    return $single;
}