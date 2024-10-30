<?php
/*
Plugin Name: Holiday Notifications
Description: The announcement plugin allows you to easily set announcements for your website to let your customers know of upcoming holidays, events, and promotions..
Version: 1.0.0
Author: Seota Digital Marketing
Author URI: https://seota.com/
Text Domain: wp-holiday-notifications
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define('WPHNP_URL', dirname( __FILE__ ));

require_once WPHNP_URL.'/wp-holiday-notifications-cpt.php';

//------------ include css styles-----------------//
function WPHNP_enqueue_style() 
{
	wp_enqueue_style('wp-holiday-style', plugins_url('css/wp-holiday-notifications.css', __FILE__),'','1.0', false);
	wp_enqueue_script( 'wp-holiday-script', plugins_url('js/wp-holiday-notifications.js', __FILE__), array('jquery'), false, true);
}
add_action( 'wp_enqueue_scripts', 'WPHNP_enqueue_style' );

function WPHNP_init() 
{
    // set the current plugin version
    $wphnp_options = '1.0.0';
    $whn_options = get_option( 'wphnp_options' );
    // if it's not the latest version.
    if ( version_compare( $wphnp_options, $whn_options[ 'version' ], '>' ) ) 
    {
        $whn_options[ 'version' ] = $wphnp_options;
        update_option( 'wphnp_options', $whn_options );
    }
}

add_action( 'init', 'WPHNP_init', 0 );

function wphnp_this_mdl_footer(){
	$_end_compare = date('Y-m-d H:i:s', strtotime('+1 day'));
	$_start_compare = date('Y-m-d H:i:s', strtotime('-1 day'));
	$_args = array(
        'numberposts'       => -1,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'post_type'         => 'holiday-notify',
        'post_status'       => 'publish',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_start_dtp',
				'value' => $_start_compare,
				'compare' => '<=',
				'type ' => 'DATETIME'
			),
			array(
				'key' => '_end_dtp',
				'value' => $_end_compare,
				'compare' => '>=',
				'type ' => 'DATETIME'
			)
		)
    );
    $posts_array = get_posts( $_args );
	foreach($posts_array as $_pt){
		$_title = $_pt->post_title;
		$_id = $_pt->ID;
		$_content = $_pt->post_content;
		$_zone = get_post_meta($_id, '_dtp_zone', true);
		$_st = get_post_meta($_id, '_start_dtp', true);
		$_ed = get_post_meta($_id, '_end_dtp', true);
		$_img = get_the_post_thumbnail_url($_id, 'full');
		$_dzone = date_default_timezone_get();
		$_cr = date_default_timezone_set($_zone);
		$_cr_date = time();
		$_start_dt = strtotime($_st);
		$_end_dt = strtotime($_ed);
		if(($_start_dt <= $_cr_date) && ($_end_dt >= $_cr_date) ){
			echo '<div class="whn-modal" data-id="'.esc_html($_id).'">
			  <div class="whn-modal-content">
				<div class="whn-modal-header">
				  <span class="whn-close">&times;</span>
				  <h3>'.esc_html($_title).'</h3>
				</div>
				<div class="whn-modal-body"><div class="whn-left"><img src="'.esc_url($_img).'" /></div>
				<div class="whn-right">'.$_content.'</div></div>
			  </div>
			</div>';
		}
		date_default_timezone_set($_dzone);
	}
} 
add_action('wp_footer', 'wphnp_this_mdl_footer');