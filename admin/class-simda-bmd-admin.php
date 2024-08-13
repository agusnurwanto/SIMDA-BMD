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

	function get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;
		if ( is_array( $post_type ) ) {
			$post_type = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type IN ($post_type_in_string)
			", $page_title);
		} else {
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type = %s
			", $page_title, $post_type);
		}
		$page = $wpdb->get_var( $sql );
		if ( $page ) {
			return get_post( $page, $output );
		}
		return null;
	}

	public function generatePage($options = array()){
		$post_type = 'page';
		$status = 'private';
		if(!empty($options['post_status'])){
			$status = $options['post_status'];
		}
		if(!empty($options['post_type'])){
			$post_type = $options['post_type'];
		}

		if(!empty($options['post_id'])){
			$custom_post = get_page($options['post_id']);
		}else{
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
		}
		$_post = array(
			'post_title'	=> $options['nama_page'],
			'post_content'	=> $options['content'],
			'post_type'		=> $post_type,
			'post_status'	=> $status,
			'comment_status'	=> 'closed'
		);
		if (empty($custom_post) || empty($custom_post->ID)) {
			$id = wp_insert_post($_post);
			$_post['insert'] = 1;
			$_post['ID'] = $id;
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
			if(empty($options['show_header'])){
				update_post_meta($custom_post->ID, 'ast-main-header-display', 'disabled');
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			}
			update_post_meta($custom_post->ID, 'ast-breadcrumbs-content', 'disabled');
			update_post_meta($custom_post->ID, 'ast-featured-img', 'disabled');
			update_post_meta($custom_post->ID, 'site-content-layout', 'page-builder');
			update_post_meta($custom_post->ID, 'site-post-title', 'disabled');
			update_post_meta($custom_post->ID, 'site-sidebar-layout', 'no-sidebar');
			update_post_meta($custom_post->ID, 'theme-transparent-header-meta', 'disabled');
		}else if(!empty($options['update'])){
			$_post['ID'] = $custom_post->ID;
			wp_update_post( $_post );
			$_post['update'] = 1;
		}
		if(!empty($options['custom_url'])){
			$custom_post->custom_url = $options['custom_url'];
		}
		if(!empty($options['no_key'])){
			$link = get_permalink($custom_post);
		}else{
			$link = $this->get_link_post($custom_post);
		}
		return array(
			'post' => $custom_post,
			'id' => $custom_post->ID,
			'title' => $options['nama_page'],
			'url' => $link
		);
	}

	function crb_attach_simda_options(){
		global $wpdb;
		
		$mapping_skpd = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping SKPD',
			'content' => '[mapping_skpd]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_tanah = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Tanah',
			'content' => '[mapping_tanah]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_mesin = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Mesin',
			'content' => '[mapping_mesin]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_bangunan = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Bangunan',
			'content' => '[mapping_bangunan]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_jalan_irigrasi = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Jalan Irigrasi',
			'content' => '[mapping_jalan_irigrasi]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_aset_tetap = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Aset Tetap',
			'content' => '[mapping_aset_tetap]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		
		$mapping_konstruksi_dalam_pengerjaan = $this->generatePage(array(
			'nama_page' => 'Laporan Mapping Rekening Konstruksi Dalam Pengerjaan',
			'content' => '[mapping_konstruksi_dalam_pengerjaan]',
        	'show_header' => 1,
        	'no_key' => 1
		));

		$basic_options_container = Container::make( 'theme_options', __( 'SIMDA BMD' ) )
			->set_page_menu_position( 4 )
	        ->add_fields( array(
	        	Field::make( 'html', 'crb_simda_bmd_referensi_html' )
	            	->set_html( '
	            		<h3>Halaman Terkait</h3>
	            		<ol>
	            			<li><a target="_blank" href="'.$mapping_skpd['url'].'">'.$mapping_skpd['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_tanah['url'].'">'.$mapping_tanah['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_mesin['url'].'">'.$mapping_mesin['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_bangunan['url'].'">'.$mapping_bangunan['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_jalan_irigrasi['url'].'">'.$mapping_jalan_irigrasi['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_aset_tetap['url'].'">'.$mapping_aset_tetap['title'].'</a></li>
	            			<li><a target="_blank" href="'.$mapping_konstruksi_dalam_pengerjaan['url'].'">'.$mapping_konstruksi_dalam_pengerjaan['title'].'</a></li>
	            		</ol>
	            		Referensi: <a target="_blank" href="https://github.com/agusnurwanto/SIMDA-BMD">https://github.com/agusnurwanto/SIMDA-BMD</a>' ),
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
	    Container::make( 'theme_options', __( 'Mapping SKPD SIPD' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_sub_unit_mapping_sipd() );
	    Container::make( 'theme_options', __( 'Rek. Tanah' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_tanah_mapping() );
	    Container::make( 'theme_options', __( 'Rek. Mesin' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_mesin_mapping() );
	    Container::make( 'theme_options', __( 'Rek. Bangunan' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_bangunan_mapping() );
	    Container::make( 'theme_options', __( 'Rek. Jalan Irigrasi' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_jalan_mapping() );
	    Container::make( 'theme_options', __( 'Rek. Aset Tetap' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_aset_tetap_mapping() );
	    Container::make( 'theme_options', __( 'Rek. Kontruksi dalam Pengerjaan' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( $this->get_spbmd_rek_kontruksi_mapping() );
	    // Container::make( 'theme_options', __( 'Kondisi Mesin' ) )
		   //  ->set_page_parent( $basic_options_container )
		   //  ->add_fields( $this->get_spbmd_kondisi_mesin_mapping() );
	    Container::make( 'theme_options', __( 'Migrasi Data' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( array(
	        	Field::make( 'html', 'crb_simda_bmd_referensi_html' )
	            	->set_html( add_thickbox().'
	            		<div id="my-content-id" style="display:none;">
						</div>
						<a href="#TB_inline?&width=753&height=400&inlineId=my-content-id" class="thickbox" id="open-popup" title="DAFTAR MAPPING LOKASI SUB UNIT"></a>
	            		Referensi: <a target="_blank" href="https://github.com/agusnurwanto/SIMDA-BMD">https://github.com/agusnurwanto/SIMDA-BMD</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_a' )
	            	->set_html( 'Migrasi table KD_KIB_A (Aset Tanah)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_a' )
	            	->set_html( '<a onclick="migrasi_data(\'A\'); return false" href="javascript:void(0);" class="button button-primary">Proses KD_KIB_A</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_b' )
	            	->set_html( 'Migrasi table KD_KIB_B (Aset Mesin)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_b' )
	            	->set_html( '<a onclick="migrasi_data(\'B\'); return false" href="javascript:void(0);" class="button button-primary">Proses KD_KIB_B</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_c' )
	            	->set_html( 'Migrasi table KD_KIB_C (Aset Bangunan)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_c' )
	            	->set_html( '<a onclick="migrasi_data(\'C\'); return false" href="javascript:void(0);" class="button button-primary">Proses KD_KIB_C</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_d' )
	            	->set_html( 'Migrasi table KD_KIB_D (Aset Jalan Irigrasi)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_d' )
	            	->set_html( '<a onclick="migrasi_data(\'D\'); return false" href="javascript:void(0);" class="button button-primary">Proses KD_KIB_D</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_e' )
	            	->set_html( 'Migrasi table KD_KIB_E (Aset Tetap seperti buku, tanaman, hewan)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_e' )
	            	->set_html( '<a onclick="migrasi_data(\'E\'); return false" href="javascript:void(0);" class="button button-primary">Proses KD_KIB_E</a>' ),
	        	Field::make( 'html', 'crb_simda_bmd_migrasi_f' )
	            	->set_html( 'Migrasi table KD_KIB_F (Aset Kontruksi Dalam Pengerjaan)' ),
		        Field::make( 'html', 'crb_simda_bmd_migrasi_aksi_f' )
	            	->set_html( '<a onclick="migrasi_data(\'F\'); return false" href="javascript:void(0);" class="button button-default">Proses KD_KIB_F</a>' ),
	        ) );
	    Container::make( 'theme_options', __( 'Import Settings' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields( array(
		    	Field::make( 'html', 'crb_simda_bmd_import_settings' )
	            	->set_html( 'Halaman impport settings!' )
	        ) );
	}

	function get_spbmd_kondisi_mesin_mapping(){
		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_kondisi_mesin_ket' )->set_html( 'Kode mapping SIMDA BMD diambil dari tabel Ref_Kondisi (1=Baik, 2=Kurang Baik, 3=Rusak Berat, 4=Hilang, 5=Tidak Ditemukan, 6=Lainnya)' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT keterangan FROM `mesin` GROUP by keterangan');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		$no++;
			   		$key = $this->trim_text($row['keterangan']);
			   		if(!empty($key)){
			     		$ret[$key] = Field::make( 'text', 'crb_simda_bmd_kondisi_mesin_'.$key, $no.'. Kondisi aset Mesin di SPBMD: '.$row['keterangan'] );
			     	}
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_kondisi_mesin_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_kondisi_mesin_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_rek_kontruksi_mapping(){
		$halaman_mapping_rek_kontruksi = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. Kontruksi',
			'content' => '[halaman_mapping_rek_kontruksi]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_f = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB F',
			'content' => '[halaman_laporan_kib_f]',
        	'show_header' => 1,
        	'no_key' => 1
		));

		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_rek_aset_tetap_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_kontruksi['url'].'">'.$halaman_mapping_rek_kontruksi['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_f['url'].'">'.$halaman_laporan_kib_f['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `kontruksi_dlm_pengerjaan` GROUP by kd_barang, jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		// $this->update_val_mapping('_crb_simda_bmd_rek_kontruksi_', $row['kd_barang'], $row['jenis_barang']);
			   		$no++;
			   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_kontruksi_'.$key, $no.'. Nama Jenis Kontruksi dalam Pengerjaan di SPBMD: '.$row['kd_barang'].'-'.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_kontruksi_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_kontruksi_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_rek_aset_tetap_mapping(){
		$halaman_mapping_rek_aset_tetap = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. Aset Tetap',
			'content' => '[halaman_mapping_rek_aset_tetap]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_e = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB E',
			'content' => '[halaman_laporan_kib_e]',
        	'show_header' => 1,
        	'no_key' => 1
		));


		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_rek_aset_tetap_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_aset_tetap['url'].'">'.$halaman_mapping_rek_aset_tetap['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_e['url'].'">'.$halaman_laporan_kib_e['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `aset_tetap` GROUP by kd_barang, jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		// $this->update_val_mapping('_crb_simda_bmd_rek_aset_tetap_', $row['kd_barang'], $row['jenis_barang']);
			   		$no++;
			   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_aset_tetap_'.$key, $no.'. Nama Jenis Aset Tetap di SPBMD: '.$row['kd_barang'].'-'.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_aset_tetap_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_aset_tetap_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_rek_bangunan_mapping(){
		$halaman_mapping_rek_bangunan = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. Bangunan',
			'content' => '[halaman_mapping_rek_bangunan]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_c = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB C',
			'content' => '[halaman_laporan_kib_c]',
        	'show_header' => 1,
        	'no_key' => 1
		));


		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_rek_bangunan_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_bangunan['url'].'">'.$halaman_mapping_rek_bangunan['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_c['url'].'">'.$halaman_laporan_kib_c['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `gedung` GROUP by kd_barang, jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		// $this->update_val_mapping('_crb_simda_bmd_rek_bangunan_', $row['kd_barang'], $row['jenis_barang']);
			   		$no++;
			   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_bangunan_'.$key, $no.'. Nama Jenis Bangunan di SPBMD: '.$row['kd_barang'].'-'.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_bangunan_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_bangunan_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_rek_jalan_mapping(){
		$halaman_mapping_rek_jalan = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. Jalan',
			'content' => '[halaman_mapping_rek_jalan]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_d = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB D',
			'content' => '[halaman_laporan_kib_d]',
        	'show_header' => 1,
        	'no_key' => 1
		));

		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_rek_jalan_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_jalan['url'].'">'.$halaman_mapping_rek_jalan['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_d['url'].'">'.$halaman_laporan_kib_d['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `jalan_irigasi` GROUP by kd_barang, jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		// $this->update_val_mapping('_crb_simda_bmd_rek_jalan_', $row['kd_barang'], $row['jenis_barang']);
			   		$no++;
			   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_jalan_'.$key, $no.'. Nama Jenis Jalan di SPBMD: '.$row['kd_barang'].'-'.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_jalan_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_jalan_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function update_val_mapping($key, $kd_barang, $jenis_barang){
		$val_lama = get_option($key.$this->trim_text($jenis_barang));
		update_option($key.$this->trim_text($kd_barang.'-'.$jenis_barang), $val_lama);
	}

	function get_spbmd_rek_mesin_mapping(){
		$halaman_mapping_rek_mesin = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. Mesin',
			'content' => '[halaman_mapping_rek_mesin]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_b = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB B',
			'content' => '[halaman_laporan_kib_b]',
        	'show_header' => 1,
        	'no_key' => 1
		));

		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_rek_mesin_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_mesin['url'].'">'.$halaman_mapping_rek_mesin['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_b['url'].'">'.$halaman_laporan_kib_b['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `mesin` GROUP by kd_barang, jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		$no++;
			   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_mesin_'.$key, $no.'. Nama Jenis Mesin di SPBMD: '.$row['kd_barang'].'-'.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_mesin_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_mesin_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_rek_tanah_mapping(){
		$halaman_mapping_rek_tanah = $this->generatePage(array(
			'nama_page' => 'Halaman Mapping Rek. tanah',
			'content' => '[halaman_mapping_rek_tanah]',
        	'show_header' => 1,
        	'no_key' => 1
		));
		$halaman_laporan_kib_a = $this->generatePage(array(
			'nama_page' => 'Halaman Laporan KIB A',
			'content' => '[halaman_laporan_kib_a]',
        	'show_header' => 1,
        	'no_key' => 1
		));

		$dbh = $this->connect_spbmd();
		$ret = array(
			Field::make( 'html', 'crb_simda_bmd_rek_tanah_ket' )
			->set_html( '
				<ol>
					<li><a target="_blank" href="'.$halaman_mapping_rek_tanah['url'].'">'.$halaman_mapping_rek_tanah['title'].'</a></li>
					<li><a target="_blank" href="'.$halaman_laporan_kib_a['url'].'">'.$halaman_laporan_kib_a['title'].'</a></li>
				</ol>
				Kode mapping SIMDA BMD diambil dari tabel Ref_Rek5_108 yang digabung antara kolom (Kd_Aset, Kd_Aset0, Kd_Aset1, Kd_Aset2, Kd_Aset3, Kd_Aset4, Kd_Aset5) dengan sparator titik (.). Contoh 1.3.2.5.3.1.8' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT jenis_barang FROM `tanah` GROUP by jenis_barang');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		$no++;
			   		$key = $this->trim_text($row['jenis_barang']);
			     	$ret[] = Field::make( 'text', 'crb_simda_bmd_rek_tanah_'.$key, $no.'. Nama Jenis Tanah di SPBMD: '.$row['jenis_barang'] );
			   	}
			   	$dbh = null;
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_tanah_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_rek_tanah_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_sub_unit_mapping(){
		$dbh = $this->connect_spbmd();
		$ret = array(Field::make( 'html', 'crb_simda_bmd_sub_unit_ket' )->set_html( 'Kode mapping SIMDA BMD diambil dari tabel ref_upb yang digabung antara kolom (kd_prov, kd_kab_kota, kd_bidang, kd_unit, kd_sub, kd_upb)' ));
		if($dbh){
			try {
				$result = $dbh->query('SELECT * FROM mst_kl_sub_unit');
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
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_simda_bmd_sub_unit_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_simda_bmd_sub_unit_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function get_spbmd_sub_unit_mapping_sipd(){
		global $wpdb;
		$dbh = $this->connect_spbmd();
		if($dbh){
			try {
				$result = $dbh->query('
					SELECT 
						s.*,
						u.kd_bidang as kd_bidang_induk,
						u.nama_bidang as nama_induk
					FROM mst_kl_satker s 
					INNER JOIN mst_kl_bidang u ON u.kd_bidang=s.n_kd_bidang
						AND u.kd_urusan=s.n_kd_urusan
				');
				$no = 0;
			   	while($row = $result->fetch()) {
			   		$no++;
			   		$alamat = '';
			   		if(!empty($row['ALAMAT_satker']) || trim($row['ALAMAT_satker'])!=''){
			   			$alamat = ' | Alamat: '.$row['ALAMAT_satker'];
			   		}
			   		$kd_lokasi = $row['n_kd_urusan'].'-'.$row['n_kd_bidang'].'-'.$row['kd_prop'].'-'.$row['kd_kab'].'-'.$row['kd_satker'];
					$ret[] = Field::make( 'html', 'crb_sipd_unit_header_'.$kd_lokasi )->set_html( '<h3>Mapping Kode SATKER</h3>' );
			     	$ret[] = Field::make( 'text', 'crb_sipd_kode_unit_'.$kd_lokasi, $no.'. (Kode OPD E-BMD) Nama SATKER di SPBMD: '.$row['NAMA_satker'].$alamat.' | kd_lokasi: '.$kd_lokasi.' | nama_bidang: '.$row['nama_induk'].' | kd_bidang '.$row['kd_bidang_induk']);
			     	$ret[] = Field::make( 'text', 'crb_sipd_nama_unit_'.$kd_lokasi, '(Nama OPD E-BMD)');

					$ret[] = Field::make( 'html', 'crb_sipd_sub_unit_header_'.$kd_lokasi )->set_html( '<h3>Mapping Kode Lokasi</h3>' );
					$result2 = $dbh->query($wpdb->prepare('
						SELECT 
							s.*,
							u.kd_lokasi as kd_lokasi_induk,
							u.NAMA_unit as nama_induk
						FROM mst_kl_sub_unit s 
						INNER JOIN mst_kl_unit u ON u.kd_prop=s.kd_prop
							AND u.kd_kab=s.kd_kab
							AND u.kd_satker=s.kd_satker
							AND u.kd_Unit=s.kd_Unit
						WHERE s.kd_satker=%s
							AND s.kd_kab=%s
							AND s.kd_prop=%s
					', $row['kd_satker'], $row['kd_kab'], $row['kd_prop']));
					$no_lokasi = 0;
				   	while($row2 = $result2->fetch()) {
				   		$no_lokasi++;
				   		$alamat = '';
				   		if(!empty($row2['ALAMAT_sub_unit']) || trim($row2['ALAMAT_sub_unit'])!=''){
				   			$alamat = ' | Alamat: '.$row2['ALAMAT_sub_unit'];
				   		}
				     	$ret[] = Field::make( 'text', 'crb_sipd_sub_unit_'.$row2['kd_lokasi'], $no.'.'.$no_lokasi.'. (Kode Lokasi E-BMD) Nama Sub Unit di SPBMD: '.$row2['NAMA_sub_unit'].$alamat.' | kd_lokasi: '.$row2['kd_lokasi'].' | nama_induk: '.$row2['nama_induk'].' | kd_lokasi_induk '.$row2['kd_lokasi_induk']);
				   	}
			   	}
			} catch (PDOException $e) {
				$ret[] = Field::make( 'html', 'crb_sipd_unit_ket_error' )->set_html( $e->getMessage() );
			}
		}else{
			$ret[] = Field::make( 'html', 'crb_sipd_sub_unit_ket_error' )->set_html( '<span style="color:red;">Koneksi database SPBMD gagal</span>' );
		}
		return $ret;
	}

	function trim_text($text){
		return str_replace(array(
			PHP_EOL, 
			"\n", 
			"\r", 
			'[', 
			'@', 
			'"', 
			'?', 
			'<', 
			'=', 
			':', 
			';', 
			' ', 
			'/', 
			'(', 
			')', 
			'.', 
			'+', 
			'`', 
			',', 
			'&'
		), '_', trim(strtolower($text)));
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
				$kd_lokasi_mapping = $_POST['data']['skpd'];
			   	
			   	if(!empty($kd_lokasi_mapping)){
					$type = $_POST['data']['type'];
					$nama_type = '';
				   	$cek_status_koneksi_spbmd = $dbh->getAttribute(PDO::ATTR_CONNECTION_STATUS);
					if(!empty($cek_status_koneksi_spbmd)){
						$kd_barang = '';
						if($type == 'A'){
							$nama_type = 'Tanah';
							$table_aset_spbmd = 'tanah';
							$table_aset_simda = 'Ta_KIB_A';
							$key_rek = '_crb_simda_bmd_rek_tanah_';
							$table_aset_p_spbmd = 'pemeliharaan_tanah';
							$table_aset_p_simda = 'Ta_KIBAR';
						}else if($type == 'B'){
							$nama_type = 'Mesin';
							$table_aset_spbmd = 'mesin';
							$table_aset_simda = 'Ta_KIB_B';
							$key_rek = '_crb_simda_bmd_rek_mesin_';
							$kd_barang = true;
						}else if($type == 'C'){
							$nama_type = 'Bangunan';
							$table_aset_spbmd = 'gedung';
							$table_aset_simda = 'Ta_KIB_C';
							$key_rek = '_crb_simda_bmd_rek_bangunan_';
							$table_aset_p_spbmd = 'pemeliharaan_gedung';
							$table_aset_p_simda = 'Ta_KIBCR';
							$kd_barang = true;
						}else if($type == 'D'){
							$nama_type = 'Jalan';
							$table_aset_spbmd = 'jalan_irigasi';
							$table_aset_simda = 'Ta_KIB_D';
							$key_rek = '_crb_simda_bmd_rek_jalan_';
							$table_aset_p_spbmd = 'pemeliharaan_jalan_irigasi';
							$table_aset_p_simda = 'Ta_KIBDR';
							$kd_barang = true;
						}else if($type == 'E'){
							$nama_type = 'Aset Tetap';
							$table_aset_spbmd = 'aset_tetap';
							$table_aset_simda = 'Ta_KIB_E';
							$key_rek = '_crb_simda_bmd_rek_aset_tetap_';
							$kd_barang = true;
						}else{
							$ret['status'] = 'error';
							$ret['message'] = 'Rekening table Ta_KIB_'.$type.' masih dalam pengembangan!';
						}

						if($ret['status'] == 'success'){
							$sql = '
								SELECT
									m.kd_lokasi as kd_lokasi_spbmd,
									m.*,
									s.* 
								FROM '.$table_aset_spbmd.' m
									LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
								WHERE m.kd_Lokasi IN ('.implode(',', array_keys($kd_lokasi_mapping)).')';
						   	$ret['sql_'.$table_aset_spbmd] = $sql;
							$result = $dbh->query($sql);
							$aset = array();
							$no = 0;
							$cek_unik = array();
							$cek_double = array();
						   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
						   		$kd_lok_simda = $kd_lokasi_mapping[$row['kd_lokasi_spbmd']];
						   		$_kd_lok_simda = explode('.', $kd_lok_simda);
						   		$kd_prov = $_kd_lok_simda[0];
						   		$kd_kab_kota = $_kd_lok_simda[1];
						   		$kd_bidang = $_kd_lok_simda[2];
						   		$kd_unit = $_kd_lok_simda[3];
						   		$kd_sub = $_kd_lok_simda[4];
						   		$kd_upb = $_kd_lok_simda[5];
						   		$sql = "
						   			SELECT 
						   				* 
						   			FROM ref_upb 
						   			where kd_prov=$kd_prov
						   				AND kd_kab_kota=$kd_kab_kota
						   				AND kd_bidang=$kd_bidang
						   				AND kd_unit=$kd_unit
						   				AND kd_sub=$kd_sub
						   				AND kd_upb=$kd_upb
						   		";
						   		$row['sql_simda'] = $sql;
						   		$row['kd_lok_simda'] = $kd_lok_simda;
						   		$upb_simda = $this->CurlSimda(array(
									'query' => $sql
								));
						   		$row['upb_simda'] = $upb_simda;
						   		if(!empty($upb_simda)){
						   			$kd_aset = '';
						   			$kd_aset0 = '';
						   			$kd_aset1 = '';
						   			$kd_aset2 = '';
						   			$kd_aset3 = '';
						   			$kd_aset4 = '';
						   			$kd_aset5 = '';
							   		if(empty($kd_barang)){
							   			$key = $this->trim_text($row['jenis_barang']);
							     	}else{
							   			$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
							     	}
							     	$rek = get_option( $key_rek.$key );
							     	if(!empty($rek)){
							     		$rek_all = $rek;
							     		$rek = explode('.', $rek);
							     		$kd_aset = $rek[0];
							   			$kd_aset0 = $rek[1];
							   			$kd_aset1 = $rek[2];
							   			$kd_aset2 = $rek[3];
							   			$kd_aset3 = $rek[4];
							   			$kd_aset4 = $rek[5];
							   			$kd_aset5 = $rek[6];
							     	}
								   	if(
								   		!empty($kd_aset)
								   		AND !empty($kd_aset0)
								   		AND !empty($kd_aset1)
								   		AND !empty($kd_aset2)
								   		AND !empty($kd_aset3)
								   		AND !empty($kd_aset4)
								   		AND !empty($kd_aset5)
								   	){
							   			$sql = "
								   			SELECT 
								   				* 
								   			FROM Ref_Rek5_108 
								   			where kd_aset IN (".$kd_aset.")
									   			AND kd_aset0 IN (".$kd_aset0.")
									   			AND kd_aset1 IN (".$kd_aset1.")
									   			AND kd_aset2 IN (".$kd_aset2.")
									   			AND kd_aset3 IN (".$kd_aset3.")
									   			AND kd_aset4 IN (".$kd_aset4.")
									   			AND kd_aset5 IN (".$kd_aset5.")
								   		";
								   		$row['sql_rek_simda'] = $sql;
								   		$rek_simda = $this->CurlSimda(array(
											'query' => $sql
										));
								   		$row['rek_simda'] = $rek_simda;
								   	}else{
								   		$rek_simda = '';
								   	}
								   	if(!empty($rek_simda)){
								   		$keterangan = '';
							   			$where_simda = '';
							   			$kondisi = '6';
						   				$tgl_pembukuan = date('YYYY').'-12-31';
							   			$kontruksi_tingkat = '';
							   			$kontruksi_beton = '';
						   				if(!empty($row['tgl_pengadaan'])){
							   				$tgl_pembukuan = explode('-', $row['tgl_pengadaan']);
							   				$tgl_pembukuan = $tgl_pembukuan[0];
						   					$tgl_pembukuan = $tgl_pembukuan.'-12-31';
						   				}
										$options_no = array(
											'kd_prov' => $kd_prov,
											'kd_kab_kota' => $kd_kab_kota,
											'kd_bidang' => $kd_bidang,
											'kd_unit' => $kd_unit,
											'kd_sub' => $kd_sub,
											'kd_upb' => $kd_upb,
											'table'	=> $table_aset_simda
										);

										$options_no = array_merge(array(
											'kd_aset' => $kd_aset,
								   			'kd_aset0' => $kd_aset0,
								   			'kd_aset1' => $kd_aset1,
								   			'kd_aset2' => $kd_aset2,
								   			'kd_aset3' => $kd_aset3,
								   			'kd_aset4' => $kd_aset4,
								   			'kd_aset5' => $kd_aset5
										), $options_no);

								   		if(empty($row['jumlah'])){
								   			$row['jumlah'] = 1;
								   		}
								   		$row['harga'] = $row['harga']/$row['jumlah'];
								   		for($i=1; $i<=$row['jumlah']; $i++){
											$id_pemda = $this->get_id_pemda($options_no);
									   		if($table_aset_simda == 'Ta_KIB_A'){
									   			$row['Luas'] = str_replace(',', '.', $row['Luas']);
								   				$keterangan = substr($row['jenis_barang'].", ".$row['Keterangan'].", Reg: ".$row['register_serti'], 0, 225);
								   				$where_simda = "
								   					AND Luas_M2 = '".$row['Luas']."'
								   					AND harga = '".$row['harga']."'
										   			AND penggunaan = '".substr($row['guna'], 0, 50)."'
										   			AND alamat = '".substr($row['alamat'], 0, 255)."'  COLLATE SQL_Latin1_General_CP1_CS_AS
										   			AND tgl_perolehan = '".$row['tgl_pengadaan']." 00:00:00'
										   			AND tgl_pembukuan = '".$row['tgl_pengadaan']." 00:00:00'
										   			AND keterangan = '".$keterangan."'
								   				";
									   		}else if($table_aset_simda == 'Ta_KIB_B'){
								   				$keterangan = substr($i." ".$row['jenis_barang'].", ".$row['keterangan'].", Reg: ".$row['register'], 0, 225);
								   				$where_simda = "
								   					AND harga = '".$row['harga']."'
										   			AND merk = '".substr($row['merk'], 0, 50)."'
										   			AND cc = '".substr($row['ukuran'], 0, 50)."'
										   			AND bahan = '".substr($row['bahan'], 0, 50)."'
										   			AND tahun = '".$row['thn_beli']."'
										   			AND nomor_pabrik = '".substr($row['no_pabrik'], 0, 50)."'
										   			AND nomor_rangka = '".substr($row['no_rangka'], 0, 50)."'
										   			AND nomor_mesin = '".substr($row['no_mesin'], 0, 50)."'
										   			AND nomor_polisi = '".substr($row['no_polisi'], 0, 10)."'
										   			AND nomor_bpkb = '".substr($row['no_bpkb'], 0, 50)."'
										   			AND asal_usul = '".substr($row['asal'], 0, 50)."'
										   			AND keterangan = '".$keterangan."'
										   			AND tgl_perolehan = '".$row['tgl_pengadaan']." 00:00:00'
										   			AND tgl_pembukuan = '".$tgl_pembukuan." 00:00:00'
								   				";
									   		}else if($table_aset_simda == 'Ta_KIB_C'){
								   				$keterangan = substr($i." ".$row['jenis_barang'].", ".$row['keterangan'].", Reg: ".$row['register'], 0, 225);
								   				if($row['kontruksi_tingkat'] > 1){
								   					$kontruksi_tingkat = 'Tidak';
								   				}else{
								   					$kontruksi_tingkat = 'Bertingkat';
								   				}
								   				if($row['kontruksi_beton'] > 1){
								   					$kontruksi_beton = 'Tidak';
								   				}else{
								   					$kontruksi_beton = 'Beton';
								   				}
								   				$tgl_pembukuan = explode('-', $row['tgl_dok_gedung']);
								   				$tgl_pembukuan = $tgl_pembukuan[0];
							   					$tgl_pembukuan = $tgl_pembukuan.'-12-31';
								   				$where_simda = "
								   					AND harga = '".$row['harga']."'
										   			AND bertingkat_tidak = '".$kontruksi_tingkat."'
										   			AND beton_tidak = '".$kontruksi_beton."'
										   			AND tgl_perolehan = '".$row['tgl_dok_gedung']." 00:00:00'
										   			AND luas_lantai = '".$row['luas_lantai']."'
										   			AND lokasi = '".substr($row['alamat'], 0, 255)."'  COLLATE SQL_Latin1_General_CP1_CS_AS
										   			AND dokumen_tanggal = '".$row['tgl_dok_gedung']." 00:00:00'
										   			AND dokumen_nomor = '".substr($row['no_dok_gedung'], 0, 50)."'
										   			AND status_tanah = '".substr($row['status_tanah'], 0, 50)."'
										   			AND keterangan = '".$keterangan."'
										   			AND tgl_pembukuan = '".$tgl_pembukuan." 00:00:00'
								   				";
									   		}else if($table_aset_simda == 'Ta_KIB_D'){
								   				$keterangan = substr($i." ".$row['jenis_barang'].", ".$row['keterangan'].", Reg: ".$row['register'], 0, 225);
								   				$tgl_pembukuan = explode('-', $row['dok_tanggal']);
								   				$tgl_pembukuan = $tgl_pembukuan[0];
							   					$tgl_pembukuan = $tgl_pembukuan.'-12-31';
								   				$where_simda = "
								   					AND Konstruksi = '".$row['kontruksi']."'
								   					AND Panjang = '".$row['panjang']."'
								   					AND Lebar = '".$row['lebar']."'
								   					AND Luas = '".$row['luas']."'
								   					AND harga = '".$row['harga']."'
										   			AND tgl_perolehan = '".$row['dok_tanggal']." 00:00:00'
										   			AND lokasi = '".substr($row['letak'], 0, 255)."'  COLLATE SQL_Latin1_General_CP1_CS_AS
										   			AND dokumen_tanggal = '".$row['dok_tanggal']." 00:00:00'
										   			AND dokumen_nomor = '".substr($row['dok_nomor'], 0, 50)."'
										   			AND status_tanah = '".substr($row['status_tanah'], 0, 50)."'
										   			AND keterangan = '".$keterangan."'
										   			AND tgl_pembukuan = '".$tgl_pembukuan." 00:00:00'
								   				";
									   		}else if($table_aset_simda == 'Ta_KIB_E'){
								   				$keterangan = substr($i." ".$row['jenis_barang'].", ".$row['keterangan'].", Reg: ".$row['register'], 0, 225);
								   				$tgl_pembukuan = explode('-', $row['tgl_pengadaan']);
								   				$tgl_pembukuan = $tgl_pembukuan[0];
							   					$tgl_pembukuan = $tgl_pembukuan.'-12-31';

							   					if(!empty($row['seni_bahan'])){
							   						$row['buku_spesifikasi'] = $row['seni_asal'].' '.$row['seni_bahan'];
							   					}else if(!empty($row['hewan_tumbuhan_jenis'])){
							   						$row['buku_spesifikasi'] = $row['hewan_tumbuhan_jenis'];
							   					}
							   					$row['buku_spesifikasi'] = trim($row['buku_spesifikasi']);

							   					if(!empty($row['seni_pencipta'])){
							   						$row['buku_pencipta'] = $row['seni_pencipta'];
							   					}
								   				$where_simda = "
								   					AND Judul = '".$row['buku_judul']."'
								   					AND Pencipta = '".$row['buku_pencipta']."'
								   					AND Bahan = '".$row['buku_spesifikasi']."'
								   					AND Ukuran = '".$row['hewan_tumbuhan_ukuran']."'
								   					AND harga = '".$row['harga']."'
										   			AND tgl_perolehan = '".$row['tgl_pengadaan']." 00:00:00'
										   			AND keterangan = '".$keterangan."'
										   			AND tgl_pembukuan = '".$tgl_pembukuan." 00:00:00'
								   				";
									   		}
								   			$sql = "
								   				SELECT TOP 1
							   						*
											  	FROM $table_aset_simda
											  	WHERE kd_prov=$kd_prov
									   				AND kd_kab_kota=$kd_kab_kota
									   				AND kd_bidang=$kd_bidang
									   				AND kd_unit=$kd_unit
									   				AND kd_sub=$kd_sub
									   				AND kd_upb=$kd_upb
									   				AND kd_aset8 IN (".$kd_aset.")
										   			AND kd_aset80 IN (".$kd_aset0.")
										   			AND kd_aset81 IN (".$kd_aset1.")
										   			AND kd_aset82 IN (".$kd_aset2.")
										   			AND kd_aset83 IN (".$kd_aset3.")
										   			AND kd_aset84 IN (".$kd_aset4.")
										   			AND kd_aset85 IN (".$kd_aset5.")
										   			".$where_simda."
									   			ORDER by IDPemda DESC
								   			";
								   			$cek_aset = $this->CurlSimda(array(
												'query' => $sql
											));
											$row['sql_aset_simda'] = $sql;
											$row['aset_simda'] = $cek_aset;
											$no_register = $this->get_no_register($options_no);

											$options_columns = array(
												'IDPemda'	=> "'".$id_pemda."'",
												'Kd_Prov'	=> "".$kd_prov."",
												'Kd_Kab_Kota'	=> "".$kd_kab_kota."",
												'Kd_Bidang'	=> "".$kd_bidang."",
												'Kd_Unit'	=> "".$kd_unit."",
												'Kd_Sub'	=> "".$kd_sub."",
												'Kd_UPB'	=> "".$kd_upb."",
												'Kd_Aset1'	=> "NULL",
												'Kd_Aset2'	=> "NULL",
												'Kd_Aset3'	=> "NULL",
												'Kd_Aset4'	=> "NULL",
												'Kd_Aset5'	=> "NULL",
												'No_Register'	=> "".$no_register."",
												'Kd_Pemilik'	=> "12",
												'Tgl_Perolehan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
												'Harga'	=> "'".$row['harga']."'",
												'Keterangan'	=> "'".$keterangan."'",
												'No_SP2D'	=> "NULL",
												'No_ID'	=> "NULL",
												'Kd_Kecamatan'	=> "NULL",
												'Kd_Desa'	=> "NULL",
												'Invent'	=> "NULL",
												'No_SKGuna'	=> "NULL",
												'Kd_Penyusutan'	=> "NULL",
												'Kd_Data'	=> "'2'",
												'Log_User'	=> "'art'",
												'Log_entry'	=> "'".date('Y-m-d H:i:s')."'",
												'Kd_Masalah'	=> "NULL",
												'Ket_Masalah'	=> "NULL",
												'Kd_KA'	=> "'1'",
												'No_SIPPT'	=> "NULL",
												'Dev_Id'	=> "NULL",
												'Kd_Hapus'	=> "0",
												'IDData'	=> "NULL",
												'Kd_Aset8'	=> "".$kd_aset."",
												'Kd_Aset80'	=> "".$kd_aset0."",
												'Kd_Aset81'	=> "".$kd_aset1."",
												'Kd_Aset82'	=> "".$kd_aset2."",
												'Kd_Aset83'	=> "".$kd_aset3."",
												'Kd_Aset84'	=> "".$kd_aset4."",
												'Kd_Aset85'	=> "".$kd_aset5."",
												'No_Reg8'	=> "".$no_register."",
											    'Tg_Update8'	=> "NULL"
											);

											if($table_aset_simda == 'Ta_KIB_A'){
												$tgl_sertifikat = 'null';
												if(!empty($row['nomor_serti'])){
													$tgl_sertifikat = "'".$row['tgl_serti']." 00:00:00"."'";
												}
												$options_columns_custom = array(
													'Luas_M2'	=> "'".$row['Luas']."'",
													'Alamat'	=> "'".substr($row['alamat'], 0, 255)."'",
													'Hak_Tanah'	=> "'Hak Pakai'",
													'Sertifikat_Tanggal'	=> $tgl_sertifikat,
													'Sertifikat_Nomor'	=> "'".$row['nomor_serti']."'",
													'Penggunaan'	=> "'".substr($row['guna'], 0, 50)."'",
													'Tahun'	=> "'".$row['thn_pengadaan']."'",
													'Tgl_Pembukuan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'Asal_usul'	=> "'Pembelian'"
												);
											}else if($table_aset_simda == 'Ta_KIB_B'){
												$options_columns_custom = array(
													'Kd_Ruang'	=> "NULL",
													'Kd_Pemilik'	=> "12",
													'Merk'	=> "'".substr($row['merk'], 0, 50)."'",
													'Type'	=> "'".substr($row['jenis_barang'], 0, 50)."'",
													'CC'	=> "'".substr($row['ukuran'], 0, 50)."'",
													'Bahan'	=> "'".substr($row['bahan'], 0, 50)."'",
													'Tgl_Perolehan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'Nomor_Pabrik'	=> "'".substr($row['no_pabrik'], 0, 50)."'",
													'Nomor_Rangka'	=> "'".substr($row['no_rangka'], 0, 50)."'",
													'Nomor_Mesin'	=> "'".substr($row['no_mesin'], 0, 50)."'",
													'Nomor_Polisi'	=> "'".substr($row['no_polisi'], 0, 10)."'",
													'Nomor_BPKB'	=> "'".substr($row['no_bpkb'], 0, 50)."'",
													'Asal_usul'	=> "'".substr($row['asal'], 0, 50)."'",
													'Kondisi'	=> "'".$kondisi."'",
													'Masa_Manfaat'	=> "'0'",
													'Tahun'	=> "'".$row['thn_beli']."'",
													'Tgl_Pembukuan'	=> "'".$tgl_pembukuan." 00:00:00'",
													'Nilai_Sisa'	=> "NULL"
												);
											}else if($table_aset_simda == 'Ta_KIB_C'){
												$options_columns_custom = array(
													'Harga' => "'".$row['harga']."'",
										   			'Bertingkat_Tidak' => "'".$kontruksi_tingkat."'",
										   			'Beton_Tidak' => "'".$kontruksi_beton."'",
										   			'Tgl_Perolehan' => "'".$row['tgl_dok_gedung']." 00:00:00'",
										   			'Luas_Lantai' => "'".$row['luas_lantai']."'",
										   			'Lokasi' => "'".substr($row['alamat'], 0, 255)."'",
										   			'Dokumen_Tanggal' => "'".$row['tgl_dok_gedung']." 00:00:00'",
										   			'Dokumen_Nomor' => "'".substr($row['no_dok_gedung'], 0, 50)."'",
										   			'Status_Tanah' => "'".substr($row['status_tanah'], 0, 50)."'",
										   			'Tgl_Pembukuan' => "'".$tgl_pembukuan." 00:00:00'",
													'Kd_Pemilik'	=> "12",
													'Kd_Tanah1'	=> "NULL",
													'Kd_Tanah2'	=> "NULL",
													'Kd_Tanah3'	=> "NULL",
													'Kd_Tanah4'	=> "NULL",
													'Kd_Tanah5'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kondisi'	=> "'".$row['kondisi']."'",
													'Masa_Manfaat'	=> "'0'",
													'Nilai_Sisa'	=> "NULL",
										   			'Keterangan' => "'".$keterangan."'",
													'Tahun'	=> "NULL",
													'Kd_Tanah8'	=> "NULL",
													'Kd_Tanah80'	=> "NULL",
													'Kd_Tanah81'	=> "NULL",
													'Kd_Tanah82'	=> "NULL",
													'Kd_Tanah83'	=> "NULL",
													'Kd_Tanah84'	=> "NULL",
													'Kd_Tanah85'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kd_Tanah0'	=> "NULL"
												);
											}else if($table_aset_simda == 'Ta_KIB_D'){
												$options_columns_custom = array(
								   					'Konstruksi' => "'".$row['kontruksi']."'",
								   					'Panjang' => "'".$row['panjang']."'",
								   					'Lebar' => "'".$row['lebar']."'",
								   					'Luas' => "'".$row['luas']."'",
													'Harga' => "'".$row['harga']."'",
										   			'Tgl_Perolehan' => "'".$row['dok_tanggal']." 00:00:00'",
										   			'Lokasi' => "'".substr($row['letak'], 0, 255)."'",
										   			'Dokumen_Tanggal' => "'".$row['dok_tanggal']." 00:00:00'",
										   			'Dokumen_Nomor' => "'".substr($row['dok_nomor'], 0, 50)."'",
										   			'Status_Tanah' => "'".substr($row['status_tanah'], 0, 50)."'",
										   			'Tgl_Pembukuan' => "'".$tgl_pembukuan." 00:00:00'",
													'Kd_Pemilik'	=> "12",
													'Kd_Tanah1'	=> "NULL",
													'Kd_Tanah2'	=> "NULL",
													'Kd_Tanah3'	=> "NULL",
													'Kd_Tanah4'	=> "NULL",
													'Kd_Tanah5'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kondisi'	=> "'".$row['kondisi']."'",
													'Masa_Manfaat'	=> "'0'",
													'Nilai_Sisa'	=> "NULL",
										   			'Keterangan' => "'".$keterangan."'",
													'Tahun'	=> "NULL",
													'Kd_Tanah8'	=> "NULL",
													'Kd_Tanah80'	=> "NULL",
													'Kd_Tanah81'	=> "NULL",
													'Kd_Tanah82'	=> "NULL",
													'Kd_Tanah83'	=> "NULL",
													'Kd_Tanah84'	=> "NULL",
													'Kd_Tanah85'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kd_Tanah0'	=> "NULL"
												);
											}else if($table_aset_simda == 'Ta_KIB_E'){
												$options_columns_custom = array(
								   					'Judul' => "'".$row['buku_judul']."'",
								   					'Pencipta' => "'".$row['buku_pencipta']."'",
								   					'Bahan' => "'".$row['buku_spesifikasi']."'",
								   					'Ukuran' => "'".$row['hewan_tumbuhan_ukuran']."'",
													'Harga' => "'".$row['harga']."'",
										   			'Tgl_Perolehan' => "'".$row['tgl_pengadaan']." 00:00:00'",
										   			'Tgl_Pembukuan' => "'".$tgl_pembukuan." 00:00:00'",
													'Kd_Pemilik'	=> "12",
													'Kd_Tanah1'	=> "NULL",
													'Kd_Tanah2'	=> "NULL",
													'Kd_Tanah3'	=> "NULL",
													'Kd_Tanah4'	=> "NULL",
													'Kd_Tanah5'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kondisi'	=> "'1'",
													'Masa_Manfaat'	=> "'0'",
													'Nilai_Sisa'	=> "NULL",
										   			'Keterangan' => "'".$keterangan."'",
													'Tahun'	=> "NULL",
													'Kd_Tanah8'	=> "NULL",
													'Kd_Tanah80'	=> "NULL",
													'Kd_Tanah81'	=> "NULL",
													'Kd_Tanah82'	=> "NULL",
													'Kd_Tanah83'	=> "NULL",
													'Kd_Tanah84'	=> "NULL",
													'Kd_Tanah85'	=> "NULL",
													'Kd_Tanah'	=> "NULL",
													'Kd_Tanah0'	=> "NULL"
												);
											}
											$n_options_columns = array_merge($options_columns, $options_columns_custom);
											$columns = array();
											$values = array();
											$update_columns = array();
											foreach ($n_options_columns as $k => $v) {
												$columns[] = $k;
												$values[] = $v;
												$update_columns[] = $k.'='.$v;
											}
									   		if(empty($cek_aset)){
												$sql = "
													INSERT INTO $table_aset_simda (
														".implode(', ', $columns)."
													) VALUES (
														".implode(', ', $values)."
													)
												";
												$row['sql_insert_simda'] = $sql;
											}else{
												$sql = "
													UPDATE $table_aset_simda SET
														".implode(', ', $update_columns)."
													WHERE kd_prov=$kd_prov
									   				AND kd_kab_kota=$kd_kab_kota
									   				AND kd_bidang=$kd_bidang
									   				AND kd_unit=$kd_unit
									   				AND kd_sub=$kd_sub
									   				AND kd_upb=$kd_upb
									   				AND kd_aset8 IN (".$kd_aset.")
										   			AND kd_aset80 IN (".$kd_aset0.")
										   			AND kd_aset81 IN (".$kd_aset1.")
										   			AND kd_aset82 IN (".$kd_aset2.")
										   			AND kd_aset83 IN (".$kd_aset3.")
										   			AND kd_aset84 IN (".$kd_aset4.")
										   			AND kd_aset85 IN (".$kd_aset5.")
										   			".$where_simda."
												";
												$row['sql_update_simda'] = $sql;
											}

											// insert atau update table aset di simda
											$this->CurlSimda(array(
												'query' => $sql
											));

											// proses select dan (insert atau update) data aset pemeliharaan
											if($table_aset_simda == 'Ta_KIB_A'){
												$o_columns = array(
													'IDPemda'	=> "'".$id_pemda."'",
													'Kd_Prov'	=> "".$kd_prov."",
													'Kd_Kab_Kota'	=> "".$kd_kab_kota."",
													'Kd_Bidang'	=> "".$kd_bidang."",
													'Kd_Unit'	=> "".$kd_unit."",
													'Kd_Sub'	=> "".$kd_sub."",
													'Kd_UPB'	=> "".$kd_upb."",
													'Kd_Aset1'	=> "NULL",
													'Kd_Aset2'	=> "NULL",
													'Kd_Aset3'	=> "NULL",
													'Kd_Aset4'	=> "NULL",
													'Kd_Aset5'	=> "NULL",
													'No_Register'	=> "".$no_register."",
													'Kd_Pemilik'	=> "12",
													'Tgl_Perolehan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'No_SP2D'	=> "NULL",
													'No_ID'	=> "NULL",
													'Kd_Kecamatan'	=> "NULL",
													'Kd_Desa'	=> "NULL",
													'Kd_Prov1'	=> "NULL",
													'Kd_Kab_Kota1'	=> "NULL",
													'Kd_Bidang1'	=> "NULL",
													'Kd_Unit1'	=> "NULL",
													'Kd_Sub1'	=> "NULL",
													'Kd_UPB1'	=> "NULL",
													'No_Register1'	=> "NULL",
													'Invent'	=> "NULL",
													'No_SKGuna'	=> "NULL",
													'Kd_Penyusutan'	=> "NULL",
													'Kd_Data'	=> "'2'",
													'Kd_Alasan'	=> "''",
													'Log_User'	=> "'art'",
													'Log_entry'	=> "'".date('Y-m-d H:i:s')."'",
													'Nm_Rekanan'	=> "NULL",
													'Alamat_Reakanan'	=> "NULL",
													'Tgl_Mulai'	=> "NULL",
													'Tgl_Selesai'	=> "NULL",
													'Kd_KA'	=> "'1'",
													'Kd_Koreksi'	=> "NULL",
													'IDData'	=> "NULL",
													'Kd_Aset8'	=> "".$kd_aset."",
													'Kd_Aset80'	=> "".$kd_aset0."",
													'Kd_Aset81'	=> "".$kd_aset1."",
													'Kd_Aset82'	=> "".$kd_aset2."",
													'Kd_Aset83'	=> "".$kd_aset3."",
													'Kd_Aset84'	=> "".$kd_aset4."",
													'Kd_Aset85'	=> "".$kd_aset5.""
												);
												$tgl_sertifikat = 'null';
												if(!empty($row['nomor_serti'])){
													$tgl_sertifikat = "'".$row['tgl_serti']." 00:00:00"."'";
												}
												$columns_custom = array(
													'Tgl_Pembukuan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'Tahun'	=> "NULL",
													'Luas_M2'	=> "NULL",
													'Alamat'	=> "'".substr($row['alamat'], 0, 255)."'",
													'Hak_Tanah'	=> "'Hak Pakai'",
													'Sertifikat_Tanggal'	=> $tgl_sertifikat,
													'Sertifikat_Nomor'	=> "'".$row['nomor_serti']."'",
													'Penggunaan'	=> "'".substr($row['guna'], 0, 50)."'",
													'Asal_usul'	=> "'Pembelian'"
												);
												$sql = '
													SELECT
														*
													FROM '.$table_aset_p_spbmd.'
													WHERE id_tanah='.$row['id_tanah'];
											   	$ret['sql_'.$table_aset_p_spbmd] = $sql;
												$result_p = $dbh->query($sql);
												$kd_id = 0;
												$sql = "
									   				SELECT TOP 1
								   						No_Urut
												  	FROM $table_aset_p_simda
												  	WHERE IDPemda='".$id_pemda."'
												  	ORDER by No_Urut DESC
									   			";
									   			$urut_aset_p = $this->CurlSimda(array(
													'query' => $sql
												));
												if(!empty($urut_aset_p)){
													$kd_id = $urut_aset_p[0]->No_Urut;
												}
												$row['kd_id_default'] = $kd_id;
												$row['kd_id_sql'] = $sql;
												$o_columns = array_merge($o_columns, $columns_custom);
												while($row_p = $result_p->fetch(PDO::FETCH_NAMED)) {
													$columns_custom2 = array();
													$columns_custom2['Kd_Riwayat'] = 2;
													$columns_custom2['Tgl_Dokumen'] = "'".$row_p['tgl_pelihara']."'";

													// jika tgl dokumen lebih kecil dari tbl perolehan, maka tgl dokumen = tgl perolehan
													$tgl_perolehan = new Datetime($row['tgl_pengadaan']);
													$tgl_dokumen = new Datetime($row_p['tgl_pelihara']);
													if($tgl_dokumen < $tgl_perolehan){
														$columns_custom2['Tgl_Dokumen'] = "'".$row['tgl_pengadaan']."'";
													}

													$columns_custom2['Harga'] = $row_p['biaya_pelihara'];
													$columns_custom2['Keterangan'] = "'".$row_p['jenis_pelihara']."'";
													$no_dokumen = $row_p['bukti_pelihara'];
													if(empty($no_dokumen)){
														$no_dokumen = '-';
													}
													$columns_custom2['No_Dokumen'] = "'".$no_dokumen."'";

													$sql = "
										   				SELECT TOP 1
									   						*
													  	FROM $table_aset_p_simda
													  	WHERE IDPemda='".$id_pemda."'
													  		AND Kd_Riwayat=".$columns_custom2['Kd_Riwayat']."
													  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
													  		AND Harga='".$columns_custom2['Harga']."'
													  		AND Keterangan=".$columns_custom2['Keterangan']."
													  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
										   			";
										   			$cek_aset_p = $this->CurlSimda(array(
														'query' => $sql
													));
													$update_columns = array();
													$columns = array();
													$values = array();
													$oo_columns = array_merge($o_columns, $columns_custom2);
													if(empty($cek_aset_p)){
														$kd_id++;
														$oo_columns['Kd_Id'] = $kd_id;
														foreach ($oo_columns as $kk => $vv) {
															$columns[] = $kk;
															$values[] = $vv;
														}
														$sql = "
															INSERT INTO $table_aset_p_simda (
																".implode(', ', $columns)."
															) VALUES (
																".implode(', ', $values)."
															)
														";
														$row['sql_insert_simda_p'] = $sql;
													}else{
														foreach ($oo_columns as $kk => $vv) {
															$update_columns[] = $kk.'='.$vv;
														}
														$sql = "
															UPDATE $table_aset_p_simda SET
																".implode(', ', $update_columns)."
														  	WHERE IDPemda='".$id_pemda."'
														  		AND Kd_Id=".$columns_custom2['Kd_Riwayat']."
														  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
														  		AND Harga='".$columns_custom2['Harga']."'
														  		AND Keterangan=".$columns_custom2['Keterangan']."
														  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
														";
														$row['sql_update_simda_p'] = $sql;
													}

													// echo ' | insert-update = '. $sql;
													// insert atau update table pemeliharaan aset di simda
													$this->CurlSimda(array(
														'query' => $sql
													));
												}
											}else if($table_aset_simda == 'Ta_KIB_C'){
												$o_columns = array(
													'IDPemda'	=> "'".$id_pemda."'",
													'Kd_Prov'	=> "".$kd_prov."",
													'Kd_Kab_Kota'	=> "".$kd_kab_kota."",
													'Kd_Bidang'	=> "".$kd_bidang."",
													'Kd_Unit'	=> "".$kd_unit."",
													'Kd_Sub'	=> "".$kd_sub."",
													'Kd_UPB'	=> "".$kd_upb."",
													'Kd_Aset1'	=> "NULL",
													'Kd_Aset2'	=> "NULL",
													'Kd_Aset3'	=> "NULL",
													'Kd_Aset4'	=> "NULL",
													'Kd_Aset5'	=> "NULL",
													'No_Register'	=> "".$no_register."",
													'Kd_Pemilik'	=> "12",
													'Tgl_Perolehan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'No_SP2D'	=> "NULL",
													'No_ID'	=> "NULL",
													'Kd_Kecamatan'	=> "NULL",
													'Kd_Desa'	=> "NULL",
													'Kd_Prov1'	=> "NULL",
													'Kd_Kab_Kota1'	=> "NULL",
													'Kd_Bidang1'	=> "NULL",
													'Kd_Unit1'	=> "NULL",
													'Kd_Sub1'	=> "NULL",
													'Kd_UPB1'	=> "NULL",
													'No_Register1'	=> "NULL",
													'Invent'	=> "NULL",
													'No_SKGuna'	=> "NULL",
													'Kd_Penyusutan'	=> "NULL",
													'Kd_Data'	=> "'2'",
													'Kd_Alasan'	=> "''",
													'Log_User'	=> "'art'",
													'Log_entry'	=> "'".date('Y-m-d H:i:s')."'",
													'Nm_Rekanan'	=> "NULL",
													'Alamat_Reakanan'	=> "NULL",
													'Tgl_Mulai'	=> "NULL",
													'Tgl_Selesai'	=> "NULL",
													'Kd_KA'	=> "'1'",
													'Kd_Koreksi'	=> "NULL",
													'IDData'	=> "NULL",
													'Kd_Aset8'	=> "".$kd_aset."",
													'Kd_Aset80'	=> "".$kd_aset0."",
													'Kd_Aset81'	=> "".$kd_aset1."",
													'Kd_Aset82'	=> "".$kd_aset2."",
													'Kd_Aset83'	=> "".$kd_aset3."",
													'Kd_Aset84'	=> "".$kd_aset4."",
													'Kd_Aset85'	=> "".$kd_aset5.""
												);
												$tgl_sertifikat = 'null';
												if(!empty($row['nomor_serti'])){
													$tgl_sertifikat = "'".$row['tgl_serti']." 00:00:00"."'";
												}
												$columns_custom = array(
													'Tgl_Pembukuan'	=> "'".$row['tgl_pengadaan']." 00:00:00'",
													'Tahun'	=> "NULL",
													'Luas_M2'	=> "NULL",
													'Alamat'	=> "'".substr($row['alamat'], 0, 255)."'",
													'Hak_Tanah'	=> "'Hak Pakai'",
													'Sertifikat_Tanggal'	=> $tgl_sertifikat,
													'Sertifikat_Nomor'	=> "'".$row['nomor_serti']."'",
													'Penggunaan'	=> "'".substr($row['guna'], 0, 50)."'",
													'Asal_usul'	=> "'Pembelian'"
												);
												$sql = '
													SELECT
														*
													FROM '.$table_aset_p_spbmd.'
													WHERE id_gedung='.$row['id_gedung'];
											   	$ret['sql_'.$table_aset_p_spbmd] = $sql;
												$result_p = $dbh->query($sql);
												$kd_id = 0;
												$sql = "
									   				SELECT TOP 1
								   						No_Urut
												  	FROM $table_aset_p_simda
												  	WHERE IDPemda='".$id_pemda."'
												  	ORDER by No_Urut DESC
									   			";
									   			$urut_aset_p = $this->CurlSimda(array(
													'query' => $sql
												));
												if(!empty($urut_aset_p)){
													$kd_id = $urut_aset_p[0]->No_Urut;
												}
												$row['kd_id_default'] = $kd_id;
												$row['kd_id_sql'] = $sql;
												$o_columns = array_merge($o_columns, $columns_custom);
												while($row_p = $result_p->fetch(PDO::FETCH_NAMED)) {
													$columns_custom2 = array();
													$columns_custom2['Kd_Riwayat'] = 2;
													$columns_custom2['Tgl_Dokumen'] = "'".$row_p['tgl_pelihara']."'";

													// jika tgl dokumen lebih kecil dari tbl perolehan, maka tgl dokumen = tgl perolehan
													$tgl_perolehan = new Datetime($row['tgl_pengadaan']);
													$tgl_dokumen = new Datetime($row_p['tgl_pelihara']);
													if($tgl_dokumen < $tgl_perolehan){
														$columns_custom2['Tgl_Dokumen'] = "'".$row['tgl_pengadaan']."'";
													}

													$columns_custom2['Harga'] = $row_p['biaya_pelihara'];
													$columns_custom2['Keterangan'] = "'".$row_p['jenis_pelihara']."'";
													$no_dokumen = $row_p['bukti_pelihara'];
													if(empty($no_dokumen)){
														$no_dokumen = '-';
													}
													$columns_custom2['No_Dokumen'] = "'".$no_dokumen."'";

													$sql = "
										   				SELECT TOP 1
									   						*
													  	FROM $table_aset_p_simda
													  	WHERE IDPemda='".$id_pemda."'
													  		AND Kd_Riwayat=".$columns_custom2['Kd_Riwayat']."
													  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
													  		AND Harga='".$columns_custom2['Harga']."'
													  		AND Keterangan=".$columns_custom2['Keterangan']."
													  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
										   			";
										   			$cek_aset_p = $this->CurlSimda(array(
														'query' => $sql
													));
													$update_columns = array();
													$columns = array();
													$values = array();
													$oo_columns = array_merge($o_columns, $columns_custom2);
													if(empty($cek_aset_p)){
														$kd_id++;
														$oo_columns['Kd_Id'] = $kd_id;
														foreach ($oo_columns as $kk => $vv) {
															$columns[] = $kk;
															$values[] = $vv;
														}
														$sql = "
															INSERT INTO $table_aset_p_simda (
																".implode(', ', $columns)."
															) VALUES (
																".implode(', ', $values)."
															)
														";
														$row['sql_insert_simda_p'] = $sql;
													}else{
														foreach ($oo_columns as $kk => $vv) {
															$update_columns[] = $kk.'='.$vv;
														}
														$sql = "
															UPDATE $table_aset_p_simda SET
																".implode(', ', $update_columns)."
														  	WHERE IDPemda='".$id_pemda."'
														  		AND Kd_Id=".$columns_custom2['Kd_Riwayat']."
														  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
														  		AND Harga='".$columns_custom2['Harga']."'
														  		AND Keterangan=".$columns_custom2['Keterangan']."
														  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
														";
														$row['sql_update_simda_p'] = $sql;
													}

													// echo ' | insert-update = '. $sql;
													// insert atau update table pemeliharaan aset di simda
													$this->CurlSimda(array(
														'query' => $sql
													));
												}
											}else if($table_aset_simda == 'Ta_KIB_D'){
												$o_columns = array(
													'IDPemda'	=> "'".$id_pemda."'",
													'Kd_Prov'	=> "".$kd_prov."",
													'Kd_Kab_Kota'	=> "".$kd_kab_kota."",
													'Kd_Bidang'	=> "".$kd_bidang."",
													'Kd_Unit'	=> "".$kd_unit."",
													'Kd_Sub'	=> "".$kd_sub."",
													'Kd_UPB'	=> "".$kd_upb."",
													'Kd_Aset1'	=> "NULL",
													'Kd_Aset2'	=> "NULL",
													'Kd_Aset3'	=> "NULL",
													'Kd_Aset4'	=> "NULL",
													'Kd_Aset5'	=> "NULL",
													'No_Register'	=> "".$no_register."",
													'Kd_Pemilik'	=> "12",
													'Tgl_Perolehan'	=> "'".$row['dok_tanggal']." 00:00:00'",
													'No_SP2D'	=> "NULL",
													'No_ID'	=> "NULL",
													'Kd_Kecamatan'	=> "NULL",
													'Kd_Desa'	=> "NULL",
													'Kd_Prov1'	=> "NULL",
													'Kd_Kab_Kota1'	=> "NULL",
													'Kd_Bidang1'	=> "NULL",
													'Kd_Unit1'	=> "NULL",
													'Kd_Sub1'	=> "NULL",
													'Kd_UPB1'	=> "NULL",
													'No_Register1'	=> "NULL",
													'Invent'	=> "NULL",
													'No_SKGuna'	=> "NULL",
													'Kd_Penyusutan'	=> "NULL",
													'Kd_Data'	=> "'2'",
													'Kd_Alasan'	=> "''",
													'Log_User'	=> "'art'",
													'Log_entry'	=> "'".date('Y-m-d H:i:s')."'",
													'Nm_Rekanan'	=> "NULL",
													'Alamat_Reakanan'	=> "NULL",
													'Tgl_Mulai'	=> "NULL",
													'Tgl_Selesai'	=> "NULL",
													'Kd_KA'	=> "'1'",
													'Kd_Koreksi'	=> "NULL",
													'IDData'	=> "NULL",
													'Kd_Aset8'	=> "".$kd_aset."",
													'Kd_Aset80'	=> "".$kd_aset0."",
													'Kd_Aset81'	=> "".$kd_aset1."",
													'Kd_Aset82'	=> "".$kd_aset2."",
													'Kd_Aset83'	=> "".$kd_aset3."",
													'Kd_Aset84'	=> "".$kd_aset4."",
													'Kd_Aset85'	=> "".$kd_aset5.""
												);
												$columns_custom = array(
													'Tgl_Pembukuan'	=> "'".$row['dok_tanggal']." 00:00:00'",
													'Tahun'	=> "NULL",
													'Konstruksi' => "'".$row['kontruksi']."'",
													'Panjang' => "'".$row['panjang']."'",
													'Lebar' => "'".$row['lebar']."'",
													'Luas' => "'".$row['luas']."'",
													'Dokumen_Tanggal' => "'".$row['dok_tanggal']." 00:00:00'",
													'Dokumen_Nomor' => "'".$row['dok_nomor']."'",
													'Lokasi'	=> "'".substr($row['letak'], 0, 255)."'",
													'Status_Tanah'	=> "'".$row['status_tanah']."'",
													'Asal_usul'	=> "'Pembelian'"
												);
												$sql = '
													SELECT
														*
													FROM '.$table_aset_p_spbmd.'
													WHERE id_jalan_irigasi='.$row['id_jalan_irigasi'];
											   	$ret['sql_'.$table_aset_p_spbmd] = $sql;
												$result_p = $dbh->query($sql);
												$kd_id = 0;
												$sql = "
									   				SELECT TOP 1
								   						No_Urut
												  	FROM $table_aset_p_simda
												  	WHERE IDPemda='".$id_pemda."'
												  	ORDER by No_Urut DESC
									   			";
									   			$urut_aset_p = $this->CurlSimda(array(
													'query' => $sql
												));
												if(!empty($urut_aset_p)){
													$kd_id = $urut_aset_p[0]->No_Urut;
												}
												$row['kd_id_default'] = $kd_id;
												$row['kd_id_sql'] = $sql;
												$o_columns = array_merge($o_columns, $columns_custom);
												while($row_p = $result_p->fetch(PDO::FETCH_NAMED)) {
													$columns_custom2 = array();
													$columns_custom2['Kd_Riwayat'] = 2;
													$columns_custom2['Tgl_Dokumen'] = "'".$row_p['tgl_pelihara']."'";

													// jika tgl dokumen lebih kecil dari tbl perolehan, maka tgl dokumen = tgl perolehan
													$tgl_perolehan = new Datetime($row['dok_tanggal']);
													$tgl_dokumen = new Datetime($row_p['tgl_pelihara']);
													if($tgl_dokumen < $tgl_perolehan){
														$columns_custom2['Tgl_Dokumen'] = "'".$row['dok_tanggal']."'";
													}

													$columns_custom2['Harga'] = $row_p['biaya_pelihara'];
													$columns_custom2['Keterangan'] = "'".$row_p['jenis_pelihara']."'";
													$no_dokumen = $row_p['bukti_pelihara'];
													if(empty($no_dokumen)){
														$no_dokumen = '-';
													}
													$columns_custom2['No_Dokumen'] = "'".$no_dokumen."'";

													$sql = "
										   				SELECT TOP 1
									   						*
													  	FROM $table_aset_p_simda
													  	WHERE IDPemda='".$id_pemda."'
													  		AND Kd_Riwayat=".$columns_custom2['Kd_Riwayat']."
													  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
													  		AND Harga='".$columns_custom2['Harga']."'
													  		AND Keterangan=".$columns_custom2['Keterangan']."
													  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
										   			";
										   			$cek_aset_p = $this->CurlSimda(array(
														'query' => $sql
													));
													$update_columns = array();
													$columns = array();
													$values = array();
													$oo_columns = array_merge($o_columns, $columns_custom2);
													if(empty($cek_aset_p)){
														$kd_id++;
														$oo_columns['Kd_Id'] = $kd_id;
														foreach ($oo_columns as $kk => $vv) {
															$columns[] = $kk;
															$values[] = $vv;
														}
														$sql = "
															INSERT INTO $table_aset_p_simda (
																".implode(', ', $columns)."
															) VALUES (
																".implode(', ', $values)."
															)
														";
														$row['sql_insert_simda_p'] = $sql;
													}else{
														foreach ($oo_columns as $kk => $vv) {
															$update_columns[] = $kk.'='.$vv;
														}
														$sql = "
															UPDATE $table_aset_p_simda SET
																".implode(', ', $update_columns)."
														  	WHERE IDPemda='".$id_pemda."'
														  		AND Kd_Id=".$columns_custom2['Kd_Riwayat']."
														  		AND Tgl_Dokumen=".$columns_custom2['Tgl_Dokumen']."
														  		AND Harga='".$columns_custom2['Harga']."'
														  		AND Keterangan=".$columns_custom2['Keterangan']."
														  		AND No_Dokumen=".$columns_custom2['No_Dokumen']."
														";
														$row['sql_update_simda_p'] = $sql;
													}

													// echo ' | insert-update = '. $sql;
													// insert atau update table pemeliharaan aset di simda
													$this->CurlSimda(array(
														'query' => $sql
													));
												}
											}

											// script untuk melakukan check query yang double
											$where = "
												SELECT * FROM $table_aset_simda
												WHERE kd_prov=$kd_prov
								   				AND kd_kab_kota=$kd_kab_kota
								   				AND kd_bidang=$kd_bidang
								   				AND kd_unit=$kd_unit
								   				AND kd_sub=$kd_sub
								   				AND kd_upb=$kd_upb
								   				AND kd_aset8 IN (".$kd_aset.")
									   			AND kd_aset80 IN (".$kd_aset0.")
									   			AND kd_aset81 IN (".$kd_aset1.")
									   			AND kd_aset82 IN (".$kd_aset2.")
									   			AND kd_aset83 IN (".$kd_aset3.")
									   			AND kd_aset84 IN (".$kd_aset4.")
									   			AND kd_aset85 IN (".$kd_aset5.")
									   			".$where_simda;
											if(empty($cek_unik[$where])){
												$cek_unik[$where] = $sql;
											}else{
												$where = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $where);
												$cek_double[] = preg_replace('!\s+!', ' ', $where);
											}
										}
						   			}else{
							   			$row['status'] = 'error';
							   			$row['message'] = 'Rekening tidak ditemukan di SIMDA BMD untuk jenis_barang="'.$row['jenis_barang'].' ('.$rek_all.')". Perbaiki data mapping Rekening "'.$nama_type.'"!';
							   		}
					   			}else{
						   			$row['status'] = 'error';
						   			$row['message'] = 'Sub Unit tidak ditemukan di SIMDA BMD untuk kode mapping "'.$kd_lok_simda.'". Perbaiki data mapping SKPD untuk kd_lokasi="'.$row['kd_lokasi_spbmd'].'"!';
						   		}
						   		$aset[] = $row;
						   	}
						   	$ret['data'] = $aset;
						   	$ret['double'] = $cek_double;
						}
					}else{
						$ret['status'] = 'error';
						$ret['message'] = 'Koneksi database SPBMD gagal!';
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

    function CekNull($number, $length=2){
        $l = strlen($number);
        $ret = '';
        for($i=0; $i<$length; $i++){
            if($i+1 > $l){
                $ret .= '0';
            }
        }
        $ret .= $number;
        return $ret;
    }

	function get_id_pemda($options){
		$kd_prov = $options['kd_prov'];
		$kd_kab_kota = $options['kd_kab_kota'];
		$kd_bidang = $options['kd_bidang'];
		$kd_unit = $options['kd_unit'];
		$kd_sub = $options['kd_sub'];
		$kd_upb = $options['kd_upb'];
		$table = $options['table'];
		$no_urut = 1000000;
		$id_pemda = $this->CekNull($kd_bidang).$this->CekNull($kd_unit).$this->CekNull($kd_sub, 3).$this->CekNull($kd_upb, 3);
		$sql = "
				SELECT TOP 1
					idpemda
		  	FROM $table
		  	WHERE kd_prov=$kd_prov
   				AND kd_kab_kota=$kd_kab_kota
   				AND kd_bidang=$kd_bidang
   				AND kd_unit=$kd_unit
   				AND kd_sub=$kd_sub
   				AND kd_upb=$kd_upb
   			ORDER by IDPemda DESC
			";
			$cek_max = $this->CurlSimda(array(
			'query' => $sql
		));
		if(!empty($cek_max)){
			$no_urut = str_replace($id_pemda, '', $cek_max[0]->idpemda);
		}
		$no_urut++;
		$id_pemda .= $no_urut;
		return $id_pemda;
	}

	function get_no_register($options){
		$kd_prov = $options['kd_prov'];
		$kd_kab_kota = $options['kd_kab_kota'];
		$kd_bidang = $options['kd_bidang'];
		$kd_unit = $options['kd_unit'];
		$kd_sub = $options['kd_sub'];
		$kd_upb = $options['kd_upb'];
		$kd_aset = $options['kd_aset'];
		$kd_aset0 = $options['kd_aset0'];
		$kd_aset1 = $options['kd_aset1'];
		$kd_aset2 = $options['kd_aset2'];
		$kd_aset3 = $options['kd_aset3'];
		$kd_aset4 = $options['kd_aset4'];
		$kd_aset5 = $options['kd_aset5'];
		$table = $options['table'];
		$no_register = 0;
		$sql = "
				SELECT TOP 1
					no_register
		  	FROM $table
		  	WHERE kd_prov=$kd_prov
   				AND kd_kab_kota=$kd_kab_kota
   				AND kd_bidang=$kd_bidang
   				AND kd_unit=$kd_unit
   				AND kd_sub=$kd_sub
   				AND kd_upb=$kd_upb
   				AND kd_aset8 IN (".$kd_aset.")
	   			AND kd_aset80 IN (".$kd_aset0.")
	   			AND kd_aset81 IN (".$kd_aset1.")
	   			AND kd_aset82 IN (".$kd_aset2.")
	   			AND kd_aset83 IN (".$kd_aset3.")
	   			AND kd_aset84 IN (".$kd_aset4.")
	   			AND kd_aset85 IN (".$kd_aset5.")
   			ORDER by no_register DESC
			";
			$cek_max = $this->CurlSimda(array(
			'query' => $sql
		));
		if(!empty($cek_max)){
			$no_register = $cek_max[0]->no_register;
		}
		$no_register++;
		return $no_register;
	}

	public function get_skpd_mapping(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil get skpd mapping!'
		);
		if (
			!empty($_POST) 
		) {
			$dbh = $this->connect_spbmd();
			if($dbh){
				$kd_lokasi_mapping = array();
				$result = $dbh->query('SELECT * FROM mst_kl_sub_unit');
			   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
			   		$val_mapping = get_option( '_crb_simda_bmd_sub_unit_'.$row['kd_lokasi'] );
			     	if(!empty($val_mapping)){
			     		$row['val_mapping'] = $val_mapping;
			     		$kd_lokasi_mapping[] = $row;
			     	}
			   	}
			   	$ret['data'] = $kd_lokasi_mapping;
			}else{
				$ret['status'] = 'error';
				$ret['message'] = 'Tidak terkoneksi ke database SPBMD!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function mapping_skpd(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT * FROM mst_kl_sub_unit');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$val_mapping = get_option( '_crb_simda_bmd_sub_unit_'.$row['kd_lokasi'] );
		   		$nama_simda = "";
		   		$alamat_simda = "";
		     	if(!empty($val_mapping)){
			   		$kd_lok_simda = $val_mapping;
			   		$_kd_lok_simda = explode('.', $kd_lok_simda);
			   		if(!empty($_kd_lok_simda[5])){
				   		$kd_prov = $_kd_lok_simda[0];
				   		$kd_kab_kota = $_kd_lok_simda[1];
				   		$kd_bidang = $_kd_lok_simda[2];
				   		$kd_unit = $_kd_lok_simda[3];
				   		$kd_sub = $_kd_lok_simda[4];
				   		$kd_upb = $_kd_lok_simda[5];
				   		$sql = "
				   			SELECT 
				   				* 
				   			FROM ref_upb 
				   			where kd_prov=$kd_prov
				   				AND kd_kab_kota=$kd_kab_kota
				   				AND kd_bidang=$kd_bidang
				   				AND kd_unit=$kd_unit
				   				AND kd_sub=$kd_sub
				   				AND kd_upb=$kd_upb
				   		";
				   		$upb_simda = $this->CurlSimda(array(
							'query' => $sql
						));
				   		$nama_simda = $upb_simda[0]->Nm_UPB;
				   		$alamat_simda = "";
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_lokasi']."</td>
		     			<td>".$row['NAMA_sub_unit']."</td>
		     			<td>".$row['ALAMAT_sub_unit']."</td>
		     			<td>".$nama_simda."</td>
		     			<td>".$val_mapping."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='6'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping SKPD</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Lokasi</th>
						<th class='text-center'>Nama SKPD</th>
						<th class='text-center'>Alamat</th>
						<th class='text-center'>Kode Lokasi SIMDA BMD</th>
						<th class='text-center'>Nama SKPD SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_tanah(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT jenis_barang FROM `tanah` GROUP by jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_tanah_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='4'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Tanah</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_mesin(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `mesin` GROUP by kd_barang, jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_mesin_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_barang']."</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='5'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Mesin</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Barang</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_bangunan(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `gedung` GROUP by kd_barang, jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_bangunan_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_barang']."</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='5'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Bangunan</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Barang</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_jalan_irigrasi(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `jalan_irigasi` GROUP by kd_barang, jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_jalan_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_barang']."</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='5'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Jalan Irigrasi</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Barang</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_aset_tetap(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `aset_tetap` GROUP by kd_barang, jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_aset_tetap_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_barang']."</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='5'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Aset Tetap</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Barang</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}

	function mapping_konstruksi_dalam_pengerjaan(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		$body_table = "";
		$dbh = $this->connect_spbmd();
		if($dbh){
			$no = 0;
			$result = $dbh->query('SELECT kd_barang, jenis_barang FROM `kontruksi_dlm_pengerjaan` GROUP by kd_barang, jenis_barang');
		   	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		   		$no++;
		   		$key = $this->trim_text($row['kd_barang'].'-'.$row['jenis_barang']);
		   		$val_mapping = get_option( '_crb_simda_bmd_rek_kontruksi_'.$key );
		   		$nama_simda = "";
		     	if(!empty($val_mapping)){
		     		$rek = explode('.', $val_mapping);
				   	if(!empty($rek[6])){
			     		$kd_aset = $rek[0];
			   			$kd_aset0 = $rek[1];
			   			$kd_aset1 = $rek[2];
			   			$kd_aset2 = $rek[3];
			   			$kd_aset3 = $rek[4];
			   			$kd_aset4 = $rek[5];
			   			$kd_aset5 = $rek[6];
			   			$sql = "
				   			SELECT 
				   				* 
				   			FROM Ref_Rek5_108 
				   			where kd_aset IN (".$kd_aset.")
					   			AND kd_aset0 IN (".$kd_aset0.")
					   			AND kd_aset1 IN (".$kd_aset1.")
					   			AND kd_aset2 IN (".$kd_aset2.")
					   			AND kd_aset3 IN (".$kd_aset3.")
					   			AND kd_aset4 IN (".$kd_aset4.")
					   			AND kd_aset5 IN (".$kd_aset5.")
				   		";
				   		$row['sql_rek_simda'] = $sql;
				   		$rek_simda = $this->CurlSimda(array(
							'query' => $sql
						));
						$nama_simda = $rek_simda[0]->Nm_Aset5;
				   	}
		     	}
		     	$body_table .= "
		     		<tr>
		     			<td class='text-center'>$no</td>
		     			<td>".$row['kd_barang']."</td>
		     			<td>".$row['jenis_barang']."</td>
		     			<td class='text-center'>".$val_mapping."</td>
		     			<td>".$nama_simda."</td>
		     		</tr>
		     	";
		   	}
		}else{
			$body_table = "<tr><td class='text-center' colspan='5'>Koneksi database SPBMD gagal!</td></tr>";
		}
		$body = "
			<h1 class='text-center'>Data Mapping Rekening Kontruksi Dalam Pengerjaan</h1>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th class='text-center'>No</th>
						<th class='text-center'>Kode Barang</th>
						<th class='text-center'>Nama Jenis</th>
						<th class='text-center'>Kode Jenis SIMDA BMD</th>
						<th class='text-center'>Nama Jenis SIMDA BMD</th>
					</tr>
				</thead>
				<tbody>
					".$body_table."
				</tbody>
			</table>
		";
		return $body;
	}
}
