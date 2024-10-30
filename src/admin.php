<?php
/**
* https://bitbucket.org/p24alliance/autosafety/issues/49/match-submissions-to-congressional-reps
* https://bitbucket.org/jonahwhale/civic-geo/get/main.zip
*/
use CivicLookup\CivicGeoLookup as CivicGeoLookup;
/**
 * Settings url
 * http://localhost:8099/?page_id=1192981
 * http://localhost:8099/wp-admin/options-general.php?page=mt-cglu
 */
add_filter( 'plugin_action_links_civic-lookup/civic-lookup.php', 'civic_lookup_settings_link' );

function civic_lookup_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		'mt-cglu',
		get_admin_url() . 'admin.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}

/**
* http://localhost:8099/wp-admin/options-general.php?page=mt-cglu
* https://bitbucket.org/p24alliance/autosafety/issues/49/match-submissions-to-congressional-reps
* https://www.experiencesolutionsnow.com/civic-test/
*/
function congress_admin_config(){
    $useGoogleMaps = false;
    $google_civic_key = get_option('google_civic_key');
    $useCache = false;
    $lookup = new CivicGeoLookup();
    ?>
    <form name="addnew" method="post" action="options.php">
        <?php settings_fields('civic-lookup-group'); ?>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="google_civic_key">
                            Google Civic Key:
                        </label>
                    </th>
                    <td colspan="2">
                        <input name="google_civic_key" type="text" size="45"
                        value="<?php echo esc_attr($google_civic_key); ?>">
                        <p>
                            Get your Google Civic API Key at the
                            <a
                            href="https://console.developers.google.com/apis/credentials"
                            target="_blank">
                                Google APIs
                            </a>
                            and enable
                            <a href="https://console.cloud.google.com/apis/library/civicinfo.googleapis.com" target="_blank">
                                Google Civic Information API
                            </a>
                        </p>
                    </td>
                </tr>
                <?php if($useGoogleMaps) { ?>
                    <tr valign="top">
                        <th scope="row"><label for="congress_google_map_api_key">Google Map API Key:</label></th>
                        <td colspan="2">
                            <input type="text" class="regular-text" name="congress_google_map_api_key" value="<?php echo esc_attr( get_option('congress_google_map_api_key') ); ?>" />
                            <p>Get Google Map API Key <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">here</a></p>
                        </td>
                    </tr>
                <?php } ?>
                <tr valign="top">
                    <td colspan="3"><hr /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label>What to display?:</label></th>
                    <td colspan="2">
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <input name="congress_options[]" type="checkbox" id="title" value="title" <?php echo $lookup->qkCongressCheckOptions("title"); ?>>&nbsp;<label for="title">Title</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="picture" value="picture" <?php echo $lookup->qkCongressCheckOptions("picture"); ?>>&nbsp;<label for="picture">Picture</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="office" value="office" <?php echo $lookup->qkCongressCheckOptions("office"); ?>>&nbsp;<label for="last_name">Office</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="phone" value="phone" <?php echo $lookup->qkCongressCheckOptions("phone"); ?>>&nbsp;<label for="phone">Phone</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="zip_code" value="zip_code" <?php echo $lookup->qkCongressCheckOptions("zip_code"); ?>>&nbsp;<label for="zip_code">Zip code</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="party" value="party" <?php echo $lookup->qkCongressCheckOptions("party"); ?>>&nbsp;<label for="party">Party</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="state_name" value="state_name" <?php echo $lookup->qkCongressCheckOptions("state_name"); ?>>&nbsp;<label for="state_name">State&nbsp;Name</label>

                                    </td>
                                    <td>&nbsp;&nbsp;</td>
                                    <td>
                                        <input name="congress_options[]" type="checkbox" id="contact_form" value="contact_form" <?php echo $lookup->qkCongressCheckOptions("contact_form"); ?>>&nbsp;<label for="contact_form">Contact&nbsp;Form</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="email" value="email" <?php echo $lookup->qkCongressCheckOptions("email"); ?>>&nbsp;<label for="email">Email</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="website" value="website" <?php echo $lookup->qkCongressCheckOptions("website"); ?>>&nbsp;<label for="website">Website</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="facebook_id" value="facebook_id" <?php echo $lookup->qkCongressCheckOptions("facebook_id"); ?>>&nbsp;<label for="facebook_id">Facebook&nbsp;ID</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="youtube_id" value="youtube_id" <?php echo $lookup->qkCongressCheckOptions("youtube_id"); ?>>&nbsp;<label for="youtube_id">Youtube&nbsp;ID</label><br />
                                        <input name="congress_options[]" type="checkbox" id="twitter_id" value="twitter_id" <?php echo $lookup->qkCongressCheckOptions("twitter_id"); ?>>&nbsp;<label for="twitter_id">Twitter&nbsp;ID</label><br />
                                        <input name="congress_options[]" type="checkbox" id="google_plus_id" value="google_plus_id" <?php echo $lookup->qkCongressCheckOptions("google_plus_id"); ?>>&nbsp;<label for="google_plus_id">Google Plus ID</label><br />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php if($useCache) { ?>
                    <tr valign="top"><td colspan="3"><hr></td></tr>

                    <tr valign="top">
                        <th scope="row"><label>Cache:</label></th>
                        <td>
                            <p>Enable this to cache the data returned by the API,
                                to reduce the number of requests, and for fast loading.
                                 Select for how many minutes you would like the data to be cached &amp; saved</p>
                            <input name="congress_cache" id="congress_cache" type="checkbox" value="1" <?php echo checked(get_option('congress_cache'),1); ?>>&nbsp;<label for="congress_cache">Enable&nbsp;cache?</label>
                            <p>
                                <label for="congress_cache_time">Cache time:</label>
                                <input name="congress_cache_time" id="congress_cache_time" type="text" size="5" style="width:40px" value="<?php echo get_option('congress_cache_time'); ?>"> <small><i>minutes</i></small>
                            </p>
                        </td>
                        <td valign="top" rowspan="2">
                            <div style="background-color: #FFFFE0;border: 1px solid #E8E7AE;padding: 10px;position: relative;text-align: center;top: 10px;width: 200px;">
                                <!-- consider donating -->

                            </div>
                        </td>
                    </tr>
                <?php } ?>

                <tr valign="top"><td colspan="3"><hr></td></tr>
                <tr valign="top">
                    <th scope="row"><label>Short Codes:</label></th>
                    <td colspan="2">
                        <table>
                            <tr>
                                <th> For Searching All: </th>
                                <td>
                                    <input type="text" name="tmp1" value="[civic-lookup]" style="border:none; background:transparent; box-shadow:none; width: 300px;">
                                </td>
                                </tr>
                            <tr>
                                <th> For Searching Senators only: </th>
                                <td><input type="text" name="tmp1" value="[civic-lookup show='senator']" style="border:none; background:transparent; box-shadow:none; width: 300px;"></td>
                            </tr>
                            <tr>
                                <th> For Searching Representatives only: </th>
                                <td><input type="text" name="tmp1" value="[civic-lookup show='representative']" style="border:none; background:transparent; box-shadow:none; width: 300px;"></td>
                            </tr>
                            <tr>
                                <th> Redirect </th>
                                <td>
                                    <input type="text" name="tmp1" value="[civic-lookup redirect='/example']" style="border:none; background:transparent; box-shadow:none; width: 300px;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Continue
                                </th>
                                <td>
                                    <input type="text" name="tmp1" value="[civic-lookup continue='/example']" style="border:none; background:transparent; box-shadow:none; width: 300px;">
                                    <p>Will show continue button with populated variables</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr valign="top"><td colspan="3"><hr></td></tr>
                <tr valign="top"><td>&nbsp;</td><td colspan="2"><?php submit_button(); ?></td></tr>
            </tbody>
        </table>

    </form>


    <?php

    if($google_civic_key) {
        ?>

        <h2>Example results</h2><?php
        $examples = [
            'partial'=>'1600 Pennsylvania Avenue NW, Washington, D.C. ',
            'full'=>'915 Nw 45Th St Seattle WA'
        ];
        $lookup = new CivicGeoLookup();
        foreach($examples as $address) {
            ?>
            <hr />
            <h3><?= esc_html($address); ?>:</h3><?php
            $res = $lookup->lookupAddress($address);
            $lookup->showResult();
        }
        ?>
        <h2>Example json response</h2>
        
        <pre><?= $lookup->dump($lookup->apiResponse); ?></pre><?php
    }
}

/**
 * Notices
 */
add_action('admin_notices', 'civic_lookup_admin_notices');

function civic_lookup_admin_notices(){

	$screen = get_current_screen();
	if( $screen->id != 'settings_page_mt-cglu' ) return false;


	/**********************************************/
	/* Messages after form submission             */
	/**********************************************/
	if( !isset( $_GET['message'] ) ) return false;

	switch($_GET['message']){
		case 'update':
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Configuration saved.', 'congresslookup' );
			break;
		case 'update_shortcode':
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Shortcode setting saved.', 'congresslookup' );
			break;
		case 'delete_shortcode':
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Shortcode deleted.', 'congresslookup' );
			break;
		case 'photos_updated':
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Photos updated.', 'congresslookup' );
			break;
		case 'error_zip':
			$class = 'notice warning is-dismissible';
			$message = __( 'An error occured while updating the Photos', 'congresslookup' );
			break;
		case 'error_unzip':
			$class = 'notice warning is-dismissible';
			$message = __( 'An error occured while updating the Photos', 'congresslookup' );
			break;
	}

	if( isset($message) && isset($class) ){
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
	return true;
}

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'civic_lookup_menu');
	add_action( 'admin_init', 'civic_lookup_register_settings' );
}else{
	add_filter('widget_text', 'do_shortcode', 1);
}


/**
* http://localhost:8099/?page_id=1192981
*/
function civic_lookup_register_settings() { // allowlist options
	register_setting( 'civic-lookup-group', 'google_civic_key' );
	register_setting( 'civic-lookup-group', 'congress_google_map_api_key' );
	register_setting( 'civic-lookup-group', 'congress_cache' );
	register_setting( 'civic-lookup-group', 'congress_cache_time' );
	register_setting( 'civic-lookup-group', 'congress_options' );
    register_setting( 'civic-lookup-group', 'congress_redirect_url' );
}

function civic_lookup_menu(){
	add_options_page('Civic Lookup', 'Civic Lookup', 'administrator', 'mt-cglu', 'civic_lookup_settings');
}



/**
 * http://localhost:8099/wp-admin/options-general.php?page=mt-cglu
 */
function civic_lookup_settings() {
    //get shortcodes
    /*
    $list = congress_get_shortcodes();
    $total_list = (is_array($list)) ? sizeof($list) : 0;
    $total_list = ($total_list) ? '(' . number_format($total_list) . ')' : '';
    */
    //available tabs
    $array_tabs = array(
        'config' 		=> 'Configuration'
    );

    //set default active tab
    if( !isset($_GET['tab']) ){
        $active_tab = 'config';
    }else{
        $active_tab = ( in_array($_GET['tab'], array_keys($array_tabs) ) ) ? wp_kses($_GET['tab'], '') : 'config';
    }

    ?>
    <div class="wrap">
        <?php if (isset($_REQUEST['updated']) && $_REQUEST['updated'] == 'true') { ?>
            <div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
        <?php  } ?>

        <h2 class="nav-tab-wrapper">
            <?php
            foreach($array_tabs as $key => $value){
                echo '<a href="' . admin_url('options-general.php?page=mt-cglu&tab=' . $key) . '" class="nav-tab ' . ($active_tab == $key ? 'nav-tab-active' : '') . '">' . $value . '</a>';
            }
            ?>
        </h2>

        <div class="content-tab-wrapper" id="congress-content">
            <?php

            congress_admin_config();

            ?>
        </div>
    </div>
    <style>
    .content-tab-wrapper{border: solid 1px #ccc; border-top: none; padding: 10px; max-width: 1125px;}
    #congress-content .congress_row{}
        #congress-content .congress_row:hover{background-color: #F3F3F3;}
        #congress-content .row_odd{background-color: #EEE;}
        #congress-content fieldset{border: dotted 1px #AAA; padding: 10px; margin: 5px 5px 20px 5px;}
        #congress-content fieldset legend{font-weight: bold; font-size: initial;}
        #congress-content #default_title_id{font-style: italic; font-weight: bold;}
        #congress-content h1{margin: 0 0 25px 10px; color: blue;}
    </style>
    <?php
}
