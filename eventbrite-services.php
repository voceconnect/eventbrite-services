<?php
/*
Plugin Name: Eventbrite Services
Plugin URI: http://voceplatforms.com/
Description: Provides Eventbrite service, widgets, and features to supporting themes.
Author: Voce Communications
Author URI: http://voceplatforms.com/
Version: 1.2.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Load Keyring first.
 */
require( 'keyring/keyring.php' );

/**
 * Load the Eventbrite extended Keyring class, and then all remaining Eventbrite code.
 */
function eventbrite_load_post_keyring() {

	require( 'eventbrite-keyring/eventbrite.php' );
	require( 'voce-settings-api/voce-settings-api.php' );
	require( 'eventbrite-api/eventbrite-api.php' );
	require( 'eventbrite-settings/eventbrite-settings.php' );
	require( 'eventbrite-widgets/eventbrite-widgets.php' );
	require( 'suggested-pages-setup/suggested-pages-setup.php' );
	require( 'tlc-transients/tlc-transients.php' );
	require( 'php-calendar/calendar.php' );

}
add_action( 'plugins_loaded', 'eventbrite_load_post_keyring' );

/**
 * Inform user where to set up Eventbrite in the admin.
 *
 * @uses Voce_Eventbrite_API::get_auth_service()
 * @uses admin_url()
 */
function eventbrite_setup_admin_notice() {
	if ( class_exists( 'Voce_Eventbrite_API' ) && ! Voce_Eventbrite_API::get_auth_service() ) {
		printf( '<div class="updated"><p>%s</p></div>',
			sprintf( __( 'You can set up Eventbrite Services under %s.', 'eventbrite' ),
				'<a href="' . admin_url( 'tools.php?page=eventbrite-page' ) . '">' . __( 'Tools &rarr; Eventbrite', 'eventbrite' ) . '</a>'
			)
		);
	}
}
add_action( 'admin_notices', 'eventbrite_setup_admin_notice' );