<?php

namespace WholeSite;

class Response {
	
	/**
	 * Success of request [1|0]
	 */
	public $success;
	
	/**
	 * Time to process the request server side (seconds)
	 */
	public $requestTime;
	
	/**
	 * Response message
	 */
	public $message;
	
	/**
	 * Response code
	 */
	public $code;
	
	/**
	 * Main data payload of response
	 */
	public $data;
	
}
	