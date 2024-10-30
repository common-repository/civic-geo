<?php
/**
 * https://www.experiencesolutionsnow.com/write-your-legislator/?civic_lookup_id=3
 */
namespace CivicLookup;

/**
 * http://localhost:8091/?page_id=1192981&congress_address=test&submit=Find+Legislators
 */

class CivicGeoLookup {
    public $api_key;
    public $address;
    public $errors = [];
    public $html;
    public $result;
    public $congressOnly = true;
    public $redirectUrl;

    public function getKey() {
        return wp_kses( get_option( 'google_civic_key' ));
    }

    public function setRedirect($url) {
        $this->redirectUrl = esc_url($url);
    }
    /**
    *
    */
    public function getRedirect() {
        return $this->redirectUrl;
    }

    /**
    * Continue button url with results in var.
    */
    public function getContinue() {
        return $this->redirectUrl;
    }

    public function getUrl() {
        if(!$this->url) {
            global $wp;
            $url =  home_url( $wp->request );
            if($wp->query_string) {
                $url .= "/?".$wp->query_string;
            }
            $this->url = $url;
        }
        return $this->url;
    }

    public function getRep() {
        $p = new Person();
        if($this->result && $this->result[2]) {
            $p->setName($this->result[2]['full_name']);
        }
        return $p;
    }

    public function getSenator1() {
        $p = new Person();
        if($this->result && $this->result[0]) {
            $p->setName($this->result[0]['full_name']);
        }
        return $p;
    }

    public function getSenator2() {
        $p = new Person();
        if($this->result && $this->result[1]) {
            $p->setName($this->result[1]['full_name']);
        }
        return $p;
    }

    public function getRoles($congress_show) {
        switch($congress_show) {
            case"representative":
                $roles = '&roles=legislatorLowerBody&roles=deputyHeadOfGovernment&roles=executiveCouncil&roles=governmentOfficer&roles=headOfGovernment&roles=headOfState';
                break;
            case"senator":
               $roles = '&roles=legislatorUpperBody';
               break;
            default:
                // national senators and representatives
                $roles = '&roles=legislatorLowerBody&roles=legislatorUpperBody&levels=country';
        }
        return $roles;
    }

    /**
     *
     * https://www.experiencesolutionsnow.com/write-your-legislator/?civic_lookup_id=3
     * http://localhost:8091/?page_id=1192981&congress_address=338%NW%54th%st,%seattle%wa,%98107&submit=Find+Legislators&civicLookupShowData=1
     *
     * @todo: Handle this error:
     * { "error": { "code": 400, "message": "Failed to parse address", "errors": [ { "message": "Failed to parse address", "domain": "global", "reason": "parseError" } ] } }
     */
    function lookupAddress($address) {
        $debug = false;
        $useCache = false;
        if(!$address) return false;
        $data = ($atts_id) ? congress_get_shortcodes($atts_id) : array();
        // turn off this feature
        $data['congress_summary'] = 'no';

        //  (senator or representative or all)?
        if( !isset($data['congress_show']) && $showon ) $data['congress_show'] = $showon;

        $output = '';
        if($address) {
            $params = array(
                'key' => wp_kses( get_option( 'google_civic_key' ), '' ),
                'address' => $address
            );

            /**
             * https://developers.google.com/civic-information/docs/v2/representatives/representativeInfoByAddress
             */
            $this->congress_show = $data['congress_show'];
            $roles = $this->getRoles($this->congress_show);


            $api_call = "https://www.googleapis.com/civicinfo/v2/representatives?" . http_build_query( $params ) . $roles;
            if($debug) {
                $this->dump($api_call);
            }
            $cache_key = 'congresslookup_' . $address. $discard;
            $congress = get_transient( $cache_key );

            if ($congress === false || !get_option('congress_cache') ) {
                $request  = wp_remote_get( $api_call );
                $congress = wp_remote_retrieve_body( $request );
                if(isset($_REQUEST['civicLookupShowData'])) {
                    $this->dump($congress->officials);
                }
                if($debug) {
                    print_r($congress);
                }
                if($useCache) {
                    set_transient($cache_key, $congress, get_option('congress_cache_time') * MINUTE_IN_SECONDS);
                }

            }

            $congress = json_decode($congress);
            $this->apiResponse = $congress;
            if( isset($data['congress_options']) && is_array($data['congress_options']) ){
                $a = $data['congress_options'];
            }else{
                $a = get_option("congress_options");
            }

            if ( !$a ) $a = array();


            ///$output .= '<pre>' . print_r($congress, 1) . '</pre>';

            if( !empty($congress->officials) ) {
                $output .= '<div class="legislators_list">';
                $this->result = array();
                $index = -1;
                foreach ($congress->officials as $c) {
                    $the_title = $this->getTitle($a,$c);
                    // This will limit to federal senators and representatives
                    if($this->congressOnly) {
                        if(!$the_title) continue;
                    }

                    if($c->address[0]->line1 == "The White House") {
                        continue;
                    }

                    $index++;
                    $this->result[$index]['pic'] = $c->photoUrl;
                    $this->result[$index]['full_name'] = esc_html( $c->name );
                    //_______________________________________________________

                    $output .= "<div class='legislator_block'><h3 class='legislator'>" . esc_html( $c->name ) . "</h3>";

                    if( in_array("picture", $a) ) {
                        $photoUrl = $this->congressGetPhoto($c->photoUrl);
                        $output .= "<img class='legislator-pic' src='" . $photoUrl . "' width='70' height='88' alt='' />";
                    }

                    if ( $data['congress_summary'] == 'yes' && !empty($c->office->address[0]->line1) ) {

                        $output .= '<p>Office: ' . $c->office->address[0]->line1 . '</p>';
                        $output .= '<p style="clear: both;"></p>';

                    } else {

                        $output .= '<ul class="legislator-contact">';

                        //best approach to get title
                        if($the_title) {
                            $output .= '<li>' . esc_html__('Title','') . ' : ' . $the_title . '</li>';
                            $this->result[$index]['title'] = $the_title;
                        }

                        if ( !empty($c->party) && in_array("party", $a) ){
                            $output .= '<li>' . esc_html__('Party','') . ' : ' . $c->party . '</li>';
                        }

                        if (!empty($c->phones) && in_array("phone", $a) ){
                            $output .= '<li>' . esc_html__('Phone','') . ' : ' . implode(',',$c->phones) . '</li>';
                        }

                        if (!empty($c->emails) && in_array("email", $a)){
                            if (is_array($c->emails)){
                                $emails = array();
                                foreach ( $c->emails as $email ) {
                                    $emails[] = '<a href="mailto:' . $email . '" target="_blank">' . $email . '</a>';
                                }
                                $output .= '<li>' . esc_html__('Emails','') . ' : ' . implode(',',$emails) . '</li>';
                            }
                        }

                        if (!empty($c->address) ){
                            if (is_array($c->address)){
                                $addresses = array();
                                $zips = array();
                                $states = array();
                                foreach ( $c->address as $address ) {
                                    if(!empty($address->line1)){
                                        $addresses[] = $address->line1;
                                        $zips[] = $address->zip;
                                        $states[] = $address->state;
                                    }
                                }

                                if ( !empty($addresses) && in_array("office", $a) ) {
                                    $output .= '<li>' . esc_html__( 'Office', '' ) . ' : ' . implode( ',', $addresses ) . '</li>';
                                }

                                if ( !empty($zips) && in_array("zip_code", $a) ) {
                                    $output .= '<li>' . esc_html__( 'Zip code', '' ) . ' : ' . implode( ',', $zips ) . '</li>';
                                }
                                /*
                                if (!empty($states)  && in_array("state_name", $a)  ){
                                    $output .= '<li>' . esc_html__('State','') . ' : ' . implode(',',$states) . '</li>';
                                }
                                */
                            }
                        }

                        if ( !empty($c->urls) && in_array("website", $a) ){

                            if (is_array($c->urls)){
                                $urls = array();
                                foreach ( $c->urls as $url ) {
                                    $urltxt = (strlen($url) > 30) ? substr($url,0,30).'...' : $url;
                                    $urls[] = '<a href="' . $url . '" target="_blank">' . $urltxt . '</a>';
                                }
                                $output .= '<li>' . esc_html__('Website','') . ' : ' . implode(',',$urls) . '</li>';
                            }

                        }
                        if($c->channels) {
                            foreach ( $c->channels AS $key => $value ) {
                                $output .= $this->showChannel($value,$a,$index);
                            }
                        }
                        $output .= '</ul></div>';
                    }
                }

                // Apply filter
                $results_filter = apply_filters('congress_results', $this->result);
                if( !is_array($results_filter) ){
                    echo $results_filter;
                    // Reset output
                    $output = '';
                } else {
                    $this->result = $results_filter;
                }
                /*
                if($this->result){
                    $output .= '<script>';
                    $output .= 'var responseCongressJson = \'' . json_encode($this->result) . '\';';
                    $output .= 'if(typeof jsCongress !== \'undefined\' && jQuery.isFunction(jsCongress)){jsCongress(responseCongressJson);} ';
                    $output .= '</script>';
                }
                */
                 $output .= '
                 <div style="clear:both"></div>
                 </div><br /><br />';

            }
            else
            {
                // No officials found
                $this->errors[] = "Invalid address";
                $output .= "No information found for address";
            }
        }
        else {
            $this->errors[] = "No address provided";
            $output .= "CivicGeoLookup Error2";
        }
        $this->html = $output;
        return $this->result;
    }

    public function showResult() {
        echo $this->html;
    }

    /**
     *
     */
    public function showChannel($value,$a,$index) {
        if(!$value) return false;
        $this->result[ $index ][ $value->type ] = $value->id;

        $key_translated = $value_translated = '';
        $key_translated = ucwords( str_replace( '_',' ', $value->type ) );
        if ( empty( $value->id ) ) {
            $value_translated = 'Not Available';

        } elseif (
            strpos( $value->id, 'http:' ) !== FALSE
            || strpos( $value->id, 'https:' )
            !== FALSE
        )
        {
            $value_translated = '<a href="'
            . esc_url_raw( $value->id )
            . '" target="_blank">'
            . esc_url( $value->id )
            . '</a>';

        } elseif ( $value->type == 'Facebook' && in_array("facebook_id", $a) ) {
            $fbtxt = (strlen($value->id) > 20) ? substr($value->id,0,20).'...' : $value->id;
            $value_translated = '<a href="'
            . 'https://www.facebook.com/'
            . $value->id
            . '" target="_blank">'
            . esc_url( 'https://www.facebook.com/'
            . $fbtxt )
            . '</a>';

        } elseif ( $value->type == 'Twitter' && in_array("twitter_id", $a) ) {
            $value_translated = '<a href="'
            . esc_url_raw( 'https://twitter.com/'
            . $value->id )
            . '" target="_blank">'
            . esc_url( 'https://twitter.com/'
            . $value->id )
            . '</a>';

        } elseif ( $value->type == 'Votesmart' && in_array("votesmart_id", $a) ) {
            $value_translated = '<a href="'
            . esc_url_raw( 'http://votesmart.org/candidate/'
            . $value->id )
            . '" target="_blank">'
            . esc_url( 'http://votesmart.org/candidate/'
            . $value->id )
            . '</a>';

        } elseif ( $value->type == 'YouTube' && in_array("youtube_id", $a) ) {
            $yttxt = (strlen($value->id) > 10) ? substr($value->id,0,10).'...' : $value->id;
            $value_translated = '<a href="'
            . esc_url_raw( 'https://www.youtube.com/user/'
            . $value->id )
            . '" target="_blank">'
            . esc_url_raw( 'http://www.youtube.com/user/'
            . $yttxt )
            . '</a>';

        } elseif ( $value->type == 'GooglePlus' && in_array("google_plus_id", $a) ) {
            $value_translated = '<a href="'
            . esc_url_raw( 'https://plus.google.com/'
            . $value->id )
            . '" target="_blank">'
            . esc_url_raw( 'https://plus.google.com/'
            . $value->id )
            . '</a>';

        } else {
            $value_translated = $key_translated = '';
        }



        // Apply filters
        if($key_translated && $value_translated){
            $key_translated = apply_filters( 'congress_field', $key_translated, $value->type );
            $value_translated  = apply_filters( 'congress_value', $value_translated, $value->type, $value->id );
            $output .= apply_filters( 'congress_output',
            '<li>' . $key_translated . ' : '
            . $value_translated . '</li>', $value->type, $value->id,
            $key_translated, $value_translated,
            (array) $c );
        }
        return $output;
    }

    /**
     *
     */
    public function getTitle($a,$c) {
        if( in_array("title", $a) ) {
            $the_title = '';
            if($this->congress_show == 'representative'){
                $the_title = 'Representative';
            }elseif($this->congress_show == 'senator'){
                $the_title = 'Senator';
            }else{
                if( isset($c->roles[0]) && !empty($c->roles[0]) ){
                    if($c->roles[0] == 'legislatorLowerBody'){
                        $the_title = 'Representative';
                    }elseif($c->roles[0] == 'legislatorUpperBody'){
                        $the_title = 'Senator';
                    }else{
                        $the_title = '';
                    }
                }elseif( isset($c->urls[0]) && !empty($c->urls[0]) ){
                    if( strpos($c->urls[0], 'senate.gov') !== false ){
                        $the_title = 'Senator';
                    }elseif( strpos($c->urls[0], 'house.gov') !== false ){
                        $the_title = 'Representative';
                    }else{
                        $the_title = '';
                    }
                }else{
                    $the_title = '';
                }
            }
            return $the_title;

        } else {
            return false;
        }
    }

    /**
     *
     */
    public function congressGetPhoto($url){
    	$url = trim($url);

    	//return default image for empty url
    	if( empty($url) ) return LEGISLATORS_PATH . 'photo/unknown.jpg';

    	//return original url for non-ssl site
    	if( !is_ssl() ) return $url;

    	$photo = basename($url);
    	$cache_key = 'congresslookup_photo_' . $photo;
    	$cache_photo = get_transient( $cache_key );

    	if ( $cache_photo === false ) {

    		$request  = wp_remote_get( $url );
    		$data_photo = wp_remote_retrieve_body( $request );

    		$photo_file = LEGISLATORS_PATH_BASE . 'photo/' . $photo;
    		$photo_url  = LEGISLATORS_PATH . 'photo/' . $photo;
    		file_put_contents( $photo_file, $data_photo );

    		set_transient( $cache_key, $photo_url, 1 * DAY_IN_SECONDS );
    		$cache_photo = $photo_url;
    	}

    	return $url;
    }

    public function dump($var) {

        if(function_exists('dump')) {
            dump($var);
        } else {
            print_r($var);
        }


    }
    /**
     * was congress_get_default_title
     */
    public function getDefaultTitle($show = 'all') {
        if($show == 'representative'){
            $htext = 'Locate your Representatives';
        }elseif($show == 'senator'){
            $htext = 'Locate Your Senators';
        }else{
            $htext = 'Locate your Senators and Representative';
        }
        return $htext;
    }

    /**
     * was congress_get_shortcodes
     */
    public function getShortcodes($id = false){
    	$list = get_option('congress_shortcodes');
    	if($id){
    		if( isset($list[$id]) ) return $list[$id];
    		return false;
    	}
    	return $list;
    }
    /**
     * was qkCongressCheckOptions
     */
    public function qkCongressCheckOptions($t, $opt = false)
    {
    	if( ! is_array($opt) ){
    		// Use default options if not supplied
    		$opt = get_option('congress_options');
    	}

    	if(is_array($opt))
    	{
    		foreach($opt AS $key=>$value){
    			if($value == $t) return 'checked="checked"';
    		}
    	}

    	return('');
    }
    /**
     *
     */
    public function GetRemoteLastModified( $uri )
    {
        // default
        $unixtime = 0;

        $fp = @fopen( $uri, "r" );
        if( !$fp ) {return;}

        $MetaData = stream_get_meta_data( $fp );

        foreach( $MetaData['wrapper_data'] as $response )
        {
            // case: redirection
            if( substr( strtolower($response), 0, 10 ) == 'location: ' )
            {
                $newUri = substr( $response, 10 );
                fclose( $fp );
                return GetRemoteLastModified( $newUri );
            }
            // case: last-modified
            elseif( substr( strtolower($response), 0, 15 ) == 'last-modified: ' )
            {
                $unixtime = strtotime( substr($response, 15) );
                break;
            }
        }
        fclose( $fp );
        return $unixtime;
    }
    /**
     * was congress_get_title
     */
    public function getPageTitle($line = false){
        $lookup = new CivicGeoLookup();
    	if( !$line ) return false;

    	if( $line['congress_title'] == 'default'){
    		$congress_title = $lookup->getDefaultTitle($line['congress_show']);
    	}elseif( $line['congress_title'] == 'empty'){
    		$congress_title = '';
    	}elseif( $line['congress_title'] == 'custom'){
    		$congress_title = $line['congress_title_custom'];
    	}

    	return $congress_title;
    }

}
