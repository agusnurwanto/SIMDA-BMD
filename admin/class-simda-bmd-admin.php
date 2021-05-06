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
			if($dbh){
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
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi' )
	            	->set_html( '<a onclick="migrasi_data(\'A\'); return false" href="javascript:void(0);" class="button button-primary">Proses</a>' ),
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
		if($dbh){
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

    function migrasi_data(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil migrasi data!'
		);
		if (
			!empty($_POST) 
			&& !empty($_POST['data']) 
			&& !empty($_POST['data']['type'])
		) {
			$dbh = $this->connect_spbmd();
			if($dbh){
				$result = $dbh->query('SELECT * FROM mst_kl_sub_unit');
				$kd_lokasi_mapping = array();
			   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
			   		$val_mapping = get_option( '_crb_simda_bmd_sub_unit_'.$row['kd_lokasi'] );
			     	if(!empty($val_mapping)){
			     		$kd_lokasi_mapping[$row['kd_lokasi']] = $val_mapping;
			     	}
			   	}
			   	if(!empty($kd_lokasi_mapping)){
					$type = $_POST['data']['type'];
					if($type == 'A'){
					   	$cek_status_koneksi_spbmd = $dbh->getAttribute(PDO::ATTR_CONNECTION_STATUS);
						if(!empty($cek_status_koneksi_spbmd)){
							$sql = '
								SELECT
									t.kd_lokasi as kd_lokasi_spbmd,
									t.*,
									s.* 
								FROM tanah t
									LEFT JOIN mst_kl_sub_unit s ON t.kd_lokasi=s.kd_lokasi
								WHERE t.kd_Lokasi IN ('.implode(',', array_keys($kd_lokasi_mapping)).')
								limit 10';
							$result = $dbh->query($sql);
							$aset = array();
							$no = 0;
						   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
						   		$kd_lok_simda = $kd_lokasi_mapping[$row['kd_lokasi_spbmd']];
						   		$kd_prov = (int) substr($kd_lok_simda, 0, 2);
						   		$kd_kab_kota = (int) substr($kd_lok_simda, 2, 2);
						   		$kd_bidang = (int) substr($kd_lok_simda, 4, 2);
						   		$kd_unit = (int) substr($kd_lok_simda, 6, 2);
						   		$kd_sub = (int) substr($kd_lok_simda, 8, 2);
						   		$sql = "
						   			SELECT 
						   				* 
						   			FROM ref_sub_unit 
						   			where kd_prov=$kd_prov
						   				AND kd_kab_kota=$kd_kab_kota
						   				AND kd_bidang=$kd_bidang
						   				AND kd_unit=$kd_unit
						   				AND kd_sub=$kd_sub
						   		";
						   		$row['sql_simda'] = $sql;
						   		$row['kd_lok_simda'] = $kd_lok_simda;
						   		$sub_unit_simda = $this->CurlSimda(array(
									'query' => $sql
								));
						   		$row['sub_unit_simda'] = $sub_unit_simda;
						   		if(!empty($sub_unit_simda)){
						   			// $id_pemda = $kd_bidang.$kd_unit.$kd_sub;
						   			$sql = "
						   				SELECT
					   						IDPemda,
					   						Kd_Prov,
									      	Kd_Kab_Kota,
									      	Kd_Bidang,
									      	Kd_Unit,
									      	Kd_Sub,
									      	Kd_UPB,
									      	Kd_Aset1,
									      	Kd_Aset2,
									      	Kd_Aset3,
									      	Kd_Aset4,
									      	Kd_Aset5,
									      	No_Register,
									      	Kd_Pemilik,
									      	Tgl_Perolehan,
									      	Luas_M2,
									      	Alamat,
									      	Hak_Tanah,
									      	Sertifikat_Tanggal,
									      	Sertifikat_Nomor,
									      	Penggunaan,
									      	Asal_usul,
									      	Harga,
									      	Keterangan,
									      	Tahun,
									      	No_SP2D,
									      	No_ID,
									      	Tgl_Pembukuan,
									      	Kd_Kecamatan,
									      	Kd_Desa,
									      	Invent,
									      	No_SKGuna,
									      	Kd_Penyusutan,
									      	Kd_Data,
									      	Log_User,
									      	Log_entry,
									      	Kd_Masalah,
									      	Ket_Masalah,
									      	Kd_KA,
									      	No_SIPPT,
									      	Dev_Id,
									      	Kd_Hapus,
									      	IDData,
									      	Kd_Aset8,
									      	Kd_Aset80,
									      	Kd_Aset81,
									      	Kd_Aset82,
									      	Kd_Aset83,
									      	Kd_Aset84,
									      	Kd_Aset85,
									      	No_Reg8,
									      	Tg_Update8
									  FROM Ta_KIB_A
									  WHERE kd_prov=$kd_prov
						   				AND kd_kab_kota=$kd_kab_kota
						   				AND kd_bidang=$kd_bidang
						   				AND kd_unit=$kd_unit
						   				AND kd_sub=$kd_sub
						   			";
						   			$cek_aset = $this->CurlSimda(array(
										'query' => $sql
									));
									$row['sql_aset_simda'] = $sql;
									$row['aset_simda'] = $cek_aset;
						   		}else{
						   			$row['status'] = 'error';
						   			$row['message'] = 'Sub Unit tidak ditemukan di SIMDA BMD. Perbaiki data mapping SKPD!';
						   		}
						   		$aset[] = $row;
						   	}
						   	$ret['data'] = $aset;
						}else{
							$ret['status'] = 'error';
							$ret['message'] = 'Koneksi database SPBMD gagal!';
						}
					}
			   	}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Sub Unit belum ada yang di mapping!';
			   	}
			   	$dbh = null;
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
    }
}