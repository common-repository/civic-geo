<?php
/**
 *
 */
add_action( 'wp_enqueue_scripts', 'civic_lookup_scripts' );

function civic_lookup_scripts(){
	wp_enqueue_script( 'jquery' );
	// wp_enqueue_script('google_map_api', 'https://maps.googleapis.com/maps/api/js?key=' . get_option('congress_google_map_api_key') . '&libraries=places&callback=initAutocomplete', array('jquery'), false, true ) ;
}

add_action('wp_head', 'civic_lookup_head');


// frontend ajax call to get congress api data
if ( is_admin() ) {
	add_action( 'wp_ajax_congress_get_api_data', 'congress_get_api_data_callback' );
	add_action( 'wp_ajax_nopriv_congress_get_api_data', 'congress_get_api_data_callback' );
}
