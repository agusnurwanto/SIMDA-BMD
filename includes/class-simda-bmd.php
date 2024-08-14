<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/agusnurwanto/
 * @since      1.0.0
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/includes
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Simda_Bmd {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Simda_Bmd_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SIMDA_BMD_VERSION' ) ) {
			$this->version = SIMDA_BMD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'simda-bmd';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simda_Bmd_Loader. Orchestrates the hooks of the plugin.
	 * - Simda_Bmd_i18n. Defines internationalization functionality.
	 * - Simda_Bmd_Admin. Defines all hooks for the admin area.
	 * - Simda_Bmd_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simda-bmd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simda-bmd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simda-bmd-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-simda-bmd-public.php';

		$this->loader = new Simda_Bmd_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simda_Bmd_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Simda_Bmd_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Simda_Bmd_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('carbon_fields_register_fields', $plugin_admin, 'crb_attach_simda_options');
		$this->loader->add_action('wp_ajax_migrasi_data',  $plugin_admin, 'migrasi_data');
		$this->loader->add_action('wp_ajax_get_skpd_mapping',  $plugin_admin, 'get_skpd_mapping');
		$this->loader->add_action('wp_ajax_sql_migrate_ebmd',  $plugin_admin, 'sql_migrate_ebmd');
		
		add_shortcode('mapping_skpd',  array($plugin_admin, 'mapping_skpd' ));
		add_shortcode('mapping_tanah',  array($plugin_admin, 'mapping_tanah' ));
		add_shortcode('mapping_mesin',  array($plugin_admin, 'mapping_mesin' ));
		add_shortcode('mapping_bangunan',  array($plugin_admin, 'mapping_bangunan' ));
		add_shortcode('mapping_jalan_irigrasi',  array($plugin_admin, 'mapping_jalan_irigrasi' ));
		add_shortcode('mapping_aset_tetap',  array($plugin_admin, 'mapping_aset_tetap' ));
		add_shortcode('mapping_konstruksi_dalam_pengerjaan',  array($plugin_admin, 'mapping_konstruksi_dalam_pengerjaan' ));

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Simda_Bmd_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action('wp_ajax_get_mapping_rek',  $plugin_public, 'get_mapping_rek');
		$this->loader->add_action('wp_ajax_import_excel_mapping_rek', $plugin_public, 'import_excel_mapping_rek');
		$this->loader->add_action('wp_ajax_export_data', $plugin_public, 'export_data');

		add_shortcode('halaman_mapping_rek_tanah',  array($plugin_public, 'halaman_mapping_rek_tanah' ));
		add_shortcode('halaman_mapping_rek_kontruksi',  array($plugin_public, 'halaman_mapping_rek_kontruksi' ));
		add_shortcode('halaman_mapping_rek_aset_tetap',  array($plugin_public, 'halaman_mapping_rek_aset_tetap' ));
		add_shortcode('halaman_mapping_rek_bangunan',  array($plugin_public, 'halaman_mapping_rek_bangunan' ));
		add_shortcode('halaman_mapping_rek_jalan',  array($plugin_public, 'halaman_mapping_rek_jalan' ));
		add_shortcode('halaman_mapping_rek_mesin',  array($plugin_public, 'halaman_mapping_rek_mesin' ));

		add_shortcode('halaman_laporan_kib_a',  array($plugin_public, 'halaman_laporan_kib_a' ));		
		add_shortcode('halaman_laporan_kib_b',  array($plugin_public, 'halaman_laporan_kib_b' ));		
		add_shortcode('halaman_laporan_kib_c',  array($plugin_public, 'halaman_laporan_kib_c' ));		
		add_shortcode('halaman_laporan_kib_d',  array($plugin_public, 'halaman_laporan_kib_d' ));		
		add_shortcode('halaman_laporan_kib_e',  array($plugin_public, 'halaman_laporan_kib_e' ));		
		add_shortcode('halaman_laporan_kib_f',  array($plugin_public, 'halaman_laporan_kib_f' ));		

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Simda_Bmd_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
