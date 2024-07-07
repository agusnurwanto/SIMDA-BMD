<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto/
 * @since      1.0.0
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Simda_Bmd
 * @subpackage Simda_Bmd/public
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Simda_Bmd_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simda-bmd-public.css', array(), $this->version, 'all' );
		wp_enqueue_style($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'css/datatables.min.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simda-bmd-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'js/datatables.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'chart', plugin_dir_url(__FILE__) . 'js/chart.min.js', array('jquery'), $this->version, false);
		wp_localize_script( $this->plugin_name, 'ajax', array(
		    'url' => admin_url( 'admin-ajax.php' )
		));

	}

	public function halaman_mapping_rek_tanah($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-tanah.php';
	}

	public function halaman_mapping_rek_kontruksi($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-kontruksi.php';
	}

	public function halaman_mapping_rek_aset_tetap($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-aset-tetap.php';
	}

	public function halaman_mapping_rek_bangunan($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-bangunan.php';
	}

	public function halaman_mapping_rek_jalan($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-jalan.php';
	}

	public function halaman_mapping_rek_mesin($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-mapping-rek-mesin.php';
	}

	public function halaman_laporan_kib_a($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-a.php';
	}

	public function halaman_laporan_kib_b($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-b.php';
	}

	public function halaman_laporan_kib_c($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-c.php';
	}

	public function halaman_laporan_kib_d($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-d.php';
	}

	public function halaman_laporan_kib_e($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-e.php';
	}

	public function halaman_laporan_kib_f($atts){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once SIMDA_BMD_PLUGIN_PATH . 'public/partials/wp-simda-bmd-halaman-laporan-kib-f.php';
	}

	public function import_excel_mapping_rek(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
				'message'	=> 'Berhasil import excel!'
		);
		if (!empty($_POST)) {
			$ret['data'] = array(
				'insert' => array(), 
				'update' => array(),
				'error' => array()
			);
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SIMDA_BMD_APIKEY )) {
				$data = json_decode(stripslashes($_POST['import_data']), true); 
				if (!empty($_POST['tipe_rekening'])) {
					$tipe_rekening = $_POST['tipe_rekening'];
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Tipe Rekening kosong!';
				}
				// untuk mengatur tabel sesuai tipe rekening
				$nama_tabel = array(
					"mapping_rekening_tanah" => "data_mapping_rek_a",
					"mapping_rekening_mesin" => "data_mapping_rek_b",
					"mapping_rekening_bangunan" => "data_mapping_rek_c",
					"mapping_rekening_jalan" => "data_mapping_rek_d",
					"mapping_rekening_aset_tetap" => "data_mapping_rek_e",
					"mapping_rekening_kontruksi" => "data_mapping_rek_f"
				);

				foreach ($data as $key => $val) {
					$cek_id = $wpdb->get_var($wpdb->prepare("
	            		SELECT 
	            			id
	            		from $nama_tabel[$tipe_rekening] 
	            		where kode_rekening_spbmd=%s
	            	", $val['kode_rekening_spbmd']));
					$wpdb->last_error = "";
					$data_db = array(
	                    'kode_rekening_spbmd'=> $val['kode_rekening_spbmd'],
						'uraian_rekening_spbmd'=> $val['uraian_rekening_spbmd'],
	                    'kode_rekening_ebmd'=> $val['kode_rekening_ebmd'],
	                    'uraian_rekening_ebmd'=> $val['uraian_rekening_ebmd'],
	                    'update_at' => current_time('mysql'),
						'active' => 1
					);
					if(empty($cek_id)){
						$wpdb->insert("$nama_tabel[$tipe_rekening]", $data_db);
						$ret['data']['insert'][] = $data_db;
					}else{
						$wpdb->update("$nama_tabel[$tipe_rekening]", $data_db, array(
							"id" => $cek_id
						));
						// wp_update_user($data_db);
						$ret['data']['update'][] = $data_db;
					}
					if(!empty($wpdb->last_error)){
						$ret['data']['error'][] = array($wpdb->last_error, $data_db);
					};
				}

			}
		}else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	public function get_mapping_rek()
	{
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);

		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SIMDA_BMD_APIKEY)) {
				if (!empty($_POST['kode_rekening_spbmd'])) {
					$kode_rekening_spbmd = $_POST['kode_rekening_spbmd'];
				} 
				if (!empty($_POST['uraian_rekening_spbmd'])) {
					$uraian_rekening_spbmd = $_POST['uraian_rekening_spbmd'];
				} 
				if (!empty($_POST['kode_rekening_ebmd'])) {
					$kode_rekening_ebmd = $_POST['kode_rekening_ebmd'];
				} 
				if (!empty($_POST['uraian_rekening_ebmd'])) {
					$uraian_rekening_ebmd = $_POST['uraian_rekening_ebmd'];
				} 
				if (!empty($_POST['tipe_rekening'])) {
					$tipe_rekening = $_POST['tipe_rekening'];
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Tipe Rekening kosong!';
				}

				if ($ret['status'] == 'success') {

					// untuk mengatur tabel sesuai tipe rekening
					$nama_tabel = array(
						"mapping_rekening_tanah" => "data_mapping_rek_a",
						"mapping_rekening_mesin" => "data_mapping_rek_b",
						"mapping_rekening_bangunan" => "data_mapping_rek_c",
						"mapping_rekening_jalan" => "data_mapping_rek_d",
						"mapping_rekening_aset_tetap" => "data_mapping_rek_e",
						"mapping_rekening_kontruksi" => "data_mapping_rek_f"
					);

					$data = $wpdb->get_results("
						SELECT * 
						FROM $nama_tabel[$tipe_rekening]
						WHERE active = 1
					",
						ARRAY_A
					);
					// print_r($data); die($wpdb->last_query);

					if (!empty($data)) {
						$counter = 1;
						$tbody = '';

						foreach ($data as $kk => $vv) {
							$tbody .= "<tr>";
							$tbody .= "<td class='text-center'>" . $counter++ . "</td>";
							$tbody .= "<td>" . $vv['kode_rekening_spbmd'] . "</td>";
							$tbody .= "<td>" . $vv['uraian_rekening_spbmd'] . "</td>";
							$tbody .= "<td>" . $vv['kode_rekening_ebmd'] . "</td>";
							$tbody .= "<td>" . $vv['uraian_rekening_ebmd'] . "</td>";

							$btn = '<div class="btn-action-group">';
							$btn .= '<button class="btn btn-sm btn-warning" onclick="edit_rekening(\'' . $vv['id'] . '\'); return false;" href="#" title="Edit Dokumen"><span class="dashicons dashicons-edit"></span></button>';
							$btn .= '<button style="margin-left: 5px;" class="btn btn-sm btn-danger" onclick="hapus_rekening(\'' . $vv['id'] . '\'); return false;" href="#" title="Hapus Dokumen"><span class="dashicons dashicons-trash"></span></button>';
							$btn .= '</div>';

							$tbody .= "<td class='text-center'>" . $btn . "</td>";
							$tbody .= "</tr>";
						}

						$ret['data'] = $tbody;
					} else {
						$ret['data'] = "<tr><td colspan='6' class='text-center'>Tidak ada data tersedia</td></tr>";
					}
				}
			} else {
				$ret = array(
					'status' => 'error',
					'message'   => 'Api Key tidak sesuai!'
				);
			}
		} else {
			$ret = array(
				'status' => 'error',
				'message'   => 'Format tidak sesuai!'
			);
		}
		die(json_encode($ret));
	}

	function connect_spbmd(){
		$host = get_option( '_crb_spbmd_ip' );
		$port = get_option( '_crb_spbmd_port' );
		if(empty($port)){
			$port = '3306';
		}
		$db = get_option( '_crb_spbmd_db' );
		$user = get_option( '_crb_spbmd_user' );
		$pass = get_option( '_crb_spbmd_pass' );
		$host = 'mysql:host='.$host.';dbname='.$db.';port='.$port;
		// die($host.'; user: '.$user.'; pass: '.$pass);
		try {
			$dbh = new PDO($host, $user, $pass);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		   	$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		   	return $dbh;
		} catch (Exception $e) {
			return false;
		}
	}
}
