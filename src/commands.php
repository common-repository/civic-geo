<?php
/**
* wp-cli commands
*  zip -vr civic-geo.zip civic-geo/ -x "*.DS_Store" -x "*.git*"
*/
class cli_civic_geo extends WP_CLI_Command {

    /**
     * General info
     *
     * wp autosafety info nhtsacomplaints
     */
    public function info($vars) {
        echo get_option('congress_google_map_api_key');
    }
    /**
     * General info
     *
     * wp autosafety info nhtsacomplaints
     */
    public function lookup($vars) {
        echo "\n Looking up...";
        $address = '915 Nw 45Th St Seattle, WA';
        $lookup = new CivicGeoLookup();
        $out = $lookup->lookupAddress($address);
        print_r($out);
    }
}

WP_CLI::add_command( 'civic-geo', 'cli_civic_geo' );
