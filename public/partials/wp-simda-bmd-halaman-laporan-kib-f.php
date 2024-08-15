<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])) {
    $simpan_db = true;
    $wpdb->update('data_laporan_kib_f', array('active' => 0), array('active' => 1));
}

$no = 0;
if ($simpan_db) {
	$mapping_rek_db = $wpdb->get_results("
		SELECT
			*
		FROM data_mapping_rek_f
		WHERE active=1
	", ARRAY_A);
	$mapping_rek = array();
	foreach ($mapping_rek_db as $key => $value) {
		$mapping_rek[$value['kode_rekening_spbmd']] = $value;
	}

	$mapping_opd = $this->get_mapping_skpd();

	$sql = '
		SELECT
			m.kd_lokasi as kd_lokasi_spbmd,
			m.*,
			s.* 
		FROM kontruksi_dlm_pengerjaan m
		LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
	$result = $dbh->query($sql);
	while($row = $result->fetch(PDO::FETCH_NAMED)) {
		$row['harga'] = $row['harga']/$row['jumlah'];
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

			$kode_rek = $row['kd_barang'].' (Belum dimapping)';
			$nilai_kontrak = $row['nilai_kontrak'];
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

	        $data = array(
	            'nama_skpd' => $nama_induk,
	            'kode_skpd' => $kode_induk,
	            'kode_lokasi' => $row['kd_lokasi_spbmd'],
	            'kode_lokasi_mapping' => $kd_lokasi_mapping,
                'kode_barang' => $row['kd_barang'],
                'jenis_barang' => $row['jenis_barang'],
	            'nama_lokasi' => $row['NAMA_sub_unit'],
	            'kode_aset' => $kode_rek,
	            'nama_aset' => $nama_rek,
	            'tanggal_perolehan' => '',
	            'tanggal_pengadaan' => $row['tgl_pengadaan'],
	            'kondisi' => '',
	            'no_register' => $no_register,
	            'asal_usul' => $row['Asal'],
	            'luas_tanah' => $row['Luas'],	
	            'alamat' => $row['alamat'],
	            'keterangan' => '',
	            'satuan' => '',
	            'klasifikasi' => '',
	            'tanggal_sertifikat' => $row['tgl_serti'],
	            'no_sertifikat' => $row['nomor_serti'],
	            'status_sertifikat' => '',
	            'umur_ekonomis' => 0,
	            'masa_pakai' => '',
	            'nilai_perolehan' => $row['harga'],
	            'jumlah_barang' => 1,
	            'active' => 1
	        );
	        $cek_id = $wpdb->get_var($wpdb->prepare("
	            SELECT id
	            FROM data_laporan_kib_f
	            WHERE kode_aset=%s AND kode_lokasi=%s AND no_register=%d
	        ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register));
	        if (empty($cek_id)) {
	            $wpdb->insert('data_laporan_kib_f', $data);
	        } else {
	            $wpdb->update('data_laporan_kib_f', $data, array('id' => $cek_id));
	        }
		}
	}
    die();
}else{
    $data_laporan_kib_f = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_f
        WHERE active=1
        ORDER by nama_skpd ASC, nama_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
    ", ARRAY_A);

	$body = '';
    foreach ($data_laporan_kib_f as $get_laporan) {
        $no++;
		$body .= '
			<tr>
	            <td>' . $no . '</td>
	            <td>' . $get_laporan['nama_skpd'] . '</td>
	            <td>' . $get_laporan['kode_skpd'] . '</td>
	            <td>' . $get_laporan['nama_unit'] . '</td>
	            <td>' . $get_laporan['kode_lokasi_mapping'] . '</td>
	            <td>' . $get_laporan['kode_barang'] . '</td>
	            <td>' . $get_laporan['jenis_barang'] . '</td>
	            <td>' . $get_laporan['kode_aset'] . '</td>
	            <td>' . $get_laporan['nama_aset'] . '</td>
				<td></td>
				<td></td>
				<td></td>
				<td>Pembelian</td>
	            <td class="text-right">'.$this->rupiah($nilai_kontrak).'</td>
				<td>'.$get_laporan['alamat'].'</td>
				<td>'.$get_laporan['keterangan'].'</td>
				<td></td>
				<td>'.$get_laporan['kontruksi_tingkat'].'</td>
				<td>'.$get_laporan['kontruksi_beton'].'</td>
				<td>'.$get_laporan['luas'].'</td>
				<td>'.$get_laporan['tgl_dok'].'</td>
				<td>'.$get_laporan['no_dok'].'</td>
				<td>'.$get_laporan['status_tanah'].'</td>
				<td>'.$get_laporan['jumlah'].'</td>
				<td></td>
				<td></td>
			</tr>
		';
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
        <div style="padding: 10px;margin:0 0 3rem 0;">
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB F</h1>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_a" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
						<tr>
							<th rowspan="2">NO</th>
							<th rowspan="2">NAMA OPD</th>
							<th rowspan="2">KODE OPD</th>
							<th rowspan="2">NAMA UNIT</th>
							<th rowspan="2">KODE LOKASI</th>
                            <th rowspan="2">KODE LAMA</th>
                            <th rowspan="2">NAMA LAMA</th>
							<th rowspan="2">KODE ASET 108</th>
							<th rowspan="2">NAMA ASET</th>
							<th rowspan="2">TANGGAL PEROLEHAN</th>
							<th rowspan="2">TANGGAL PENGADAAN</th>
							<th rowspan="2">NOMOR REGISTER</th>
							<th rowspan="2">ASAL USUL</th>
							<th rowspan="2">NILAI KONTRAK</th>
							<th rowspan="2">ALAMAT</th> 
							<th rowspan="2">KETERANGAN</th>
							<th rowspan="2">Bangunan (P, SP, D)</th>
							<th colspan="2">Konstruksi Bangunan</th>
							<th rowspan="2">Luas (m2)</th>
							<th colspan="2">Dokumen</th>
							<th rowspan="2">STATUS TANAH</th>
							<th rowspan="2">JUMLAH</th>
							<th rowspan="2">SATUAN</th>
							<th rowspan="2">KLASIFIKASI ASET</th>
						</tr>
						<tr>
							<th>Bertingkat/tidak</th>
							<th>Beton/Tidak</th>
							<th>Tanggal</th>
							<th>Nomor</th>
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

<script type="text/javascript">
jQuery(document).ready(function(){
    run_download_excel_bmd();
});
</script>