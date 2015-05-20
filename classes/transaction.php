<?php

namespace WholeSite;

class Transaction {
	
	private $SERVICE_PATH = '/transaction';
	
	/**
	 * WholeSite Cart ID
	 */
	public $cart;
	
	/**
	 * Custom Order ID
	 * e.g. 'ACME20140801235959'
	 */
	public $orderId;
	
	/**
	 * Customer ID
	 * e.g. 'John Doe'
	 */
	public $customerId;
	
	/**
	 * Credit Card Type [VISA|MASTERCARD|DISCOVER|AMEX]
	 * e.g. 'VISA'
	 */
	public $cardType;
	
	/**
	 * Card Holder Full Name
	 */
	public $cardholder;
	
	/**
	 * Credit Card PAN Number
	 */
	public $pan;
	
	/**
	 * Credit Card Validation Number (3-4 digits)
	 * [optional]
	 */
	public $cvv = null;
	
	/**
	 * Credit Card Expiry Month (2 Digits)
	 * e.g. 09
	 */
	public $expiryMonth;
	
	/**
	 * Credit Card Expiry Year (2 Digits)
	 * e.g. 18
	 */
	public $expiryYear;
	
	/**
	 * Transaction Amount
	 * e.g. 132.99
	 */
	public $amount;
	
	/**
	 * Taxes - An array of taxes that have been applied to $amount
	 * e.g. array( array( "percent" => 13, "description" => "HST" ), array( ... ) )
	 */
	public $taxes;
	
	/**
	 * Specify where payment should be designated
	 * e.g. 'Silent Auction'
	 */
	public $designation;
	
	/**
	 * Should the payment provider remain anonymous ( Donor ) [true|false]
	 */
	public $anonymous;
	
	/**
	 * In honor of name
	 */
	public $inhonName;
	
	/**
	 * In memory of name
	 */
	public $inmemName;
	
	/**
	 * How did you hear about us
	 */
	public $howDidYouHear;
	
	/**
	 * Comments
	 */
	public $comment;
	
	/**
	 * Type of tax receipt to issue.
	 * e.g. 'End of month'
	 */
	public $taxReceipt;
	
	/**
	 * Source of transaction
	 * e.g. 'online-campaign?utm_source=...'
	 */
	public $source;
	
	/**
	 * Payment recurrence frequency (optional) [monthly|yearly]
	 */
	public $recurrence;
	
	/**
	 * IP Address (Also used for Blacklisting)
	 */
	public $ip;
	
	/**
	 * Billing first name
	 */
	public $billFirstName;
	
	/**
	 * Billing last name
	 */
	public $billLastName;
	
	/**
	 * Billing company name
	 */
	public $billCompanyName;
	
	/**
	 * Billing address 1
	 */
	public $billAddress1;
	
	/**
	 * Billing Apt
	 */
	public $billApt;
	
	/**
	 * Billing city
	 */
	public $billCity;
	
	/**
	 * Billing Province / State
	 */
	public $billProvState;
	
	/**
	 * Billing country
	 */
	public $billCountry;
	
	/**
	 * Billing Postal / Zip Code
	 */
	public $billCode;
	
	/**
	 * Billing phone number
	 */
	public $billPhone;
	
	/**
	 * Biling phone extension
	 */
	public $billPhoneExt;
	
	/**
	 * Billing email address
	 */
	public $billEmail;
	
	/**
	 * Custom array of Data
	 * e.g. array( "promoCode" => "DISCOUNT99" )
	 */
	public $customData;
	
	/**
	 * Test mode for integration testing [true|false]
	 * 
	 * If set true transactions are processed through a test suite and do not appear 
	 * in your live/production payment gateway environment
	 */
	public $test = false;
	
	
	
	public function __construct( $params = array() ) {
		// set transaction properties
		if( isset($params['cart']) ) $this->cart = sanitize_text_field( $params['cart'] );
		if( isset($params['orderId']) ) $this->orderId = sanitize_text_field( $params['orderId'] );
		if( isset($params['customerId']) ) $this->customerId = sanitize_text_field( $params['customerId'] );
		if( isset($params['cardType']) ) $this->cardType = sanitize_text_field( $params['cardType'] );
		if( isset($params['cardholder']) ) $this->cardholder = sanitize_text_field( $params['cardholder'] );
		if( isset($params['pan']) ) $this->pan = sanitize_text_field( $params['pan'] );
		if( isset($params['cvv']) ) $this->cvv = sanitize_text_field( $params['cvv'] );
		if( isset($params['expiryMonth']) ) $this->expiryMonth = sanitize_text_field( $params['expiryMonth'] );
		if( isset($params['expiryYear']) ) $this->expiryYear = sanitize_text_field( $params['expiryYear'] );
		if( isset($params['amount']) ) $this->amount = number_format($params['amount'], 2, '.', '');
		if( isset($params['taxes']) ) $this->taxes = $params['taxes'];
		if( isset($params['designation']) ) $this->designation = sanitize_text_field( $params['designation'] );
		if( isset($params['anonymous']) ) $this->anonymous = $params['anonymous'];
		if( isset($params['inhonName']) ) $this->inhonName = sanitize_text_field( $params['inhonName'] );
		if( isset($params['inmemName']) ) $this->inmemName = sanitize_text_field( $params['inmemName'] );
		if( isset($params['howDidYouHear']) ) $this->howDidYouHear = sanitize_text_field( $params['howDidYouHear'] );
		if( isset($params['comment']) ) $this->comment = sanitize_text_field( $params['comment'] );
		if( isset($params['taxReceipt']) ) $this->taxReceipt = sanitize_text_field( $params['taxReceipt'] );
		if( isset($params['source']) ) $this->source = sanitize_text_field( $params['source'] );
		if( isset($params['recurrence']) ) $this->recurrence = sanitize_text_field( $params['recurrence'] );
		$this->ip = $_SERVER['REMOTE_ADDR'];
		if( isset($params['billFirstName']) ) $this->billFirstName = sanitize_text_field( $params['billFirstName'] );
		if( isset($params['billLastName']) ) $this->billLastName = sanitize_text_field( $params['billLastName'] );
		if( isset($params['billCompanyName']) ) $this->billCompanyName = sanitize_text_field( $params['billCompanyName'] );
		if( isset($params['billAddress1']) ) $this->billAddress1 = sanitize_text_field( $params['billAddress1'] );
		if( isset($params['billApt']) ) $this->billApt = sanitize_text_field( $params['billApt'] );
		if( isset($params['billCity']) ) $this->billCity = sanitize_text_field( $params['billCity'] );
		if( isset($params['billProvState']) ) $this->billProvState = sanitize_text_field( $params['billProvState'] );
		if( isset($params['billCountry']) ) $this->billCountry = sanitize_text_field( $params['billCountry'] );
		if( isset($params['billCode']) ) $this->billCode = sanitize_text_field( $params['billCode'] );
		if( isset($params['billPhone']) ) $this->billPhone = sanitize_text_field( $params['billPhone'] );
		if( isset($params['billPhoneExt']) ) $this->billPhoneExt = sanitize_text_field( $params['billPhoneExt'] );
		if( isset($params['billEmail']) ) $this->billEmail = sanitize_email( $params['billEmail'] );
		if( isset($params['customData']) ) $this->customData = $params['customData'];
		if( isset($params['test']) ) $this->test = ($params['test']);
	}
	
	/**
	 * Complete transaction with set data
	 * @return mixed Response
	 */
	public function process() {
		// validate data
		if( !isset($this->cart) )
			return new \WP_Error( 'error', __( 'Required parameter \'cart\'' ) );
		
		// build request data
		$data = array(
				'test' => $this->test,
				'cart' => $this->cart,
				'orderId' => $this->orderId,
				'customerId' => $this->customerId,
				'cardType' => $this->cardType,
				'cardholder' => $this->cardholder,
				'pan' => $this->pan,
				'cvv' => $this->cvv,
				'expiryMonth' => $this->expiryMonth,
				'expiryYear' => $this->expiryYear,
				'amount' => $this->amount,
				'taxes' => base64_encode( serialize( $this->taxes ) ),
				'designation' => $this->designation,
				'anonymous' => $this->anonymous,
				'inhonName' => $this->inhonName,
				'inmemName' => $this->inmemName,
				'howDidYouHear' => $this->howDidYouHear,
				'comment' => $this->comment,
				'taxReceipt' => $this->taxReceipt,
				'source' => $this->source,
				'recurrence' => $this->recurrence,
				'ip' => $this->ip,
				'billFirstName' => $this->billFirstName,
				'billLastName' => $this->billLastName,
				'billCompanyName' => $this->billCompanyName,
				'billAddress1' => $this->billAddress1,
				'billApt' => $this->billApt,
				'billCity' => $this->billCity,
				'billProvState' => $this->billProvState,
				'billCountry' => $this->billCountry,
				'billCode' => $this->billCode,
				'billPhone' => $this->billPhone,
				'billPhoneExt' => $this->billPhoneExt,
				'billEmail' => $this->billEmail,
				'customData' => base64_encode( serialize( $this->customData ) )
			);
		
		// request URL
		$url = WS_ENDPOINT . $this->SERVICE_PATH . '/process';
		
		// create request
		$request = new \WholeSite\Request( $url, $data );
		
		// send request
		$response = $request->send();
		
		// process response
		return $response;
	}
	
}
