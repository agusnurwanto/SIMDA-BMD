<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();

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


$sql = '
	SELECT
		m.kd_lokasi as kd_lokasi_spbmd,
		m.*,
		s.* 
	FROM kontruksi_dlm_pengerjaan m
	LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
$result = $dbh->query($sql);
$aset = array();
$no = 0;
$cek_unik = array();
$cek_double = array();

$body = '';
$no = 0;
$nilai_kontrak = 0;
while($row = $result->fetch(PDO::FETCH_NAMED)) {
	for($i=1; $i<=$row['jumlah']; $i++){
		$no++;
		$kode_rek = $row['kd_barang'].' (Belum dimapping)';
		$nilai_kontrak = $row['nilai_kontrak'];
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
			<td></td>
			<td>'.$kode_rek.'</td>
			<td>'.$nama_rek.'</td>
			<td></td>
			<td></td>
			<td></td>
			<td>Pembelian</td>
            <td class="text-right">'.$this->rupiah($nilai_kontrak).'</td>
			<td>'.$row['alamat'].'</td>
			<td>'.$row['keterangan'].'</td>
			<td></td>
			<td>'.$row['kontruksi_tingkat'].'</td>
			<td>'.$row['kontruksi_beton'].'</td>
			<td>'.$row['luas'].'</td>
			<td>'.$row['tgl_dok'].'</td>
			<td>'.$row['no_dok'].'</td>
			<td>'.$row['status_tanah'].'</td>
			<td>'.$row['jumlah'].'</td>
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


<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/jszip.js"></script>
<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/xlsx.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    run_download_excel_bmd();
});
</script>