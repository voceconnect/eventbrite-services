<?php
/*
Plugin Name: Eventbrite Services
Plugin URI: http://voceplatforms.com/
Description: Provides Eventbrite service, widgets, and features to supporting themes.
Author: Voce Communications
Author URI: http://voceplatforms.com/
Version: 1.3.0
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
		$eventbrite_service = Voce_Eventbrite_API::get_service();
		// if on the eventbrite settings page check if keyring service is configured
		if ( is_a( $eventbrite_service, 'Keyring_Service_Eventbrite' ) && 'tools_page_eventbrite-page' === get_current_screen()->id && ( empty( $eventbrite_service->key ) || empty( $eventbrite_service->secret ) ) ) {
			printf( '<div class="error"><p>%s</p></div>',
				sprintf( __( 'Before attempting to "Connect with Eventbrite", you will need to create an Eventbrite API token and configure Keyring under %s.', 'eventbrite' ),
					'<a href="' . admin_url( 'tools.php?page=keyring&action=services' ) . '">' . __( 'Tools &rarr; Keyring &rarr; New Connection', 'eventbrite' ) . '</a>'
				)
			);
		} elseif ( 'tools_page_eventbrite-page' !== get_current_screen()->id ) {
			printf( '<div class="updated"><p>%s</p></div>',
				sprintf( __( 'You can set up Eventbrite Services under %s.', 'eventbrite' ),
					'<a href="' . admin_url( 'tools.php?page=eventbrite-page' ) . '">' . __( 'Tools &rarr; Eventbrite', 'eventbrite' ) . '</a>'
				)
			);
		}
	}
}
add_action( 'admin_notices', 'eventbrite_setup_admin_notice' );