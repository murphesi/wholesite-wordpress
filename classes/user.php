<?php

namespace WholeSite;

/**
 * If the following fields are found to be submitted via a POST then a user will be
 * registered with WholeSite
 *
 *  - wholesite_user_registration => 1
 *  - [optional] wholesite_user_registration_identifier_field => [string or array of strings]
 */
class User {
	
	private $SERVICE_PATH = '/user';
	
	/**
	 * First name
	 */
	public $firstName;

	/**
	 * Last name
	 */
	public $lastName;

	/**
	 * Email address
	 */
	public $email;

	/**
	 * A string or array of custom meta data for this user
	 */
	public $metaData;

	/**
	 * A secondary prefix to add to the username
	 * Registration identifier (Eg. Residential, Commercial)
	 */
	public $registrationIdentifier = null;
	
	
	public function __construct( $params = array() ) {
		// filter data. Remove keys beginning with '_'
		$filtered_params = array();
		foreach ( $params as $key => $val ) {
			if ( strpos( $key, '_' ) !== 0 && $key != 'wholesite_user_registration' && $key != 'wholesite_user_registration_identifier_field' ) {
				$filtered_params[ $key ] = $val;
			}
		}

		// set properties
		if ( isset( $filtered_params['firstName'] ) ) {
			$this->firstName = sanitize_text_field( $filtered_params['firstName'] );
		}
		else if ( isset( $filtered_params['first_name'] ) ) {
			$this->firstName = sanitize_text_field( $filtered_params['first_name'] );
		}	

		if ( isset( $filtered_params['lastName'] ) ) {
			$this->lastName = sanitize_text_field( $filtered_params['lastName'] );
		}
		else if ( isset( $filtered_params['last_name'] ) ) {
			$this->lastName = sanitize_text_field( $filtered_params['last_name'] );
		}	

		if ( isset($params['email']) ) $this->email = sanitize_email( $params['email'] );

		$this->metaData = array_map( 'esc_attr', $filtered_params );
	}
	
	/**
	 * Register user with set data
	 * @return mixed Response
	 */
	public function register() {
		// Validate data
		if ( ! isset( $this->firstName ) && ! isset( $this->lastName ) && ! isset( $this->email ) ) {
			return new \WP_Error( 'error', __( 'Required parameters \'firstName\', \'lastName\', \'email\'' ) );
		}
		
		// request URL
		$url = WS_ENDPOINT . $this->SERVICE_PATH . '/register';

		// Create random password
		$tempPass = substr(md5(uniqid(rand(), true)), 0, 8);

		// Get configured prefix
		$prefix = \WholeSite\Utility::getSetting( 'user_prefix', 'wholesite_registrations' );

		// Encode user data
		$metaData = base64_encode( serialize( $this->metaData ) );

		// Build request data
		$data = array(
				'firstName' => $this->firstName,
				'lastName' => $this->lastName,
				'email' => $this->email,
				'registrationSource' => '',

				'username' => null,
				'password' => $tempPass,

				'metaData' => $metaData
			);

		$response = null;

		if ( $this->registrationIdentifier != null ) {
			// convert sub identifier to uppercase friendly string
			$identifier = str_replace( " ", '_', strtoupper( trim( preg_replace("/[^a-zA-Z0-9_\-\s]+/", '', sanitize_text_field( $this->registrationIdentifier ) ) ) ) );
 
 			$data['username'] = $prefix . '_' . $identifier . '_' . $tempPass;
 			$data['registrationSource'] = $prefix . '_' . $identifier;
		}
		else {
			$data['username'] = $prefix . '_' . $tempPass;
			$data['registrationSource'] = $prefix;
		}

		$request = new \WholeSite\Request( $url, $data );

		$response = $request->send();
		
		if ( $this->registrationIdentifier != null ) {
			// Send to 3rd party if configured
			$options = get_option( 'wholesite_registration_forwarding', array() );

			foreach ( $options['forwards'] as $f ) {
				if ( strtolower( trim( $f['key'] ) ) == strtolower( trim( $this->registrationIdentifier ) ) ) {
					// send request
					$post_response = wp_remote_post( $f['url'], array( 'body' => $this->metaData ));

					if ( is_wp_error( $post_response ) ) {
						// Future: log errors
						// print_r($post_response);
					}
				}
			}
		}

		return $response;
	}
}
