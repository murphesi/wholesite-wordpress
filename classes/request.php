<?php

namespace WholeSite;

class Request {
	
	/**
	 * Request URL
	 */
	private $url;
	
	/**
	 * Request data (array)
	 */
	private $data;
	
	/**
	 * Method of request [POST|GET]
	 */
	private $method = 'POST';
	
	
	public function __construct( $url, $data ) {
		// ensure we send requests via HTTPS
		$this->url = str_replace( 'http://', 'https://', $url );
		$this->data = $data;
	}
	
	/**
	 * Send request to url with data
	 */
	public function send() {
		// required validations
		if( !WholeSite::isConfigured() )
			return new \WP_Error( 'error', __( 'Please configure WholeSite plugin. Go to \'Settings > WholeSite\'' ) );
		
		if( $this->method != 'POST' )
			return new \WP_Error( 'error', __( 'Request method not implemented.' ) );
		
		if( !$this->url )
			return new \WP_Error( 'error', __( 'Request url required.' ) );
		
		// get site and license configurations
		$site = \WholeSite\Utility::getSetting( 'site_id' );
		$license = \WholeSite\Utility::getSetting( 'license_key' );
		
		// make sure we have trailing slash to avoid unnecessary redirects
		$url = rtrim($this->url, '/') . '/';
		
		// send request
		$request = wp_remote_post( $url . '?s=' . $site . '&l=' . $license, array( 'body' => $this->data ));
		
		// build response object
		$response = new \WholeSite\Response();
		
		// check for request posting errors
		if( is_wp_error( $response ) ) {
			$response->success = 0;
			$response->message = $response->get_error_message();
			$response->code = 'REQUESTERR';
			$response->data = '';
		}
		else {	
			if ( $request['response']['code'] == '200' ) {
				$data = json_decode( $request['body'] );
				
				$response->requestTime = $data->requestTime;
				$response->success = $data->success ? 1 : 0;
				$response->message = isset($data->message) ? $data->message : '';
				$response->code = isset($data->code) ? $data->code : '';
				$response->data = isset($data->data) ? $data->data : '';
			}
			else {
				return new \WP_Error( 'error', __( $url . ' - ' . $request['response']['message'] ) );
			}
		}
		
		return $response;
	}
			
}
	