<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])) {
    $simpan_db = true;
    $wpdb->update('data_laporan_kib_c', array('active' => 0), array('active' => 1));
}

$no = 0;
if ($simpan_db) {
	$mapping_rek_db = $wpdb->get_results("
	    SELECT *
	    FROM data_mapping_rek_c
	    WHERE active=1
	", ARRAY_A);

	$mapping_rek = [];
	foreach ($mapping_rek_db as $value) {
	    $mapping_rek[$value['kode_rekening_spbmd']] = $value;
	}
	$sql = '
	    SELECT
	        m.kd_lokasi as kd_lokasi_spbmd,
	        m.*,
	        s.* 
	    FROM gedung m
	    LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
	    ORDER by m.kd_lokasi ASC, m.kd_barang ASC, m.tgl_dok_gedung ASC';
	$result = $dbh->query($sql);

	$aset = [];

	while ($row = $result->fetch(PDO::FETCH_NAMED)) {
	    $row['harga'] = $row['harga'] / $row['jumlah'];
	    for ($i = 1; $i <= $row['jumlah']; $i++) {
	        $harga_pemeliharaan = 0;
	        $sql_harga_pemeliharaan = $dbh->query($wpdb->prepare("
	            SELECT
	                biaya_pelihara
	            FROM pemeliharaan_gedung
	        	WHERE id_gedung = %d
	        ", $row['id_gedung']));
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
	        $no_register = $this->get_no_register(array(
	            'table' => 'data_laporan_kib_c',
	            'kd_lokasi' => $row['kd_lokasi_spbmd'],
	            'kd_aset' => $kode_rek
	        ));
	        $data = array(
                'nama_skpd' => $row['NAMA_sub_unit'],
                'kode_skpd' => '',
                'nama_unit' => $row['jenis_barang'],
                'kode_unit' => $row['kd_barang'],
                'kode_lokasi' => $row['kd_lokasi_spbmd'],
                'kode_aset' => $kode_rek,
                'nama_aset' => $nama_rek,
                'tanggal_perolehan' => '',
                'tanggal_pengadaan' => $row['tgl_dok_gedung'],
                'no_register' => $no_register,
                'asal_usul' => 'Pembelian',
                'keterangan' => $row['keterangan'],
                'tingkat'=> $row['kontruksi_tingkat'],
                'beton'=> $row['kontruksi_gedung'],
                'luas_bangunan'=> $row['luas_lantai'],
                'alamat' => $row['alamat'],
                'luas_tanah' => $row['luas_tanah'],
                'kode_tanah' => $row['no_kode_tanah'],
                'satuan' => '',
                'klasifikasi' => '',
                'umur_ekonomis' => 0,
                'masa_pakai' => '',
                'bulan_terpakai' => '',
                'total_bulan_terpakai' => '',                          
                'penyusutan_ke' => '',
                'penyusutan_per_tanggal' => '',
                'nilai_dasar_perhitungan' => '',
                'nilai_penyusutan_per_tahun' => '',
                'nilai_aset' => '',
                'beban_penyusutan' => '',
                'akumulasi_penyusutan' => '',
                'nilai_buku' => '',
                'nilai_perolehan' => $row['harga'],
                'jumlah_barang' => $row['jumlah'],
                'active' => 1
	        );
	        $cek_id = $wpdb->get_var($wpdb->prepare("
	            SELECT id
	            FROM data_laporan_kib_c
	            WHERE kode_aset=%s AND kode_lokasi=%s AND no_register=%d
	        ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register));
	        if (empty($cek_id)) {
	            $wpdb->insert('data_laporan_kib_c', $data);
	        } else {
	            $wpdb->update('data_laporan_kib_c', $data, array('id' => $cek_id));
	        }
	    }
	}
}else{
    $data_laporan_kib_c = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_c
        WHERE active=1
        ORDER by nama_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
        LIMIT 50
    ", ARRAY_A);

	$body = '';
    foreach ($data_laporan_kib_c as $get_laporan) {
        $no++;
		$body .= '
			<tr>
	            <td>' . $no . '</td>
                <td>' . $get_laporan['nama_skpd'] . '</td> 
                <td>' . $get_laporan['kode_skpd'] . '</td> 
                <td>' . $get_laporan['nama_unit'] . '</td> 
                <td>' . $get_laporan['kode_lokasi'] . '</td> 
                <td>' . $get_laporan['kode_aset'] . '</td> 
                <td>' . $get_laporan['nama_aset'] . '</td> 
                <td>' . $get_laporan['tanggal_perolehan'] . '</td> 
                <td>' . $get_laporan['tanggal_pengadaan'] . '</td> 
                <td>' . $get_laporan['no_register'] . '</td> 
                <td>' . $get_laporan['asal_usul'] . '</td> 
                <td>' . $get_laporan['keterangan'] . '</td> 
                <td>' . $get_laporan['tingkat'] . '</td>
                <td>' . $get_laporan['beton'] . '</td>
                <td>' . $get_laporan['luas_bangunan'] . '</td>
                <td>' . $get_laporan['alamat'] . '</td> 
                <td>' . $get_laporan['luas_tanah'] . '</td> 
                <td>' . $get_laporan['kode_tanah'] . '</td> 
                <td>' . $get_laporan['satuan'] . '</td> 
                <td>' . $get_laporan['klasifikasi'] . '</td> 
                <td>' . $get_laporan['umur_ekonomis'] . '</td> 
                <td>' . $get_laporan['masa_pakai'] . '</td> 
                <td>' . $get_laporan['bulan_terpakai'] . '</td> 
                <td>' . $get_laporan['total_bulan_terpakai'] . '</td>                          
                <td>' . $get_laporan['penyusutan_ke'] . '</td> 
                <td>' . $get_laporan['penyusutan_per_tanggal'] . '</td> 
        		<td class="text-right">'.number_format($get_laporan['nilai_perolehan'],0,",",".").'</td> 
                <td>' . $get_laporan['nilai_aset'] . '</td> 
                <td>' . $get_laporan['nilai_dasar_perhitungan'] . '</td> 
                <td>' . $get_laporan['nilai_penyusutan_per_tahun'] . '</td> 
                <td>' . $get_laporan['beban_penyusutan'] . '</td> 
                <td>' . $get_laporan['akumulasi_penyusutan'] . '</td> 
                <td>' . $get_laporan['nilai_buku'] . '</td> 
                <td>' . $get_laporan['jumlah_barang'] . '</td> 
        	</tr>';
    }
}

if ($simpan_db) {
    die();
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
    #tabel_laporan_kib_c th, 
    #tabel_laporan_kib_c td {
        text-align: center;
        vertical-align: middle;
    }
    #tabel_laporan_kib_c thead{
        position: sticky;
        top: -6px;
        background: #ffc491;
    }
    #tabel_laporan_kib_c tfoot{
        position: sticky;
        bottom: -6px;
        background: #ffc491;
    }
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px; margin: 0 0 3rem 0;">
            <h1 class="text-center" style="margin: 3rem;">Halaman Laporan KIB C</h1>
            <div style="margin-bottom: 25px;">
                <button class="btn btn-warning" onclick="export_data();">Export Data</button>
            </div>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_c" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; border-collapse: collapse; width: 100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NAMA OPD</th>
                            <th>KODE OPD</th>
                            <th>NAMA UNIT</th>
                            <th>KODE LOKASI</th>
                            <th>KODE ASET 108</th>
                            <th>NAMA ASET</th>
                            <th>TANGGAL PEROLEHAN</th>
                            <th>TANGGAL PENGADAAN</th>
                            <th>NOMOR REGISTER</th>
                            <th>ASALUSUL</th>
                            <th>KETERANGAN</th>
                            <th>BERTINGKAT</th>
                            <th>BETON</th>
                            <th>LUAS BANGUNAN</th>
                            <th>ALAMAT</th>
                            <th>LUAS TANAH</th>
                            <th>NO KODE TANAH</th>
                            <th>SATUAN</th>
                            <th>KLASIFIKASI ASET</th>
                            <th>UMUR EKONOMIS</th>
                            <th>MASA PAKAI</th>
                            <th>BULAN TERPAKAI</th>
                            <th>TOTAL BULAN TERPAKAI</th>
                            <th>PENYUSUTAN TAHUN KE -</th>
                            <th>PENYUSUTAN PER TANGGAL</th>
                            <th>NILAI PEROLEHAN</th>
                            <th>NILAI ASET</th>
                            <th>NILAI DASAR PERHITUNGAN SUSUT</th>
                            <th>NILAI PENYUSUTAN PER TAHUN</th>
                            <th>BEBAN PENYUSUTAN</th>
                            <th>AKUMULASI PENYUSUTAN</th>
                            <th>NILAI BUKU</th>
                            <th>KUANTITAS / JUMLAH BARANG<th>
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