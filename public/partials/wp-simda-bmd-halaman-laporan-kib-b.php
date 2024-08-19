<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();

$page = 1;
if(!empty($_GET) && !empty($_GET['hal'])){
	$page = $_GET['hal'];
}

$per_page = 200;
if(!empty($_GET) && !empty($_GET['per_hal'])){
	$per_page = $_GET['per_hal'];
}
$start_page = ($page-1)*$per_page;

$nomor_urut = $start_page;
if(!empty($_GET) && !empty($_GET['nomor_urut'])){
	$nomor_urut = $_GET['nomor_urut'];
}

$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])){
	$simpan_db = true;
	if($page == 1){
    	$wpdb->update('data_laporan_kib_b', array('active' => 0), array('active' => 1));
	}
}
$no = $nomor_urut;

if ($simpan_db) {
	$mapping_rek_db = $wpdb->get_results("
		SELECT
			*
		FROM data_mapping_rek_b
		WHERE active=1
	", ARRAY_A);
	$mapping_rek = array();
	foreach ($mapping_rek_db as $key => $value) {
		$mapping_rek[$value['kode_rekening_spbmd']] = $value;
	}
	$sql = $wpdb->prepare('
        SELECT 
        	m.kd_lokasi as kd_lokasi_spbmd, 
        	m.*, 
        	s.*, 
        	k.*
        FROM mesin m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi = s.kd_lokasi
        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang = k.kd_barang
        LIMIT %d, %d', $start_page, $per_page);
        
    $result = $dbh->query($sql);

    $no = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
		for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
            if($no_register==$row['jumlah']){
                $row['harga'] = ceil($row['harga']);
            }else{
                $row['harga'] = floor($row['harga']);
            }
            $kode_rek = $row['kd_barang'].' (Belum dimapping)';
            $nama_rek = '';
            if(!empty($mapping_rek[$row['kd_barang']])){
                $kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
                $nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
            }

            $nama_induk = $row['NAMA_sub_unit'];
            $kode_induk = '';
            $kd_lokasi_mapping = $row['kd_lokasi_spbmd'];
            if(!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']])){
                if(!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['nama_induk'])){
                    $nama_induk = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['nama_induk'];
                }
                if(!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kode_induk'])){
                    $kode_induk = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kode_induk'];
                }
                if(!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kd_lokasi'])){
                    $kd_lokasi_mapping = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kd_lokasi'];
                }
            }

            if($row['asal'] == 'Dibeli'){
                $row['asal'] = 'Pengadaan APBD';
            }else if($row['asal'] == 'Lainnya'){
                $row['asal'] = 'Perolehan Lainnya';
            }else if($row['asal'] == 'Lainya'){
                $row['asal'] = 'Perolehan Lainnya';
            }else if($row['asal'] == 'Hibah'){
                $row['asal'] = 'Hibah';
            } else {
                $row['asal'] = 'Pengadaan APBD';
            }
            
            $klasifikasi = 'Intra Countable';
            if ($row['harga'] == 0) {
                $klasifikasi = 'Intra Countable';
            } elseif ($row['harga'] < $row['nil_min_kapital']) {
                $klasifikasi = 'Ekstra Countable';
            } elseif ($row['harga'] >= $row['nil_min_kapital']) {
                $klasifikasi = 'Intra Countable';
            }

            $satuan = 'Buah';
            if (substr($row['kd_barang'], 0, 4) === '0202' || substr($row['kd_barang'], 0, 4) === '0203') {
                $satuan = 'Kendaraan';
            }

		    $penyusutan_per_tahun = 0;
		    $beban_penyusutan = 0;
		    $akumulasi_penyusutan = 0;
		    $nilai_buku = 0;
	        $penyusutan = $wpdb->get_row($wpdb->prepare("
	            SELECT
	                penyusutan_per_tahun,
	                nilai_buku_skr
	            FROM penyusutan_mesin_2023
	        	WHERE id_mesin = %d
	        ", $row['id_mesin']), ARRAY_A);
	        // print_r($penyusutan); die($wpdb->last_query);
	        if(!empty($penyusutan)){
	        	$nilai_buku = $penyusutan['nilai_buku_skr'];
	        	$akumulasi_penyusutan = $row['harga'] - $penyusutan['nilai_buku_skr'];
	        	$beban_penyusutan = $penyusutan['penyusutan_per_tahun'];
	        	$penyusutan_per_tahun = $penyusutan['penyusutan_per_tahun'];
	        }
            $tanggal_pengadaan = date('d-m-Y', strtotime($row['tgl_pengadaan']));
            $tahun_pengadaan = date('Y', strtotime($row['tgl_pengadaan']));
            $masa_pakai = $tahun_pengadaan + $row['umur_ekonomis'] - 1;
            $data = array(
                'nama_skpd' => $nama_induk,
                'kode_skpd' => $kode_induk,
                'nama_unit' => '',
                'kode_unit' => $row['kd_barang'],
                'kode_lokasi' => $row['kd_lokasi_spbmd'],
                'kode_lokasi_mapping' => $kd_lokasi_mapping,
                'nama_lokasi' => $row['NAMA_sub_unit'],
                'kode_barang' => $row['kd_barang'],
                'jenis_barang' => $row['jenis_barang'],
                'kode_aset' => $kode_rek,
                'nama_aset' => $nama_rek,
                'tanggal_perolehan' => $tanggal_pengadaan,
                'tanggal_pengadaan' => $tanggal_pengadaan,
                'kondisi' => 'Baik',
                'no_register' => $no_register,
                'asal_usul' => $row['asal'],
                'pengguna' => $row['nama_pengguna'],
                'keterangan' => $row['keterangan'],
                'merk' => $row['merk'],
                'ukuran' => $row['ukuran'],
                'bahan' => $row['bahan'],
                'warna' => '',
                'no_pabrik' => $row['no_pabrik'],
                'no_mesin' => $row['no_mesin'],
                'no_kerangka' => $row['no_rangka'],
                'no_polisi' => $row['no_polisi'],
                'no_bpkb' => $row['no_bpkb'],
                'bahan_bakar' => '',
                'satuan' => $satuan,
                'no_bapp' => '',
                'klasifikasi' => $klasifikasi,
                'umur_ekonomis' => $row['umur_ekonomis'],
                'masa_pakai' => $masa_pakai,
                'nilai_perolehan' => $row['harga'],
                'nilai_aset' => $row['harga'],
                'nilai_dasar_perhitungan' => $row['harga'],
                'nilai_penyusutan_per_tahun' => $penyusutan_per_tahun,
                'beban_penyusutan' => $beban_penyusutan,
                'akumulasi_penyusutan' => $akumulasi_penyusutan,
                'nilai_buku' => $nilai_buku,
                'jumlah_barang' => 1,
                'active' => 1
            );

            $cek_id = $wpdb->get_var($wpdb->prepare("
                SELECT id
                FROM data_laporan_kib_b
                WHERE kode_aset = %s AND kode_lokasi = %s AND no_register = %d
            ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register));

            if (empty($cek_id)) {
                $wpdb->insert('data_laporan_kib_b', $data);
            } else {
                $wpdb->update('data_laporan_kib_b', $data, ['id' => $cek_id]);
            }
        }
    }
    die();
}
else{
	$sql = '
		SELECT
			COUNT(m.id_mesin) AS jml
		FROM mesin m
		LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
	$result = $dbh->query($sql);
	$jml_all = $result->fetch(PDO::FETCH_NAMED);

	$nomor_urut = $no;
	$next_page = 'hal='.($page+1).'&per_hal='.$per_page.'&nomor_urut='.$nomor_urut;
	
    $data_laporan_kib_b = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_b
        WHERE active=1
        ORDER by kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
        LIMIT $start_page, $per_page
    ", ARRAY_A);
    // print_r($data_laporan_kib_b); die($wpdb->last_query);

	$body = '';
    foreach ($data_laporan_kib_b as $get_laporan) {
        $no++;

        $tanggal_pengadaan = date('d-m-Y', strtotime($get_laporan['tanggal_pengadaan']));
		$body .= '
			<tr>
	            <td>' . $no . '</td>
            <td>' . $get_laporan['nama_skpd'] . '</td>
            <td>' . $get_laporan['kode_skpd'] . '</td>
            <td>' . $get_laporan['nama_unit'] . '</td>
            <td>' . $get_laporan['kode_lokasi_mapping'] . '</td>
            <td>' . $get_laporan['nama_lokasi'] . '</td>
            <td>' . $get_laporan['kode_barang'] . '</td>
            <td>' . $get_laporan['jenis_barang'] . '</td>
            <td>' . $get_laporan['kode_aset'] . '</td>
            <td>' . $get_laporan['nama_aset'] . '</td>
            <td>' . $tanggal_pengadaan . '</td>
            <td>' . $tanggal_pengadaan . '</td>
            <td>Baik</td>
            <td>' . $get_laporan['no_register'] . '</td>
            <td>' . $get_laporan['asal_usul'] . '</td>
            <td>' . $get_laporan['pengguna'] . '</td>
            <td>' . $get_laporan['keterangan'] . '</td>
            <td>' . $get_laporan['merk'] . '</td>
            <td>' . $get_laporan['ukuran'] . '</td>
            <td>' . $get_laporan['bahan'] . '</td>
            <td>' . $get_laporan['warna'] . '</td>
            <td>' . $get_laporan['no_pabrik'] . '</td>
            <td>' . $get_laporan['no_mesin'] . '</td>
            <td>' . $get_laporan['no_kerangka'] . '</td>
            <td>' . $get_laporan['no_polisi'] . '</td>
            <td>' . $get_laporan['no_bpkb'] . '</td>
            <td>' . $get_laporan['bahan_bakar'] . '</td>
            <td>' . $get_laporan['satuan'] . '</td>
            <td>' . $get_laporan['no_bapp'] . '</td>
            <td>' . $get_laporan['klasifikasi'] . '</td>
            <td>' . $get_laporan['umur_ekonomis'] . '</td>
            <td>' . $get_laporan['masa_pakai'] . '</td>
            <td class="text-right">'.number_format($get_laporan['nilai_perolehan'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['nilai_aset'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['nilai_dasar_perhitungan'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['nilai_penyusutan_per_tahun'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['beban_penyusutan'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['akumulasi_penyusutan'],0,",",".").'</td>
            <td class="text-right">'.number_format($get_laporan['nilai_buku'],0,",",".").'</td>
            <td>' . $get_laporan['jumlah_barang'] . '</td>
        </tr>';
    }
}
?>
<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 100vh;
        width: 100%;
    }

    .btn-action-group {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .btn-action-group .btn {
        margin: 0 5px;
    }
    #tabel_laporan_kib_b th, 
    #tabel_laporan_kib_b td {
        text-align: center;
        vertical-align: middle;
    }
    #tabel_laporan_kib_b thead{
        position: sticky;
        top: -6px;
        background: #ffc491;
    }
    #tabel_laporan_kib_b tfoot{
        position: sticky;
        bottom: -6px;
        background: #ffc491;
    }
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px;margin:0 0 3rem 0;">
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB B</h1>
            <h5 class="text-center" id="next_page"></h5>
            <div class="wrap-table">
            	<div style="margin-bottom: 25px;">
                    <button class="btn btn-warning" onclick="export_data(false, 1);">Export Data</button>
                </div>
                <table id="tabel_laporan_kib_b" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
							<tr>
								<th>No</th>
	                            <th>NAMA OPD</th>   
	                            <th>KODE UNIT OPD</th>  
	                            <th>NAMA UNIT</th>  
	                            <th>KODE LOKASI</th>    
	                            <th>NAMA LOKASI</th>
	                            <th>KODE LAMA</th>
	                            <th>NAMA LAMA</th>
	                            <th>KODE ASET 108</th>  
	                            <th>NAMA ASET</th>  
	                            <th>TANGGAL PEROLEHAN</th>  
	                            <th>TANGGAL PENGADAAN</th>  
	                            <th>KONDISI</th>    
	                            <th>NOMOR REGISTER</th> 
	                            <th>ASALUSUL</th>  
	                            <th>PENGGUNA</th>   
	                            <th>KETERANGAN</th> 
	                            <th>MERK / TYPE</th>    
	                            <th>UKURAN</th> 
	                            <th>BAHAN</th>  
	                            <th>WARNA</th>  
	                            <th>NOPABRIK</th>   
	                            <th>NOMESIN</th>    
	                            <th>NORANGKA</th>   
	                            <th>NOPOLISI</th>   
	                            <th>NOBPKB</th> 
	                            <th>BAHAN BAKAR</th>    
	                            <th>SATUAN</th> 
	                            <th>NOBAPP</th> 
	                            <th>KLASIFIKASI ASET</th>   
	                            <th>UMUR EKONOMIS</th>  
	                            <th>MASA PAKAI</th> 
	                            <th>NILAI PEROLEHAN</th>    
	                            <th>NILAI ASET</th> 
	                            <th>NILAI DASAR PERHITUNGAN SUSUT</th>  
	                            <th>NILAI PENYUSUTAN PER TAHUN</th>     
	                            <th>BEBAN PENYUSUTAN</th>   
	                            <th>AKUMULASI PENYUSUTAN</th>   
	                            <th>NILAI BUKU</th>     
	                            <th>KUANTITAS/JUMLAH BARANG</th>
							</tr>
                    </thead>
                    <tbody>
						<?php echo $body; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/jszip.js"></script>
<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/xlsx.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
	run_download_excel_bmd();
	var url = window.location.href.split('?')[0]+'?<?php echo $next_page; ?>';
    jQuery('#next_page').html('<a href="'+url+'" target="_blank">Halaman Selanjutnya</a>');

    window.jml_data = <?php echo $jml_all['jml']; ?>;
    window.per_hal = 200;
    window.all_page = Math.ceil(jml_data/per_hal);
});
function export_data(no_confirm=false, page=1){
    if(no_confirm || confirm('Apakah anda yakin untuk mengirim data ini ke database?')){
        jQuery('#wrap-loading').show();
        jQuery('#persen-loading').html('Export data halaman '+page+', dari total '+all_page+' halaman.<h3>'+Math.round((page/all_page)*100)+'%</h3>');
		jQuery.ajax({
			url:'?simpan_db=1&hal='+page+'&per_hal='+per_hal,
			success: function(response) {
				if(page < all_page){
					export_data(true, page+1);
				}else{
					jQuery('#wrap-loading').hide();
					alert('Data berhasil diexport!.');
				}
			}
		});
    }
}
</script>