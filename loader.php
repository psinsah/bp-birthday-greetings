<?php
/**
 * Plugin Name: BP Birthday Greetings
 * Plugin URI:  https://prashantdev.wordpress.com
 * Description: Members will receive a birthday greeting as a notification
 * Author:      Prashant Singh
 * Author URI:  https://profiles.wordpress.org/prashantvatsh
 * Version:     1.0.2
 * Text Domain: bp-birthday-greetings
 * License:     GPLv2 or later
 */

defined( 'ABSPATH' ) || exit;


add_action('plugins_loaded','bp_birthday_check_is_buddypress');
function bp_birthday_check_is_buddypress(){
	if ( function_exists('bp_is_active') ) {
		require( dirname( __FILE__ ) . '/bp-birthday-greetings.php' );
		require( dirname( __FILE__ ) . '/bp-birthday-widget.php' );
	}else{
		add_action( 'admin_notices', 'bp_birthday_buddypress_inactive__error' );
	}
}

function bp_birthday_buddypress_inactive__error() {
	$class = 'notice notice-error';
	$message = __( 'BP Birthday Greetings requires BuddyPress to be active and running.', 'bp-birthday-greetings' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

register_activation_hook(__FILE__, 'bp_birthday_plugin_activation');

function bp_birthday_plugin_activation() {
    if (! wp_next_scheduled ( 'bp_birthday_daily_event' )) {
		wp_schedule_event(time(), 'daily', 'bp_birthday_daily_event');
    }
}

add_action('bp_birthday_daily_event', 'bp_birthday_do_this_daily');

function bp_birthday_do_this_daily() {
	global $wp, $bp, $wpdb;
	$bp_birthday_option_value = bp_get_option( 'bp-dob' );
	$sql = $wpdb->prepare( "SELECT profile.user_id, profile.value FROM {$bp->profile->table_name_data} profile INNER JOIN $wpdb->users users ON profile.user_id = users.id AND user_status != 1 WHERE profile.field_id = %d", $bp_birthday_option_value);
	$profileval = $wpdb->get_results($sql);
	foreach ($profileval as $profileobj) {
		$timeoffset = get_option('gmt_offset');
		if(!is_numeric($profileobj->value)) {
			$bday = strtotime($profileobj->value) + $timeoffset;
		}else {
			$bday = $profileobj->value + $timeoffset;
		}
		if ((date_i18n("n")==date("n",$bday))&&(date_i18n("j")==date("j",$bday)))
			$birthdays[] = $profileobj->user_id;
		if(!empty($birthdays)){
			bp_birthday_happy_birthday_notification($birthdays);
		}
	}
}

function bp_birthday_happy_birthday_notification($birthdays){
	foreach ($birthdays as $key => $value) {
		bp_notifications_add_notification( array(
			'user_id'           => $value,
			'item_id'           => $value,
			'component_name'    => 'birthday',
			'component_action'  => 'ps_birthday_action',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
	
}

function bp_birthday_get_registered_components( $component_names = array() ) {
	if ( ! is_array( $component_names ) ) {
		$component_names = array();
	}
	array_push( $component_names, 'birthday' );
	return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'bp_birthday_get_registered_components' );

function bp_birthday_buddypress_notifications( $content, $item_id, $secondary_item_id, $total_items, $format = 'string', $action, $component  ) {
	if ( 'ps_birthday_action' === $action ) {
		$site_title = get_bloginfo( 'name' );
		$custom_title = __("Wish you a very happy birthday. $site_title wishes you more success and peace in life.",'bp-birthday-greetings');
		$custom_link  = '';
		$custom_text = __("Wish you a very happy birthday. $site_title wishes you more success and peace in life.", 'bp-birthday-greetings');
		if ( 'string' === $format ) {
			$return = apply_filters( 'ps_birthday_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
		} else {
			$return = apply_filters( 'ps_birthday_filter', array(
				'text' => $custom_text,
				'link' => $custom_link
			), $custom_link, (int) $total_items, $custom_text, $custom_title );
		}
		return $return;
	}
}
add_filter( 'bp_notifications_get_notifications_for_user', 'bp_birthday_buddypress_notifications', 10, 7);

add_action('wp_enqueue_scripts', 'bp_birthday_enqueue_style');
function bp_birthday_enqueue_style(){
	wp_enqueue_style('birthday-style',  plugin_dir_url( __FILE__ )  .'assets/css/bp-birthday-style.css');
}
