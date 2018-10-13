<?php
defined( 'ABSPATH' ) || exit;

function  bp_birthday_greetings_settings() {
    add_settings_section(
        'ps_birthday_section',
 
        __( 'BP Birthhday Greetings Settings',  'bp-birthday-greetings' ),
 
        'bp_birthday_greetings_page_callback_section',
 
        'buddypress'
    );
 
    add_settings_field(
        'bp-dob',
 
        __( 'Select DOB Field', 'bp-birthday-greetings' ),
 
        'bp_birthday_greetings_field_callback',
 
        'buddypress',
 
        'ps_birthday_section'
    );

    register_setting(
        'buddypress',
        'bp-dob',
        'string'
    );
 
}
 

add_action( 'bp_register_admin_settings', 'bp_birthday_greetings_settings',9999 );
 

function bp_birthday_greetings_page_callback_section() {
    ?>
    <p class="description"><?php _e( 'Select DOB Field for which greetings will be sent.', 'bp-birthday-greetings' );?></p>
    <?php
}
 

function bp_birthday_greetings_field_callback() {
    $bp_birthday_option_value = bp_get_option( 'bp-dob' );
    ?>
    <select name="bp-dob">
    	<option>--SELECT FIELD--</option>
    	<?php 
		if( bp_has_profile() ) : 
			while ( bp_profile_groups() ) : bp_the_profile_group();
					while ( bp_profile_fields() ) : bp_the_profile_field(); 
						?>
						<option value="<?php bp_the_profile_field_id(); ?>" <?php if($bp_birthday_option_value==bp_get_the_profile_field_id()):?> selected <?php endif;?>> <?php bp_the_profile_field_name(); ?>
						</option>
					<?php 
					endwhile;
			endwhile;
		endif;
    	?>
    </select>
    <?php
}

