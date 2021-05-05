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

	public function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function get_status_simda(){
		$cek_status_koneksi_simda = $this->CurlSimda(array(
			'query' => 'select top 1 * from ref_version ORDER BY Tgl_Update DESC',
			'no_debug' => true
		));
		$ket_simda = '<b style="color:red">Belum terkoneksi ke simda!</b>';
		if(!empty($cek_status_koneksi_simda)){
			$ket_simda = '<b style="color: green">Terkoneksi database SIMDABMD versi '.$cek_status_koneksi_simda[0]->LastAplDBVer.'</b>';
		}
		return $ket_simda;
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

	function get_status_spbmd(){
		$host = get_option( '_crb_spbmd_ip' );
		$db = get_option( '_crb_spbmd_db' );
		$user = get_option( '_crb_spbmd_user' );
		$ket_simda = '<b style="color:red">Belum terkoneksi ke SPBMD!</b>';
		if(
			!empty($host)
			&& !empty($db)
			&& !empty($user)
		){
			$dbh = $this->connect_spbmd();
			if(!empty($dbh)){
			   	$cek_status_koneksi_simda = $dbh->getAttribute(PDO::ATTR_CONNECTION_STATUS);
			   	$dbh = null;
				if(!empty($cek_status_koneksi_simda)){
					$ket_simda = '<b style="color: green">Terkoneksi database SPBMD</b>';
				}
			}
		}
		return $ket_simda;
	}

	function crb_attach_simda_options(){
		global $wpdb;
		
		$basic_options_container = Container::make( 'theme_options', __( 'SIMDA BMD' ) )
			->set_page_menu_position( 4 )
	        ->add_fields( array(
	        	Field::make( 'html', 'crb_simda_bmd_referensi_html' )
	            	->set_html( 'Referensi: <a target="_blank" href="https://github.com/agusnurwanto/SIMDA-BMD">https://github.com/agusnurwanto/SIMDA-BMD</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_koneksi_html' )
	            	->set_html( '<b>Configurasi Koneksi Database SIMDA BMD ( Status: '.$this->get_status_simda().' )</b>' ),
	            Field::make( 'text', 'crb_url_api_simda_bmd', 'URL API SIMDA' )
            	->set_help_text('Scirpt PHP SIMDA API dibuat terpisah di <a href="https://github.com/agusnurwanto/SIMDA-API-PHP" target="_blank">SIMDA API PHP</a>.'),
	            Field::make( 'text', 'crb_apikey_simda_bmd', 'APIKEY SIMDA' )
	            	->set_default_value($this->generateRandomString()),
	            Field::make( 'text', 'crb_db_simda_bmd', 'Database SIMDA' ),
	        	Field::make( 'html', 'crb_spbmd_koneksi_html' )
	            	->set_html( '<b>Configurasi Koneksi Database SPBMD ( Status: <span style="color: red;">'.$this->get_status_spbmd().'</span> )</b>' ),
	            Field::make( 'text', 'crb_spbmd_ip', 'IP server atau Hostname' ),
	            Field::make( 'text', 'crb_spbmd_port', 'Port server database' ),
	            Field::make( 'text', 'crb_spbmd_db', 'Nama Database' ),
	            Field::make( 'text', 'crb_spbmd_user', 'Username' ),
	            Field::make( 'text', 'crb_spbmd_pass', 'Password' )
	        ) );
	    Container::make( 'theme_options', __( 'Mapping SKPD' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_sub_unit_mapping() );
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

	function get_spbmd_sub_unit_mapping(){
		$dbh = $this->connect_spbmd();
		if(!empty($dbh)){
			$result = $dbh->query('SELECT * FROM mst_kl_sub_unit');
			$ret = array();
			$no = 0;
		   	while($row = $result->fetch()) {
		   		$no++;
		   		$alamat = '';
		   		if(!empty($row['ALAMAT_sub_unit']) || trim($row['ALAMAT_sub_unit'])!=''){
		   			$alamat = ' | Alamat: '.$row['ALAMAT_sub_unit'];
		   		}
		     	$ret[] = Field::make( 'text', 'crb_simda_bmd_sub_unit_'.$row['kd_lokasi'], $no.'. Nama Sub Unit di SPBMD: '.$row['NAMA_sub_unit'].$alamat.' | kd_lokasi: '.$row['kd_lokasi'] );
		   	}
		   	$dbh = null;
		   	return $ret;
		}else{
			return array(Field::make( 'html', 'crb_simda_bmd_sub_unit_ket' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' ));
		}
	}

	function CurlSimda($options, $debug=false){
        $query = $options['query'];
        $curl = curl_init();
        $req = array(
            'api_key' => get_option( '_crb_apikey_simda_bmd' ),
            'query' => $query,
            'db' => get_option('_crb_db_simda_bmd')
        );
        set_time_limit(0);
        $req = http_build_query($req);
        $url = get_option( '_crb_url_api_simda_bmd' );
        if(empty($url)){
        	return false;
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 10000
        ));

        $response = curl_exec($curl);
        // die($response);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
        	if($debug){
            	print_r($response);
        	}else{
	            $ret = json_decode($response);
	            if(!empty($ret->error)){
	            	if(empty($options['no_debug'])){
	                	echo "<pre>".print_r($ret, 1)."</pre>";
	                }
	            }else{
	            	if(!empty($ret->msg)){
	                	return $ret->msg;
	                }
	            }
	        }
        }
    }
}
