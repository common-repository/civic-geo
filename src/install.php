<?php
/**
 * 
 */
register_activation_hook( __FILE__, 'CongressLookup_install' );

function congressLookup_install()
{
	update_option('congress_cache', 1);
	update_option('congress_cache_time', 30);
	update_option('congress_themes', 'modern');
	//update_option('congress_select_choice' , 'all');
	update_option('congress_photos_last_modified', '1307992245');
	update_option('congress_options', array(0=>'title', 1=>'first_name', 2=>'last_name', 3=>'picture', 4=>'chamber', 5=>'state_rank', 6=> 'state_name', 7=> 'website', 8=> 'contact_form'));
}
