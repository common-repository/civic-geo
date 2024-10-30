<?php
/**
* http://localhost:8099/wp-admin/options-general.php?page=mt-cglu
*/
use CivicLookup\CivicGeoLookup as CivicGeoLookup;
use CivicLookup\Person as Person;

add_shortcode('civic-lookup', 'civic_lookup_show');
add_shortcode('civic-lookup-person-form', 'civic_lookup_person_form');
add_shortcode('civic-lookup-person-legislators', 'civic_lookup_person_legislators');

 /**
  * Requires: Pods
  * http://localhost:8099/?page_id=1192994
  */
 function civic_lookup_person_form($atts) {
     $mypod = pods( 'person' );
     $url = '/write-your-legislator/';
     // Output a form with all fields
     echo $mypod->form(null,'Submit',$url.'?civic_lookup_id=X_ID_X');
 }

 /**
  * Requires: Pods
  * http://localhost:8099/?page_id=1192996
  * http://localhost:8099/?page_id=1192996&civic_lookup_id=1
  * https://www.experiencesolutionsnow.com/write-your-legislator/?civic_lookup_id=2
  */
 function civic_lookup_person_legislators($atts) {
     $debug = false;
     $pods = pods( 'person' );
     $lookup = new CivicGeoLookup();
     $id = (int)$_REQUEST['civic_lookup_id'];
     ob_start();
     if($id) {
         $person = new Person($id);
         $address = $person->getFullAddress();
         if($debug) $lookup->dump($address);
         if($address) {
             $res = $lookup->lookupAddress($address);
             $lookup->showResult();
         } else {
             ?><div>No address found</div><?php
         }

     } else {
         ?><div>No legislators found</div><?php
     }
     $html = ob_get_clean();
     return $html;
 }


 /**
  * https://www.experiencesolutionsnow.com/civic-test/
  * http://localhost:8099/?page_id=1192981
  */
 function civic_lookup_show($atts) {
     $debug = false;
     $defaults = [
         'id' => false,
         'show' => 'all',
         'congress_show' => 'all',
         'congress_title' => 'default',
         'congress_title_custom' => '',
         'congress_form' => 'yes',
         'congress_summary' => 'no',
         'congress_class' => false,
     ];

     //combines attributes & their default value
     $atts2 = shortcode_atts( $defaults, $atts, 'civic-lookup' );

     $lookup = new CivicGeoLookup();
     if($atts && $atts['redirect']) {
         $lookup->setRedirect($atts['redirect']);
     }
     if($atts && $atts['continue']) {
         $lookup->setContinue($atts['continue']);
     }

     if($debug) {
         $lookup->dump($lookup);
     }
 	//compatibility with previous version
 	if( isset($atts['show']) && !empty($atts['show']) ){
 		$atts['congress_show'] = $atts['show'];
 	}
 	$show = $atts['congress_show'];

 	$html = '';

 	// require("src/page/legislators_start.php");
    ob_start();
    require(dirname(__FILE__)."/../src/blocks/lookup.php");
    $html = ob_get_clean();
 	return $html;
 }
