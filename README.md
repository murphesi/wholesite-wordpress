# WholeSite - Wordpress Plugin

 Contributors: Chris Murphy
 Tested up to: Wordpress 4.6
 Stable tag: 0.0.8
 License: GPLv3
 License URI: http://www.gnu.org/licenses

## Description

 Wordpress plugin to connect your WholeSite account for:
 	- Transaction processing
 	- User registration

## Installation

 Install in plugins folder and activate via control panel.

## User Registration

Configure a prefix to be added to each username:
WholeSite > User Registrations

Add these hidden fields to your registration form:

```
<input type="hidden" name="wholesite_user_registration" value="1" />
```

Optionally add this hidden field to use a field or dropdown for a secondary prefix:

```
<input type="hidden" name="wholesite_user_registration_identifier_field" value="insert_field_or_dropdown_name" />
```

## Credit Transaction Example

<pre>
 //~~~~~~~~~~~~~~ TRANSACTION SAMPLE ~~~~~~~~~~~~~~//

 // Build transaction data
 $transactionData = array(
		//~~~~~TEST MODE~~~~~~~~~~~~~
		'test'				=> true,
		//~~~~~TEST MODE~~~~~~~~~~~~~
		
		'cart' 				=> 'ffffffff-ffff-ffff-ffff-ffffffffffff', // required
		'orderId' 			=> 'ACMECO' . date( 'dmyGis' ),
		'customerId' 		=> 'John Doe',
		
		'paymentType'		=> 'credit',

		'cardType' 			=> 'VISA',
		'cardholder' 		=> 'John Doe', // required
		'pan' 				=> str_replace(array("\s", "-", " "), "", trim( '4242424242424242' )), // required
		'expiryMonth'		=> '08', // required
		'expiryYear'		=> '24', // required
		
		'amount'			=> number_format(0.04, 2, '.', ''), // required
		'taxes'				=> array(
									array( "percent" => 13, "description" => "HST" )	
								),
		'designation'		=> 'Auction',
		'anonymous'			=> false,
		'inhonName'			=> 'Honour Name',
		'inmemName'			=> 'Memory Name',
		'howDidYouHear'		=> 'flyer',
		'comment'			=> 'no comment',
		'taxReceipt'		=> 'immediate',
		'source'			=> 'front page banner',
		'recurrence'		=> 'none',
		
		'billFirstName'		=> 'John',
		'billLastName'		=> 'Doe',
		'billCompanyName'	=> 'DM Paper Co.',
		'billAddress1'		=> '124 Paper Rd.',
		'billApt'			=> '101',
		'billCity'			=> 'Somewhere City',
		'billProvState'		=> 'Somewhere Province',
		'billCountry'		=> 'CA',
		'billCode'			=> 'L2A 4P5',
		'billPhone'			=> '1-555-555-5555',
		'billPhoneExt'		=> '123',
		'billEmail'			=> 'test@example.com',
		
		'customData'		=> array(
									"promoCode" => "DISCOUNT99"	
								)
	);
	
 // Process transaction
 $wholesite = new \WholeSite\WholeSite();
 $response = $wholesite->processTransaction( $transactionData );

 // Check for errors
 if( is_wp_error( $response ) ) {
	echo $response->get_error_message();
 }
 else {	
	print_r( $response );
	
	// show your messaging based on response
 }

 //~~~~~~~~~~~~~~ TRANSACTION SAMPLE ~~~~~~~~~~~~~~//
</pre>

## Debit Transaction Example

<pre>
 //~~~~~~~~~~~~~~ TRANSACTION SAMPLE ~~~~~~~~~~~~~~//

 // Build transaction data
 $transactionData = array(
		//~~~~~TEST MODE~~~~~~~~~~~~~
		'test'				=> true,
		//~~~~~TEST MODE~~~~~~~~~~~~~
		
		'cart' 				=> 'ffffffff-ffff-ffff-ffff-ffffffffffff', // required
		'orderId' 			=> 'ACMECO' . date( 'dmyGis' ),
		'customerId' 		=> 'John Doe',
		
		'paymentType'		=> 'debit',

		'track2' 			=> '3728024906540591206=0609AAAAAAAAAAAAA', // required
		
		'amount'			=> number_format(0.04, 2, '.', ''), // required
		'taxes'				=> array(
									array( "percent" => 13, "description" => "HST" )	
								),
		'designation'		=> 'Auction',
		'anonymous'			=> false,
		'inhonName'			=> 'Honour Name',
		'inmemName'			=> 'Memory Name',
		'howDidYouHear'		=> 'flyer',
		'comment'			=> 'no comment',
		'taxReceipt'		=> 'immediate',
		'source'			=> 'front page banner',
		'recurrence'		=> 'none',
		
		'billFirstName'		=> 'John',
		'billLastName'		=> 'Doe',
		'billCompanyName'	=> 'DM Paper Co.',
		'billAddress1'		=> '124 Paper Rd.',
		'billApt'			=> '101',
		'billCity'			=> 'Somewhere City',
		'billProvState'		=> 'Somewhere Province',
		'billCountry'		=> 'CA',
		'billCode'			=> 'L2A 4P5',
		'billPhone'			=> '1-555-555-5555',
		'billPhoneExt'		=> '123',
		'billEmail'			=> 'test@example.com',
		
		'customData'		=> array(
									"promoCode" => "DISCOUNT99"	
								)
	);
	
 // Process transaction
 $wholesite = new \WholeSite\WholeSite();
 $response = $wholesite->processTransaction( $transactionData );

 // Check for errors
 if( is_wp_error( $response ) ) {
	echo $response->get_error_message();
 }
 else {	
	print_r( $response );
	
	// show your messaging based on response
 }

 //~~~~~~~~~~~~~~ TRANSACTION SAMPLE ~~~~~~~~~~~~~~//
</pre>

## Changelog

== 0.0.8 ==
* Add debit acceptance.

== 0.0.7 ==
* Increase request timeout to 30 seconds
* Add user registration forwarding option. POST registration detail to external url.
* Change 'wholesite_user_registration_secondary_prefix_field' to more meaningful 'wholesite_user_registration_identifier_field'

== 0.0.6 ==
* Update admin menu verbage.
* Add user registration processing.

== 0.0.5 ==
* Added email confirmations for successful transactions.

== 0.0.4 ==
* Add support for CVV value.

== 0.0.3 ==
* Check for WP error on post data response.

== 0.0.2 ==
* Move settings into submenu under WholeSite Menu
* Fix bug where you couldn't save settings

== 0.0.1 ==
* Initial creation
