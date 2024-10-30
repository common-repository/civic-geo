if($congress_key):

    //generate html id for multiple map instances in a page
    $id = date('gis') . rand(1,100);

    $schoice = $show;
    $htext = congress_get_title($atts);

    $html .= '<form action="#" class="legislators" onsubmit="return getCongressFromAddress(this, ' . $shortcode_id . ', ' . $id . ');">';
    // Dont show title if empty
    if( $htext ) $html .= '<p class="le_head">' . $htext . '</p>';
    if( $atts['congress_form'] == 'yes' ){
        $html .= '  <fieldset id="user-details">
                        <label for="congress_address">Use your zip code, or complete address for best results:</label>
                        <input type="hidden" name="showon" value="'.$show.'" id="congress_showon' . $id . '"/>
                        <input type="text" name="congress_address" id="congress_address' . $id . '" placeholder="ex: ' . esc_attr($atts['congress_map_center_placeholder']) . '" />
                        <input type="submit" value="Find" name="submit" class="submit" />
                        <img src="'.LEGISLATORS_PATH.'loader.gif" id="jloader' . $id . '" alt="loading" title="Loading" style="display:none;" />
                        <p class="congress_example"><i>ex: 89106 OR 500 S Grand Central Parkway, Las Vegas, NV 89106 </i></p>
                    </fieldset>';
    }

    // set div wrapper width & height if not set by html class or inline-style
    if( isset($atts['congress_map_size']) && strpos($atts['congress_map_size'], 'x') !== false ){
        list($width, $height) = explode('x', strtolower($atts['congress_map_size']));
    }else{
        $width = $height = '';
    }

    // remove unwanted spaces
    $width = trim($width); $height = trim($height);

    // fix width issue
    if($width){
        if( strpos($width, '%') === false && strpos($width, 'px') === false ){
            $width .= 'px';
        }
    }else{
        $width = '80%';
    }

    // fix height issue
    if($height){
        if( strpos($height, '%') === false && strpos($height, 'px') === false ){
            $height .= 'px';
        }
    }else{
        $height = '190px';
    }

    if( strpos($atts['congress_style'], 'width:') === false ){
        $atts['congress_style'] = 'width:' . $width . ';' . $atts['congress_style'];
    }

    if( strpos($atts['congress_style'], 'height:') === false ){
        $atts['congress_style'] = 'height:' . $height . ';' . $atts['congress_style'];
    }

    if( strpos($atts['congress_style'], 'margin:') === false ){
        $atts['congress_style'] = 'margin:0 auto;' . $atts['congress_style'];
    }

    if( strpos($atts['congress_style'], 'border:') === false ){
        $atts['congress_style'] = 'border:1px solid #EDEDED;' . $atts['congress_style'];
    }


    //div wrapper for the map
    $html .= '<div id="map_canvas' . $id . '"' . ( $atts['congress_class'] ? ' class="' . $atts['congress_class'] . '" ' : '') . ( $atts['congress_style'] ? ' style="' . $atts['congress_style'] . '" ' : '') . '></div>';
    $html .= '<div class="notice">*Note: Many representatives do not have a public email address. We recommend that you visit their website, and copy and paste your letter to them into their contact form. Additionally, you may print your letter and send it in the mail to their office address.</div>';
    $html .= '<div id="congress_holder' . $id . '"></div>';
    $html .= '</form>';

//____________________________________________________________________________________________________________________________________________
    //preparing to call javascript google map api
    $js  = '<script type="text/javascript">';
    $js .= '
        var geocoder' . $id . ', map' . $id . ', marker' . $id . ', congressInitialized' . $id . ' = false;
        function congressInitialize' . $id . '(){
            var latlng = new google.maps.LatLng(36.166081, -115.153088);
            var options = {
                zoom: ' . $atts['congress_map_zoom'] . ',
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            map' . $id . ' = new google.maps.Map(document.getElementById("map_canvas' . $id . '"), options);

            // GEOCODER
            geocoder' . $id . ' = new google.maps.Geocoder();
            // Marker
            marker' . $id . ' = new google.maps.Marker({
                map: map' . $id . ',
                draggable: true
            });

            marker' . $id . '.setPosition(latlng);';

    // Use geocoder to locate the lat-lng of street address
    if( $atts['congress_map_center'] ){
        $js .= '
            //Change to other address
            var address_center = "' . $atts['congress_map_center'] . '";
            geocoder' . $id . '.geocode({"address": address_center}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                        marker' . $id . '.setPosition( results[0].geometry.location );
                        map' . $id . '.panTo( results[0].geometry.location );
                    ';

        if( $atts['congress_form'] == 'no' ){
        $js .= '
                        // List congress members
                        jQuery("#congress_address' . $id . '").val(results[0].formatted_address);
                        getCongressData(results[0].geometry.location.lat(),  results[0].geometry.location.lng(), ' . $shortcode_id . ', ' . $id . ');
                    ';
        }

        $js .= '
                    }
                }
            });
        ';
    }

    $js .= '
            //Add listener to marker for reverse geocoding
            google.maps.event.addListener(marker' . $id . ', "dragend", function (event) {
                jQuery("#jloader' . $id . '").show();
                geocoder' . $id . '.geocode({"latLng": marker' . $id . '.getPosition()}, function(results, status) {
                    jQuery("#jloader' . $id . '").hide();
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            jQuery("#congress_address' . $id . '").val(results[0].formatted_address);
                            getCongressData(results[0].formatted_address, ' . $shortcode_id . ', ' . $id . ');
                        }
                    }
                });
            });

            jQuery("congress_address' . $id . '").keyup(function(){
                jQuery("congress_address' . $id . '").removeClass();
            });
        }
        ';

    if( !defined('CONGRESS_INITIAL_CALL') ) {
        $js .= 'function getCongressFromAddress(f, atts_id, id){

                    var address = jQuery("#congress_address" + id).val();
                    var showon = jQuery("#congress_showon" + id).val();

                    if(!address || address == "" || address.length == 0)
                    {
                        jQuery("#congress_address" + id).addClass( "error" );
                        jQuery("#congress_holder" + id).html( "Missing Address!" );
                        return false;
                    }

                    jQuery("#jloader" + id).show();

                    window["geocoder" + id].geocode( { "address": address}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK)
                        {
                            window["marker" + id].setPosition(results[0].geometry.location);
                            window["map" + id].setCenter(results[0].geometry.location);

                            getCongressData(address, atts_id, id);
                            jQuery("#jloader" + id).hide();
                        }
                        else
                        {
                            jQuery("#congress_holder" + id).html( "Couldn\'t find the address" );
                            jQuery("#jloader" + id).hide();
                        }
                    });

                    return false;
                }
                ';
    }

    if( !defined('CONGRESS_INITIAL_CALL') ) {
        $js .= 'function getCongressData(address, atts_id, id){
                    jQuery("#congress_holder" + id).html( jQuery("<img>",{id:"jloader_"+id,src: jQuery("#jloader" + id).attr("src") }) );

                    var showon = jQuery("#congress_showon" + id).val();
                    var data = {
                        action: "congress_get_api_data",
                        security : security,
                        atts_id: atts_id,
                        showon: showon,
                        address: address
                    };
                    jQuery.post(ajaxurl, data, function(response) {
                        if(response != "Error1" && response != "Error2"){
                            jQuery("#congress_holder" + id).html( response );
                        }else{
                            jQuery("#congress_holder" + id).html( "Address or zip code not found. Try using your complete street address for best results. This information is provided by the <a href=\"https://developers.google.com/civic-information/\" rel=\"noopener nofollow\" target=\"_blank\">Google Civic Information Database</a>." );
                        }
                    });
                }
                ';
    }

    if( !defined('CONGRESS_INITIAL_CALL') ) define('CONGRESS_INITIAL_CALL', true);

    $js .= 'jQuery(document).ready(function(){congressInitialize' . $id . '();});';

if(isset($atts['congress_js']) && $atts['congress_js']){$js .= stripslashes($atts['congress_js']);}

/* Autocomplete */

$js .= "\r\n";
$js .= "var placeSearch, autocomplete;\r\n";
$js .= "function initAutocomplete() {
          var input = document.getElementById('congress_address".$id."');
          autocomplete = new google.maps.places.Autocomplete(input, {types: ['address']});
          autocomplete.setFields(['formatted_address']);
          autocomplete.addListener('place_changed', fillInAddress);
        }\r\n";

$js .= "function fillInAddress() {
          // Get the place details from the autocomplete object.
          var place = autocomplete.getPlace();
        }\r\n";
$js .= '</script>';
    $html .= $js;
//____________________________________________________________________________________________________________________________________________


else:
     $html .='<form action="#" class="legislators">
        <p class="le_head">API Key missing, please update it</p>
        </form>';
endif;
