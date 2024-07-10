<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();

$mapping_rek_db = $wpdb->get_results("
	SELECT
		*
	FROM data_mapping_rek_c
	WHERE active=1
", ARRAY_A);
$mapping_rek = array();
foreach ($mapping_rek_db as $key => $value) {
	$mapping_rek[$value['kode_rekening_spbmd']] = $value;
}


$sql = '
	SELECT
		m.kd_lokasi as kd_lokasi_spbmd,
		m.*,
		s.* 
	FROM gedung m
	LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
$result = $dbh->query($sql);
$aset = array();
$no = 0;
$cek_unik = array();
$cek_double = array();

$body = '';
$no = 0;
while($row = $result->fetch(PDO::FETCH_NAMED)) {
	$row['harga'] = $row['harga']/$row['jumlah'];
	for($i=1; $i<=$row['jumlah']; $i++){
		$no++;
		$harga_pemeliharaan=0;
		$row['harga'] += $harga_pemeliharaan;
		$kode_rek = $row['kd_barang'].' (Belum dimapping)';
		$nama_rek = '';
		if(!empty($mapping_rek[$row['kd_barang']])){
			$kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
			$nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
		}
		$body .= '
		<tr>
			<td>'.$no.'</td>
			<td>'.$row['NAMA_sub_unit'].'</td>
			<td></td>
			<td>'.$row['jenis_barang'].'</td>
			<td>'.$row['kd_barang'].'</td>
			<td>'.$kode_rek.'</td>
			<td>'.$nama_rek.'</td>
			<td>'.$row['kondisi'].'</td>
			<td></td>
			<td></td>
			<td>'.$row['register'].'</td>
			<td>Pembelian</td>
			<td>'.$row['keterangan'].'</td>
			<td>'.$row['kontruksi_tingkat'].'</td>
			<td>'.$row['kontruksi_beton'].'</td>
			<td>'.$row['luas_lantai'].'</td>
			<td>'.$row['alamat'].'</td>
			<td>'.$row['luas_tanah'].'</td>
			<td>'.$row['no_kode_tanah'].'</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>'.$row['jumlah'].'</td>
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
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB C</h1>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_a" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
							<tr>
								<th>No</th>
								<th>NAMA OPD</th>
								<th>ID OPD</th>
								<th>NAMA UNIT</th>
								<th>KODE UNIT</th>
								<th>KODE ASET 108</th>
								<th>NAMA ASET</th>
								<th>KONDISI</th>
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
</script>