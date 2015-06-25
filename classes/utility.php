<?php

namespace WholeSite;

class Utility {
	
	/**
	 * Return a setting set by this plugin
	 * @param string $name
	 * @return mixed (null if not set)
	 */
	public static function getSetting( $name, $option_name = 'wholesite_settings' ) {
		$options = get_option( $option_name );
		
		if( $options !== FALSE && isset( $options[$name] ) )
			return $options[$name];
		else
			return null;
	}
	
}
	