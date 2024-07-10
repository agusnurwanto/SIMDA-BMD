<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();

$mapping_rek_db = $wpdb->get_results("
	SELECT
		*
	FROM data_mapping_rek_e
	WHERE active=1
", ARRAY_A);
$mapping_rek = array();
foreach ($mapping_rek_db as $key => $value) {
	$mapping_rek[$value['kode_rekening_spbmd']] = $value;
}

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


$sql = $wpdb->prepare('
	SELECT
		m.kd_lokasi as kd_lokasi_spbmd,
		m.*,
		s.* 
	FROM aset_tetap m
	LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
	LIMIT %d, %d', $start_page, $per_page);
$result = $dbh->query($sql);
$aset = array();
$no = 0;
$cek_unik = array();
$cek_double = array();

$body = '';
$no = $nomor_urut;
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
		$keterangan = '';
		$body .= '
		<tr>
			<td>'.$no.'</td>
			<td>'.$row['NAMA_sub_unit'].'</td>
			<td></td>	
			<td>'.$row['jenis_barang'].'</td>
			<td>'.$row['kd_barang'].'</td>
			<td>'.$kode_rek.'</td>
			<td>'.$nama_rek.'</td>
			<td></td>
			<td></td>
			<td></td>
			<td>'.$row['register'].'</td>
			<td>Pembelian</td>
			<td></td>
			<td></td>
			<td>'.$row['keterangan'].'</td>
			<td>'.$row['buku_pencipta'].'</td>
			<td>'.$row['buku_spesifikasi'].'</td>
			<td></td>
			<td>'.$row['seni_pencipta'].'</td>
			<td>'.$row['seni_bahan'].'</td>
			<td>'.$row['hewan_tumbuhan_jenis'].'</td>
			<td>'.$row['hewan_tumbuhan_ukuran'].'</td>
			<td>'.$row['jumlah'].'</td>
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
		</tr>
		';
	}
}
$nomor_urut = $no;
$next_page = 'hal='.($page+1).'&per_hal='.$per_page.'&nomor_urut='.$nomor_urut;
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
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB E</h1>
            <h5 class="text-center" id="next_page"></h5>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_b" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
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
								<th>KONDISI</th>
								<th>NOMOR REGISTER</th>
								<th>ASAL USUL</th>
								<th>NILAI PEROLEHAN</th>
								<th> ALAMAT </th>
								<th>KETERANGAN</th>
								<th>BUKU PENCIPTA</th>
								<th>SPESIFIKASI</th>
								<th>ASALDAERAH</th>
								<th>PENCIPTA</th>
								<th>BAHAN</th>
								<th>HEWAN JENIS</th>
								<th>UKURAN</th>
								<th>JUMLAH</th>
								<th>SATUAN</th>
								<th>NILAI ASET</th>
								<th>NILAI DASAR PERHITUNGAN SUSUT</th>
								<th>NILAI PENYUSUTAN PER TAHUN</th>
								<th>BEBAN PENYUSUTAN</th>
								<th>AKUMULASI PENYUSUTAN</th>
								<th>NILAI BUKU</th>
								<th>KLASIFIKASI ASET</th>
								<th>UMUR EKONOMIS</th>
								<th>MASA PAKAI</th>
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
	var url = window.location.href.split('?')[0]+'?<?php echo $next_page; ?>';
    jQuery('#next_page').html('<a href="'+url+'" target="_blank">Halaman Selanjutnya</a>');
});
</script>