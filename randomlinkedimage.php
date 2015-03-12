<?php
/*
Plugin Name: Random Linked Image
Plugin URI: http://wordpress.dizzyd.net
Description: Adds ability to upload images and choose where they link, having them display randomly in a given area.
Version: 1.2
Author: David Wood
Author URI: http://dizzyd.net
*/



// Install script?
register_activation_hook(__FILE__,'rli_install');


// create mysql tables
function rli_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "rli";

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	 	id mediumint(9) NOT NULL AUTO_INCREMENT,
	  	image tinytext NOT NULL,
	  	url VARCHAR(255) NOT NULL,
	  	UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

    }

}

// The actual magic. Displaying a RLI
function get_RLI() {
	global $wpdb;
	$table_name = $wpdb->prefix . "rli";

       $rli = $wpdb->get_row( "SELECT id, image, url FROM ".$table_name." ORDER BY rand() LIMIT 1" );

	echo "<a href=\"".$rli->url."\"><img src=\"".$rli->image."\" /></a>";
}




// Hook for adding admin menus
add_action('admin_menu', 'rli_add_images');

// action function for above hook
function rli_add_images() {
    // Add a new submenu under Options:
    add_options_page('RLI Upload', 'RLI Manage', 'administrator', 'rli-management', 'rli_options_page');

}

function rli_options_page() {

    $opt_name1 = 'url';
    $opt_name2 = 'image';
    $hidden_field_name = 'mt_submit_hidden';
    $hidden_rli_name = 'deleteme';
    $data_field_name1 = 'rli_url';
    $data_field_name2 = 'rli_img';

    // Read in existing option value from database
    // $opt_val = get_option( $opt_name );

    // See if RLI was deleted
    if( $_POST[ $hidden_rli_name ] == 'Y' ) {
	// Get the values
	$rli_id = $_POST[ id ];

	global $wpdb;
	$table_name = $wpdb->prefix . "rli";

	$wpdb->query("
		   DELETE FROM ".$table_name." WHERE id = '$rli_id'");
	
	?>
	<div class="updated"><p><strong><?php _e('RLI Removed.', 'mt_trans_domain' ); ?></strong></p></div>
	<?php

   	 }

	




    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val1 = $_POST[ $data_field_name1 ];
        $opt_val2 = $_POST[ $data_field_name2 ];
        // Save the posted value in the database
        // update_option( $opt_name, $opt_val ); // Old Way
	
 	 global $wpdb;
	 $table_name = $wpdb->prefix . "rli";

        $sql = "INSERT INTO " . $table_name . " (id, image, url) 
                VALUES ('', '$opt_val1', '$opt_val2')";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('RLI Added.', 'mt_trans_domain' ); ?></strong></p></div>
<?php

    }

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Random Linked Image Upload', 'mt_trans_domain' ) . "</h2>";

    // options form
    
    ?>
<p><i>RLI uses the existing <a href="upload.php">Wordpress Media Library</a> to keep things simple. Upload the image you want there, copy the <b>File URL</b>, then come back here and paste it below along with the link you want, and RLI will take care of the rest!<br />Simply place <b>&#60;? get_RLI(); ?&#62;</b> anywhere in your theme to display your RLI.</i></p>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><span style="font-size: 16px"><?php _e("Image:", 'mt_trans_domain' ); ?></span><br /> 
<input type="text" name="<?php echo $data_field_name1; ?>" value="<?php echo $opt_val1; ?>" size="20">
</p>
<p><span style="font-size: 16px"><?php _e("Link:", 'mt_trans_domain' ); ?></span><br />
<input type="text" name="<?php echo $data_field_name2; ?>" value="<?php echo $opt_val2; ?>" size="20">
</p>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Add RLI', 'mt_trans_domain' ) ?>" style="color: #00CC00;font-weight: bold"/>
</p>

</form>
<hr />
<h2>Current RLI's</h2>
<?php

	global $wpdb;
	$table_name = $wpdb->prefix . "rli";

       $listings = $wpdb->get_results( "SELECT id, image, url FROM ".$table_name." ORDER BY id" );

	foreach ($listings as $listing) {
	
		?>
			
			<form name="<?php echo $listing->id; ?>" method="post" action="">
			<input type="hidden" value="Y" name="deleteme">
			<input type="hidden" value="<?php echo $listing->id; ?>" name="id">
			<p><b>Image:</b><br /><img src="<?php echo $listing->image; ?>" /><br /><b>Link: </b><?php echo $listing->url; ?></p>
			<p class="submit" >
			<input type="submit" name="Delete" value="Delete RLI" style="color: #990000;font-weight: bold"></p>
			</form><br />
	<?php
	}
	?> 
	
</div>

<?php
 

}

?>