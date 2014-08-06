<?php

/**
 * Eventbrite service definition for Keyring.
 * http://developer.eventbrite.com/
 *
 * @package eventbrite
 */

class Keyring_Service_Eventbrite extends Keyring_Service_OAuth2 {
	const NAME  = 'eventbrite';
	const LABEL = 'Eventbrite';

	function __construct() {
		parent::__construct();

		add_action( 'keyring_connection_verified', array( $this, 'keyring_connection_verified' ), 1, 3 );
		add_action( 'keyring_connection_deleted', array( $this, 'keyring_connection_deleted' ), 1, 2 );

		$this->set_endpoint( 'authorize',    'https://www.eventbrite.com/oauth/authorize', 'GET' );
		$this->set_endpoint( 'access_token', 'https://www.eventbrite.com/oauth/token', 'POST' );
		$this->set_endpoint( 'self',         'https://www.eventbriteapi.com/v3/users/me',   'GET' );

		// Enable "basic" UI for entering key/secret
		if ( ! KEYRING__HEADLESS_MODE ) {
			add_action( 'keyring_eventbrite_manage_ui', array( $this, 'basic_ui' ) );
			add_filter( 'keyring_eventbrite_basic_ui_intro', array( $this, 'basic_ui_intro' ) );
		}
		$creds = $this->get_credentials();

		if (
			defined( 'KEYRING__EVENTBRITE_ID' )
		&&
			defined( 'KEYRING__EVENTBRITE_KEY' )
		&&
			defined( 'KEYRING__EVENTBRITE_SECRET' )
		) {
			$this->app_id  = KEYRING__EVENTBRITE_ID;
			$this->key     = KEYRING__EVENTBRITE_KEY;
			$this->secret  = KEYRING__EVENTBRITE_SECRET;
		} elseif ( $creds ) {
			$this->app_id  = $creds['app_id'];
			$this->key     = $creds['key'];
			$this->secret  = $creds['secret'];
		}

		$this->consumer = new OAuthConsumer( $this->key, $this->secret, $this->callback_url );
		$this->signature_method = new OAuthSignatureMethod_HMAC_SHA1;

		$this->authorization_header    = 'Bearer';
		$this->authorization_parameter = false;
	}

	function keyring_connection_verified( $service, $id, $request_token ) {
		if ( 'eventbrite' != $service || ( isset( $request_token->token['for'] ) && 'eventbrite' != $request_token->token['for'] ) )
			return;
		if ( is_multisite() )
			update_blog_option( absint( $request_token->token['blog_id'] ), 'eventbrite_token', array( $id ) );
		else
			update_option( 'eventbrite_token', array( $id ) );
	}

	function keyring_connection_deleted( $service, $request ) {
		if ( 'eventbrite' != $service )
			return;
		if ( is_multisite() )
			delete_blog_option( absint( $request['blog'] ), 'eventbrite_token' );
		else
			delete_option( 'eventbrite_token' );
	}

	function basic_ui_intro() {
		echo '<p>' . sprintf( __( "To get started, <a href='https://www.eventbrite.com/api/key'>register an OAuth client on Eventbrite</a>. The most important setting is the <strong>OAuth redirect_uri</strong>, which should be set to <code>%s</code>. You can set the other values to whatever you like.", 'eventbrite-parent' ), esc_url( Keyring_Util::admin_url( 'eventbrite', array( 'action' => 'verify' ) ) ) ) . '</p>';
		echo '<p>' . __( "Once you've saved those changes, copy the <strong>APPLICATION KEY</strong> value into the <strong>API Key</strong> field, then click the 'Show' link next to the <strong>OAuth client secret</strong>, copy the value into the <strong>API Secret</strong> field and click save (you don't need an App ID value for Eventbrite).", 'eventbrite-parent' ) . '</p>';
	}

	function build_token_meta( $token ) {
		$this->set_token(
			new Keyring_Access_Token(
				$this->get_name(),
				$token['access_token'],
				array()
			)
		);

		$response = $this->request( $this->self_url, array( 'method' => $this->self_method ) );
		$meta = array();
		if ( ! Keyring_Util::is_error( $response ) ) {
			if ( isset( $response->emails[0]->email ) )
				$meta['username'] = $response->emails[0]->email;

			if ( isset( $response->id ) )
				$meta['user_id'] = $response->id;

			$name = array();
			$first_name = isset( $response->first_name ) ? $response->first_name : false;
			if ( $first_name )
				$name[] = $first_name;

			$last_name = isset( $response->last_name ) ? $response->last_name : false;
			if ( $last_name )
				$name[] = $last_name;

			if ( $name )
				$meta['name'] = implode( ' ', $name );
		}

		return apply_filters( 'keyring_access_token_meta', $meta, self::NAME, $token, array(), $this );
	}

	function get_display( Keyring_Access_Token $token ) {
		return $token->get_meta( 'name' );
	}
}

add_action( 'keyring_load_services', array( 'Keyring_Service_Eventbrite', 'init' ) );
