<?php

namespace WholeSite;

class Utility {
	
	/**
	 * Return a setting set by this plugin
	 * @param string $name
	 * @return mixed (null if not set)
	 */
	public static function getSetting( $name ) {
		$options = get_option( 'wholesite_settings' );
		
		if( $options !== FALSE && isset( $options[$name] ) )
			return $options[$name];
		else
			return null;
	}
	
}
	