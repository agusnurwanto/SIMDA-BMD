<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto/
 * @since      1.0.0
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/admin
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Simda_Bmd_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simda_Bmd_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simda_Bmd_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simda-bmd-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simda_Bmd_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simda_Bmd_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simda-bmd-admin.js', array( 'jquery' ), $this->version, false );

	}

	function crb_attach_simda_options(){
		global $wpdb;
		$basic_options_container = Container::make( 'theme_options', __( 'SIMDA BMD' ) )
			->set_page_menu_position( 4 )
	        ->add_fields( array(
	        	Field::make( 'html', 'crb_simda_bmd_referensi_html' )
	            	->set_html( 'Referensi: <a target="_blank" href="https://github.com/agusnurwanto/SIMDA-BMD">https://github.com/agusnurwanto/SIMDA-BMD</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_koneksi_html' )
	            	->set_html( '<b>Configurasi Koneksi Database SIMDA BMD ( Status: <span style="color: red;">Belum Terkoneksi</span> )</b>' ),
	            Field::make( 'text', 'crb_simda_bmd_ip', 'IP server atau Hostname' ),
	            Field::make( 'text', 'crb_simda_bmd_db', 'Nama Database' ),
	            Field::make( 'text', 'crb_simda_bmd_user', 'Username' ),
	            Field::make( 'text', 'crb_simda_bmd_pass', 'Password' ),
	        	Field::make( 'html', 'crb_simda_bmd_koneksi_spbmd_html' )
	            	->set_html( '<b>Configurasi Koneksi Database SPBMD ( Status: <span style="color: red;">Belum Terkoneksi</span> )</b>' ),
	            Field::make( 'text', 'crb_spbmd_ip', 'IP server atau Hostname' ),
	            Field::make( 'text', 'crb_spbmd_db', 'Nama Database' ),
	            Field::make( 'text', 'crb_spbmd_user', 'Username' ),
	            Field::make( 'text', 'crb_spbmd_pass', 'Password' )
	        ) );
	    Container::make( 'theme_options', __( 'Mapping SKPD' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( array(
		        Field::make( 'text', 'crb_simda_bmd_sub_unit', 'Nama Sub Unit di SPBMD: ...' )
	        ) );
	    Container::make( 'theme_options', __( 'Mapping Rekening' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( array(
		        Field::make( 'text', 'crb_spbmd_rek', 'Nama Aset di SPBMD: ...' )
	        ) );
	    Container::make( 'theme_options', __( 'Migrasi Data' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( array(
	        	Field::make( 'html', 'crb_simda_bmd_referensi_html' )
	            	->set_html( 'Referensi: <a target="_blank" href="https://github.com/agusnurwanto/SIMDA-BMD">https://github.com/agusnurwanto/SIMDA-BMD</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_a' )
	            	->set_html( 'Migrasi table KD_KIB_A (Aset Tanah)' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_b' )
	            	->set_html( 'Migrasi table KD_KIB_B (Aset Mesin)' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_c' )
	            	->set_html( 'Migrasi table KD_KIB_C (Aset Bangunan)' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_d' )
	            	->set_html( 'Migrasi table KD_KIB_D (Aset Jalan Irigrasi)' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_e' )
	            	->set_html( 'Migrasi table KD_KIB_E (Aset Tetap seperti buku, tanaman, hewan)' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_f' )
	            	->set_html( 'Migrasi table KD_KIB_F (Aset Kontruksi Dalam Pengerjaan)' )
	        ) );
	}

}
