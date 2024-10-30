<?php
/**
 * Register a Holiday CPT.
 */

function WPHNP_post_init() {
    $labels = array(
        'name'               => __( 'Holiday Notifications', 'post type general name', 'wp-holiday-notifications' ),
        'singular_name'      => __( 'Holiday Notifications', 'post type singular name', 'wp-holiday-notifications' ),
        'menu_name'          => __( 'Holiday Notifications', 'admin menu', 'wp-holiday-notifications' ),
        'name_admin_bar'     => __( 'Holiday Notifications', 'add new on admin bar', 'wp-holiday-notifications' ),
        'add_new'            => __( 'Add New', 'Service', 'wp-holiday-notifications' ),
        'add_new_item'       => __( 'Add New', 'wp-holiday-notifications' ),
        'new_item'           => __( 'New Notification', 'wp-holiday-notifications' ),
        'edit_item'          => __( 'Edit Notification', 'wp-holiday-notifications' ),
        'view_item'          => __( 'View Notification', 'wp-holiday-notifications' ),
        'all_items'          => __( 'All Notifications', 'wp-holiday-notifications' ),
        'search_items'       => __( 'Search Notification', 'wp-holiday-notifications' ),
        'parent_item_colon'  => __( 'Parent Notification:', 'wp-holiday-notifications' ),
        'not_found'          => __( 'No Notification found.', 'wp-holiday-notifications' ),
        'not_found_in_trash' => __( 'No Notification found in Trash.', 'wp-holiday-notifications' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.'),
        'public'             => true,
        'menu_icon' 		 => 'dashicons-bell',
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'holiday-notify' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'thumbnail'),
		'register_meta_box_cb' => 'wphn_metaboxe'
    );

    register_post_type( 'holiday-notify', $args );
}
add_action( 'init', 'WPHNP_post_init' );

// Add the Meta Boxe support

function wphn_metaboxe() {
	add_meta_box('wphnp__box', 'Notification Settings', 'wphnp__box', 'holiday-notify', 'normal', 'high');
}

// Add meta boxes

function wphnp__box() {
	global $post;
	
	echo '<style>.wd30{width: 185px;display: inline-block}.add_row .inner-cell:first-child{margin-right: 10px}
	.ps_wraper label{font-size: 15px;font-weight: 600;display: block;margin: 5px 0}.wdt{width: 165px}.ps_wraper input{margin-bottom: 15px}</style>';
	
	echo '<div class="ps_wraper">';
	
	$_start_dtp = get_post_meta($post->ID, '_start_dtp', true);
	if(!empty($_start_dtp)){
		$_start_dtp = rtrim(str_replace(' ', 'T', $_start_dtp), ':00');
	}
	echo '<label>Start Date/Time</label><input type="datetime-local" style="width: 250px;max-width: 100%;" id="_start_dtp" name="_start_dtp" value="' . esc_html($_start_dtp)  . '" class="widefat" />';
	
	$_end_dtp = get_post_meta($post->ID, '_end_dtp', true);
	if(!empty($_end_dtp)){
		$_end_dtp = rtrim(str_replace(' ', 'T', $_end_dtp), ':00');
	}
	$_dtp_zone = get_post_meta($post->ID, '_dtp_zone', true);
	if(empty($_dtp_zone)){
		$_dtp_zone = date_default_timezone_get();
	}
	echo '<label>End Date/Time</label><input type="datetime-local" style="width: 250px;max-width: 100%;" id="_end_dtp" name="_end_dtp" value="' . esc_html($_end_dtp)  . '" class="widefat" />';
	echo "<label>Select Timezone</label><select name='_dtp_zone'><option value=''>Select</option>";
	$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	foreach($tzlist as $value)
	{
		$_selected = ($value == $_dtp_zone) ? 'selected' : '';
		echo "<option value=". esc_html($value) ." ".$_selected.">". esc_html($value) ."</option>";
	}
	echo "<select>";
	echo '</div>';
}

// Save the Metabox Data

function wphnp_savemeta($post_id, $post) {
	global $wpdb;
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	if(!empty($_POST['_start_dtp'])){
		$_sdt = str_replace('T', ' ', sanitize_text_field($_POST['_start_dtp']));
		$_sdt = $_sdt . ':00';
	}
	else{
		$_sdt = '';
	}
	if(!empty($_POST['_end_dtp'])){
		$_edt = str_replace('T', ' ', sanitize_text_field($_POST['_end_dtp']));
		$_edt = $_edt . ':00';
	}
	else{
		$_edt = '';
	}
	$whn_metas['_start_dtp'] = $_sdt;
	$whn_metas['_end_dtp'] = $_edt;
	$whn_metas['_dtp_zone'] = isset($_POST['_dtp_zone']) ? sanitize_text_field($_POST['_dtp_zone']) : '';
	
	
	foreach ($whn_metas as $key => $value) { // Cycle through the $fcmp_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}

}

add_action('save_post', 'wphnp_savemeta', 1, 2);
