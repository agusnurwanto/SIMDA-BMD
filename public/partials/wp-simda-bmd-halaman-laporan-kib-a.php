<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])) {
    $simpan_db = true;
    $wpdb->update('data_laporan_kib_a', array('active' => 0), array('active' => 1));
}

$no = 0;
if ($simpan_db) {
	$mapping_rek_db = $wpdb->get_results("
	    SELECT *
	    FROM data_mapping_rek_a
	    WHERE active=1
	", ARRAY_A);

	$mapping_rek = [];
	foreach ($mapping_rek_db as $value) {
	    $mapping_rek[$value['kode_rekening_spbmd']] = $value;
	}

	$mapping_opd = $this->get_mapping_skpd();

	$sql = '
	    SELECT
	        m.kd_lokasi as kd_lokasi_spbmd,
	        m.*,
	        s.* 
	    FROM tanah m
	    LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
	    ORDER by m.kd_lokasi ASC, m.kd_barang ASC, m.tgl_pengadaan ASC';
	$result = $dbh->query($sql);

	while ($row = $result->fetch(PDO::FETCH_NAMED)) {
	    $row['harga'] = $row['harga'] / $row['jumlah'];
	    for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
	    	if($no_register==$row['jumlah']){
	    		$row['harga'] = ceil($row['harga']);
	    	}else{
	    		$row['harga'] = floor($row['harga']);
	    	}
	        $harga_pemeliharaan = 0;
	        $sql_harga_pemeliharaan = $dbh->query($wpdb->prepare("
	            SELECT
	                biaya_pelihara
	            FROM pemeliharaan_tanah
	        	WHERE id_tanah = %d
	        ", $row['id_tanah']));
	        $get_harga = $sql_harga_pemeliharaan->fetch(PDO::FETCH_NAMED);
	        if(!empty($get_harga)){
	        	$harga_pemeliharaan = $get_harga['biaya_pelihara'];
	        }

	        $row['harga'] += $harga_pemeliharaan;
	        $kode_rek = $row['kd_barang'] . ' (Belum dimapping)';
	        $nama_rek = '';
	        if (!empty($mapping_rek[$row['kd_barang']])) {
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

	        $data = array(
	            'nama_skpd' => $nama_induk,
	            'kode_skpd' => $kode_induk,
	            'kode_lokasi' => $row['kd_lokasi_spbmd'],
	            'kode_lokasi_mapping' => $kd_lokasi_mapping,
                'nama_lokasi' => $row['NAMA_sub_unit'],
                'kode_aset' => $kode_rek,
                'nama_aset' => $nama_rek,
                'tanggal_perolehan' => '',
                'tanggal_pengadaan' => $row['tgl_pengadaan'],
                'kondisi' => 'Baik',
                'no_register' => $no_register,
                'asal_usul' => $row['Asal'],
                'luas_tanah' => $row['Luas'],   
                'alamat' => $row['alamat'],
                'keterangan' => $row['Keterangan'],
                'satuan' => 'Meter Persegi',
                'klasifikasi' => 'Intra Countable',
                'tanggal_sertifikat' => $row['tgl_serti'],
                'no_sertifikat' => $row['nomor_serti'],
                'status_sertifikat' => 'Hak Pakai',
                'umur_ekonomis' => 0,
                'masa_pakai' => '',
                'nilai_perolehan' => $row['harga'],
                'guna' => $row['guna'],
                'register_serti' => $row['register_serti'],
                'jumlah_barang' => 1,
                'active' => 1
	        );
	        $cek_id = $wpdb->get_var($wpdb->prepare("
	            SELECT id
	            FROM data_laporan_kib_a
	            WHERE kode_aset=%s AND kode_lokasi=%s AND no_register=%d
	        ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register));
	        if (empty($cek_id)) {
	            $wpdb->insert('data_laporan_kib_a', $data);
	        } else {
	            $wpdb->update('data_laporan_kib_a', $data, array('id' => $cek_id));
	        }
	    }
	}
    die();
}else{
    $data_laporan_kib_a = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_a
        WHERE active=1
        ORDER by nama_skpd ASC, nama_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
    ", ARRAY_A);

	$body = '';
    foreach ($data_laporan_kib_a as $get_laporan) {
        $no++;
        if (empty($get_laporan['guna'])) {
            $get_laporan['guna'] = '-';
        }
        if (empty($get_laporan['keterangan'])) {
            $get_laporan['keterangan'] = '-';
        }
        if (empty($get_laporan['register_serti'])) {
            $get_laporan['register_serti'] = '-';
        }
        if($get_laporan['asal_usul'] == 'Dibeli'){
            $get_laporan['asal_usul'] = 'Pengadaan APBD';
        }else if($get_laporan['asal_usul'] == 'Pemb Prosida'){
            $get_laporan['asal_usul'] = 'Hibah';
        }else if($get_laporan['asal_usul'] == 'Perolehan Zaman Belanda'){
            $get_laporan['asal_usul'] = 'Perolehan Lainnya';
        }else if($get_laporan['asal_usul'] == 'Lainnya'){
            $get_laporan['asal_usul'] = 'Perolehan Lainnya';
        }else if($get_laporan['asal_usul'] == 'Perolehan Jaman Belanda'){
            $get_laporan['asal_usul'] = 'Perolehan Lainnya';
        }else if($get_laporan['asal_usul'] == 'Lainya'){
            $get_laporan['asal_usul'] = 'Perolehan Lainnya';
        }

	    $tanggal_pengadaan = date('d-m-Y', strtotime($get_laporan['tanggal_pengadaan']));
        $body .= '
            <tr>
                <td>' . $no . '</td>
                <td>' . $get_laporan['nama_skpd'] . '</td>
                <td>' . $get_laporan['kode_skpd'] . '</td>
                <td>' . $get_laporan['kode_lokasi_mapping'] . '</td>
                <td>' . $get_laporan['nama_lokasi'] . '</td>
                <td>' . $get_laporan['kode_aset'] . '</td>
                <td>' . $get_laporan['nama_aset'] . '</td>
                <td>' . $tanggal_pengadaan . '</td>
                <td>' . $tanggal_pengadaan . '</td>
                <td>' . $get_laporan['kondisi'] . '</td>
                <td>1</td>
                <td>' . $get_laporan['asal_usul'] . '</td>
                <td>' . $get_laporan['luas_tanah'] . '</td>
                <td>' . $get_laporan['alamat'] . '</td>
                <td>'.$get_laporan['guna'].', '.$get_laporan['keterangan'].', Reg. Sertifikat: '.$get_laporan['register_serti'].'</td>
                <td>' . $get_laporan['satuan'] . '</td>
                <td>' . $get_laporan['klasifikasi'] . '</td>
                <td>' . $get_laporan['tanggal_sertifikat'] . '</td>
                <td>' . $get_laporan['no_sertifikat'] . '</td>
                <td>' . $get_laporan['status_sertifikat'] . '</td>
                <td>' . $get_laporan['umur_ekonomis'] . '</td>
                <td>' . $get_laporan['masa_pakai'] . '</td>
                <td class="text-right">'.number_format($get_laporan['nilai_perolehan'],0,",",".").'</td>
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
    #tabel_laporan_kib_a th, 
    #tabel_laporan_kib_a td {
        text-align: center;
        vertical-align: middle;
    }
    #tabel_laporan_kib_a thead{
        position: sticky;
        top: -6px;
        background: #ffc491;
    }
    #tabel_laporan_kib_a tfoot{
        position: sticky;
        bottom: -6px;
        background: #ffc491;
    }
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px; margin: 0 0 3rem 0;">
            <h1 class="text-center" style="margin: 3rem;">Halaman Laporan KIB A</h1>
            <div style="margin-bottom: 25px;">
                <button class="btn btn-warning" onclick="export_data();">Export Data</button>
            </div>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_a" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; border-collapse: collapse; width: 100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NAMA OPD</th>
                            <th>KODE OPD</th>
                            <th>KODE LOKASI</th>
                            <th>NAMA LOKASI</th>
                            <th>KODE ASET 108</th>
                            <th>NAMA ASET</th>
                            <th>TANGGAL PEROLEHAN</th>
                            <th>TANGGAL PENGADAAN</th>
                            <th>KONDISI</th>
                            <th>NOMOR REGISTER</th>
                            <th>ASALUSUL</th>
                            <th>LUAS TANAH</th>
                            <th>ALAMAT</th>
                            <th>KETERANGAN</th>
                            <th>SATUAN</th>
                            <th>KLASIFIKASI ASET</th>
                            <th>TGL SERTIFIKAT</th>
                            <th>NO SERTIFIKAT</th>
                            <th>STATUS SERTIFIKAT</th>
                            <th>UMUR EKONOMIS</th>
                            <th>MASA PAKAI</th>
                            <th>NILAI PEROLEHAN</th>
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
});
function export_data(){
    if(confirm('Apakah anda yakin untuk mengirim data ini ke database?')){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '?simpan_db=1',
            success: function(response) {
                jQuery('#wrap-loading').hide();
                alert('Data berhasil diexport!');
                // location.reload();
            }
        });
    }
}
</script>