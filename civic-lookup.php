<?php
/*
Plugin Name: Civic Lookup
Plugin URI: https://bitbucket.org/jonahwhale/civic-geo/src/main/
Description: Civic Lookup is powered by data APIs from Google
Google Civic Information API</a>
Version: 1.2
Author: Jonah B
Author URI: https://www.linkedin.com/in/jonah-baker-764b585
License: GNU General Public License v2 or later
Contributors: jonahwhale
*/

/**
* https://wordpress.org/plugins/civic-geo
*/
use CivicLookup\CivicGeoLookup as CivicGeoLookup;

if ( !defined('ABSPATH') ) exit();

define('LEGISLATORS_PATH', plugin_dir_url(__FILE__));
define('LEGISLATORS_PATH_BASE', plugin_dir_path(__FILE__));

require_once('src/classes.php');
require_once('src/install.php');
require_once('src/enqueue.php');
require_once('src/shortcodes.php');
require_once('src/admin.php');

if ( civic_lookup_is_cli_running() ) {
    require_once 'src/commands.php';
}

/**
 * http://localhost:8099/?page_id=1192981
 */
function civic_lookup_head() {
    ?>

	<script type="text/javascript">
		var ajaxurl = <?php echo json_encode( admin_url( "admin-ajax.php" ) ); ?>;
		var security = <?php echo json_encode( wp_create_nonce( "my-special-string" ) ); ?>;
	</script>

    <?php
}

function civic_lookup_is_cli_running() {
    return defined( 'WP_CLI' ) && WP_CLI;
}
