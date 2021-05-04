<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/agusnurwanto/
 * @since      1.0.0
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/includes
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Simda_Bmd_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'simda-bmd',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
