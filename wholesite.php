<?php
/**
 * Plugin Name: WholeSite
 * Short Name: wholesite
 * Description: Wordpress plugin to connect your WholeSite account.
 * Author: Mtex Media
 * Author URI: http://www.mtex.ca
 * Version: 0.0.1
 * Requires at least: 3.9
 * Tested up to: 4.0
 * Contributors: Chris Murphy
 * Requires: PHP 5 >= 5.3.0
 *  
 * 
 * 
 * Copyright (C) 2014 - Mtex Media Corporation - Chris Murphy
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses
 * 
 */
 
namespace WholeSite;
 
// define environment variables
define( 'WS_ENVIRONMENT', 'production' ); // [production|dev]
define( 'WS_VERSION', '0.0.1' );
define( 'WS_URI', plugin_dir_url( __FILE__ ) );
define( 'WS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WS_ENDPOINT', 'https://api.wholesite.com/1.0' );

// include classes
require_once( WS_PATH . 'classes/transaction.php' );
require_once( WS_PATH . 'classes/utility.php' );
require_once( WS_PATH . 'classes/request.php' );
require_once( WS_PATH . 'classes/response.php' );

class WholeSite {
	
	function __construct() {
		// admin hooks
		if( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}
	}
	
	
	/** PLUGIN SETUP **/
	
	/**
	 * Admin init hook
	 */
	function admin_init() {
		$this->register_settings();
	}
	
	/**
	 * Admin menu hook
	 */
	function admin_menu() {
		// add admin nav page
		add_menu_page( 'WholeSite Dashboard', 'WholeSite', 'activate_plugins', 'wholesite', array( $this, 'render_admin_page' ), 'dashicons-analytics', 30 );
		
		// add settings page
		add_submenu_page( 'wholesite', 'WholeSite Settings', 'Settings', 'activate_plugins', 'wholesite-settings', array( $this, 'render_settings_page' ) );  
	}
	
	/**
	 * Render admin page
	 */
	function render_admin_page() {
		?>
		<div class="wrap">
			<h2>WholeSite Dashboard</h2>
			<br/>
			Transaction overview coming soon.
			
		</div>
		<?
	}
	
	/**
	 * Register settings, sections & fields
	 */
	function register_settings() {
		register_setting( 'wholesite_settings', 'wholesite_settings', array( $this, 'sanitize_settings' ) );
	
		add_settings_section( 'wholesite_main', 'Site Settings', array( $this, 'settings_help_site' ), 'wholesite-settings' );
		
		add_settings_field( 'site_id', 'Site ID', array( $this, 'render_site_id_setting'), 'wholesite-settings', 'wholesite_main' );
		add_settings_field( 'license_key', 'License Key', array( $this, 'render_license_key_setting') , 'wholesite-settings', 'wholesite_main' );
	}
	
	/**
	 * Render settings page
	 */
	function render_settings_page() {
		?>
		<div class="wrap">
			<h2>WholeSite Settings</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wholesite_settings' );
				
				do_settings_sections( 'wholesite-settings' );
				
				submit_button(); 
				?>
			</form>
		</div>
		<?
	}
	
	/**
	 * Render help for site settings
	 */
	function settings_help_site() {
		echo '<p>Supply the Site ID and License provided by WholeSite.</p>';
	}
	
	/**
	 * Render Site ID Setting
	 */
	function render_site_id_setting() {
		$options = get_option( 'wholesite_settings' );
		$val = isset( $options['site_id'] ) ? $options['site_id'] : '';
		echo '<input id="site_id" type="text" class="regular-text" name="wholesite_settings[site_id]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Render License Key Setting
	 */
	function render_license_key_setting() {
		$options = get_option( 'wholesite_settings' );
		$val = isset( $options['license_key'] ) ? $options['license_key'] : '';
		echo '<input id="license_key" type="text" class="regular-text" name="wholesite_settings[license_key]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Sanitize settings
	 */
	function sanitize_settings( $input ) {
		$valid = array();

		// Site settings		
		if ( isset( $input['site_id'] ) ) {
			$valid['site_id'] = sanitize_text_field( $input['site_id'] );
		}
		
		if ( isset( $input['license_key'] ) ) {
			$valid['license_key'] = sanitize_text_field( $input['license_key'] );
		}

		return $valid;
	}
	
	/**
	 * Check to see if the site and license has been configured
	 * @return boolean
	 */
	public static function isConfigured() {
		return ( \WholeSite\Utility::getSetting( 'site_id' ) && \WholeSite\Utility::getSetting( 'license_key' ) );
	}
	
	
	/** FUNCTIONS **/
	
	/**
	 * Process a payment transaction
	 * @param array $params 
	 * @return object Response from transaction
	 */
	public function processTransaction( $params = array() ) {
		if( self::isConfigured() ) {
			$t = new \WholeSite\Transaction( $params );
			return $t->process();
		}
		else {
			return new \WP_Error( 'error', __( 'Please configure WholeSite plugin. Go to \'Settings > WholeSite\'' ) );
		}
	}
}

$wholesite = new WholeSite();
