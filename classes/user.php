<?php

namespace WholeSite;

/**
 * If the following fields are found to be submitted via a POST then a user will be
 * registered with WholeSite
 *
 *  - wholesite_user_registration => 1
 *  - [optional] wholesite_user_registration_secondary_prefix_field => [value or array]
 */
class User {
	
	private $SERVICE_PATH = '/user';
	
	/**
	 * WholeSite Cart ID
	 */
	public $firstName;

	public $lastName;

	public $email;

	public $customData;

	public $secondaryPrefix = null;
	
	
	public function __construct( $params = array() ) {
		// filter data. Remove keys beginning with '_'
		$filtered_params = array();
		foreach( $params as $key => $val ) {
			if( strpos( $key, '_' ) !== 0 && $key != 'wholesite_user_registration' && $key != 'wholesite_user_registration_secondary_prefix_field' ) {
				$filtered_params[$key] = $val;
			}

			if( $key == 'wholesite_user_registration_secondary_prefix_field' && isset( $params[$val] ) ) {
				$this->secondaryPrefix = $params[$val];
			}
		}

		// set properties
		if( isset( $filtered_params['firstName'] ) ) {
			$this->firstName = sanitize_text_field( $filtered_params['firstName'] );
		}
		else if( isset( $filtered_params['first_name'] ) ) {
			$this->firstName = sanitize_text_field( $filtered_params['first_name'] );
		}	

		if( isset( $filtered_params['lastName'] ) ) {
			$this->lastName = sanitize_text_field( $filtered_params['lastName'] );
		}
		else if( isset( $filtered_params['last_name'] ) ) {
			$this->lastName = sanitize_text_field( $filtered_params['last_name'] );
		}	

		if( isset($params['email']) ) $this->email = sanitize_email( $params['email'] );

		$this->customData = array_map( 'esc_attr', $filtered_params );
	}
	
	/**
	 * Register user with set data
	 * @return mixed Response
	 */
	public function register() {
		// Validate data
		if( !isset($this->firstName) && !isset($this->lastName) && !isset($this->email) )
			return new \WP_Error( 'error', __( 'Required parameters \'firstName\', \'lastName\', \'email\'' ) );
		
		// request URL
		$url = WS_ENDPOINT . $this->SERVICE_PATH . '/register';

		// Create random password
		$tempPass = substr(md5(uniqid(rand(), true)), 0, 8);

		// Get configured prefix
		$prefix = \WholeSite\Utility::getSetting( 'user_prefix', 'wholesite_registrations' );

		// Encode user data
		$metaData = base64_encode( serialize( $this->customData ) );

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

		$responses = array();

		if( $this->secondaryPrefix != null ) {
			if( !is_array( $this->secondaryPrefix ) ) {
				$this->secondaryPrefix = array( $this->secondaryPrefix );
			}

			// Register user for each secondary prefix provided
			foreach( $this->secondaryPrefix as $sp ) {
				// convert to uppercase friendly string
				$sp = str_replace( " ", '_', strtoupper( trim( preg_replace("/[^a-zA-Z0-9_\-\s]+/", '', sanitize_text_field( $sp ) ) ) ) );
	 
	 			$data['username'] = $prefix . '_' . $sp . '_' . $tempPass;
	 			$data['registrationSource'] = $prefix . '_' . $sp;

				$request = new \WholeSite\Request( $url, $data );
			
				$responses[] = $request->send();
			}
		}
		else {
			$data['username'] = $prefix . '_' . $tempPass;
			$data['registrationSource'] = $prefix;

			$request = new \WholeSite\Request( $url, $data );

			$responses[] = $request->send();
		}

		return $responses;
	}
}
