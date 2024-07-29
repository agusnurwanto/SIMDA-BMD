<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])){
	$simpan_db = true;
}

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
	FROM mesin m
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
		$nama_rek = '';
		$kode_rek = $row['kd_barang'].' (Belum dimapping)';
		if(!empty($mapping_rek[$row['kd_barang']])){
			$kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
			$nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
		}
		if($simpan_db == true){
			$wpdb->insert(
                'data_laporan_kib_b',
                [
				  'nama_skpd' => $row['NAMA_sub_unit'],
				  'kode_skpd' => '',
				  'nama_unit' => $row['jenis_barang'],
				  'kode_unit' => $row['kd_barang'],
				  'kode_aset' => $kode_rek,
				  'nama_aset' => $nama_rek,
				  'tanggal_perolehan' => '',
				  'tanggal_pengadaan' => $row['tgl_pengadaan'],
				  'kondisi' => '',
				  'no_register' => $row['register'],
				  'asal_usul' => 'Pembelian',
				  'alamat' => $row['alamat'],
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
				  'satuan' => '',
				  'no_bapp' => '',
				  'klasifikasi' => '',
				  'umur_ekonomis' => '',
				  'masa_pakai' => '',
				  'klasifikasi' => '',
				  'nilai_perolehan' => '',
				  'nilai_aset' => '',
				  'nilai_dasar_perhitungan' => '',
				  'nilai_penyusutan_per_tahun' => '',
				  'nilai_penyusutan' => '',
				  'beban_penyusutan' => '',
				  'akumulasi_penyusutan' => '',
				  'nilai_buku' => '',
				  'jumlah_barang' => $row['jumlah'],
                ],
            );
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
			<td></td>	
			<td>'.$row['tgl_pengadaan'].'</td>
			<td></td>	
			<td>'.$row['register'].'</td>	
			<td>Pembelian</td>	
			<td></td>
			<td>'.$row['nama_pengguna'].'</td>
			<td>'.$row['keterangan'].'</td>
			<td>'.$row['merk'].'</td>	
			<td>'.$row['ukuran'].'</td>	
			<td>'.$row['bahan'].'</td>	
			<td></td>	
			<td>'.$row['no_pabrik'].'</td>
			<td>'.$row['no_mesin'].'</td>
			<td>'.$row['no_rangka'].'</td>
			<td>'.$row['no_polisi'].'</td>
			<td>'.$row['no_bpkb'].'</td>
			<td></td>	
			<td></td>	
			<td></td>	
			<td></td>	
			<td>5</td>	
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
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB B</h1>
            <h5 class="text-center" id="next_page"></h5>
            <div class="wrap-table">
            	<div style="margin-bottom: 25px;">
                    <button class="btn btn-warning" onclick="export_data();">Export Data</button>
                </div>
                <table id="tabel_laporan_kib_b" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
							<tr>
								<th>No</th>
								<th>NAMA OPD</th>	
								<th>KODE UNIT OPD</th>	
								<th>NAMA UNIT</th>	
								<th>KODE UNIT</th>	
								<th>KODE ASET 108</th>	
								<th>NAMA ASET</th>	
								<th>TANGGAL PEROLEHAN</th>	
								<th>TANGGAL PENGADAAN</th>	
								<th>KONDISI</th>	
								<th>NOMOR REGISTER</th>	
								<th>ASALUSUL</th>	
								<th>ALAMAT</th>	
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

<script type="text/javascript">
jQuery(document).ready(function(){
	run_download_excel_bmd();
	var url = window.location.href.split('?')[0]+'?<?php echo $next_page; ?>';
    jQuery('#next_page').html('<a href="'+url+'" target="_blank">Halaman Selanjutnya</a>');
});
function export_data(){
    if(confirm('Apakah anda yakin untuk mengirim data ini ke database?')){
        jQuery('#wrap-loading').show();
		jQuery.ajax({
			url:'?simpan_db=1',
			success: function(response) {
				jQuery('#wrap-loading').hide();
				alert('Data berhasil diexport!.');
			}
		});
    }
}
</script>