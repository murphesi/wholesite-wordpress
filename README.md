# WholeSite - Wordpress Plugin

 Contributors: Chris Murphy
 Tested up to: 3.9.2
 Stable tag: 0.0.2
 License: GPLv3
 License URI: http://www.gnu.org/licenses

## Description

 Wordpress plugin to connect your WholeSite account.

## Installation

 Install in plugins folder and activate via control panel.

## Example

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

== 0.0.2 ==
* Move settings into submenu under WholeSite Menu
* Fix bug where you couldn't save settings

== 0.0.1 ==
* Initial creation