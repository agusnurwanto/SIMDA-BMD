<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
global $wpdbx;
$dbh = $this->connect_spbmd();

	$where_skpd_list = '';
	if (!empty($_GET) && !empty($_GET['kd_lokasi'])) {
		$skpd_list = explode(',', $_GET['kd_lokasi']);
		foreach($skpd_list as $k => $v){
			$skpd_list[$k] = $wpdb->prepare('%d', $v);
		}
	    $where_skpd_list = 'AND m.kd_lokasi IN ('.implode(',', $skpd_list).')';
	}

$page = 1;
if (!empty($_GET) && !empty($_GET['hal'])) {
    $page = $_GET['hal'];
}

$per_page = 200;
if (!empty($_GET) && !empty($_GET['per_hal'])) {
    $per_page = $_GET['per_hal'];
}
$start_page = ($page - 1) * $per_page;

$nomor_urut = $start_page;
if (!empty($_GET) && !empty($_GET['nomor_urut'])) {
    $nomor_urut = $_GET['nomor_urut'] + 200;
}

$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])) {
    $simpan_db = true;
    if (!empty($where_skpd_list)) {
    	$wpdb->query("
    		UPDATE data_laporan_kib_b m 
    		set m.active=0 
    		where m.active=1 
                ".str_replace('m.kd_lokasi', 'm.kode_lokasi', $where_skpd_list)
    	);
    }else if ($page == 1) {
        $wpdb->update(
            'data_laporan_kib_b',
            array('active' => 0),
            array('active' => 1)
        );
    }
}
$no = $nomor_urut;

if ($simpan_db) {
    $mapping_opd = $this->get_mapping_skpd();
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
    if(!empty($where_skpd_list)){
	    $sql = '
	        SELECT 
	        	m.kd_lokasi as kd_lokasi_spbmd, 
	        	m.*, 
	        	s.*, 
	        	k.*
	        FROM mesin m
	        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi = s.kd_lokasi
	        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang = k.kd_barang
	        WHERE m.milik = 12
	        	'.$where_skpd_list;

    }else{
	    $sql = $wpdb->prepare(
	        '
	        SELECT 
	        	m.kd_lokasi as kd_lokasi_spbmd, 
	        	m.*, 
	        	s.*, 
	        	k.*
	        FROM mesin m
	        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi = s.kd_lokasi
	        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang = k.kd_barang
	        WHERE m.milik = 12
	        LIMIT %d, %d
	        ',
	        $start_page,
	        $per_page
	    );
	}

    $result = $dbh->query($sql);

    $no = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $sisa_bagi = $row['harga'] % $row['jumlah'];
        $harga_asli = ($row['harga'] - $sisa_bagi) / $row['jumlah'];

        $kode_rek = $row['kd_barang'] . ' (Belum dimapping)';
        $nama_rek = '';
        if (!empty($mapping_rek[$row['kd_barang']])) {
            $kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
            $nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
        }
        $cek_ids = $wpdb->get_results($wpdb->prepare("
            SELECT 
                id,
                no_register
            FROM data_laporan_kib_b
            WHERE kode_aset = %s 
              AND kode_lokasi = %s
              AND id_mesin = %d
        ", $kode_rek, $row['kd_lokasi_spbmd'], $row['id_mesin']), ARRAY_A);

        $cek_id = array();
        foreach ($cek_ids as $val) {
            $cek_id[$val['no_register']] = $val;
        }

        $insert_multi = array();

        $penyusutan_per_tahun = 0;
        $beban_penyusutan = 0;
        $akumulasi_penyusutan = 0;
        $nilai_buku = 0;
        $penyusutan = $wpdb->get_row(
            $wpdb->prepare("
                SELECT
                    penyusutan_per_tahun,
                    nilai_buku_skr
                FROM penyusutan_mesin_2023
                WHERE id_mesin = %d
            ", $row['id_mesin']),
            ARRAY_A
        );
        for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
            if ($no_register == $row['jumlah']) {
                $harga = $harga_asli + $sisa_bagi;
            } else {
                $harga = $harga_asli;
            }

            $nama_induk = $row['NAMA_sub_unit'];
            $kode_induk = '';
            $kd_lokasi_mapping = $row['kd_lokasi_spbmd'];
            if (!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']])) {
                if (!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['nama_induk'])) {
                    $nama_induk = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['nama_induk'];
                }
                if (!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kode_induk'])) {
                    $kode_induk = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kode_induk'];
                }
                if (!empty($mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kd_lokasi'])) {
                    $kd_lokasi_mapping = $mapping_opd['lokasi'][$row['kd_lokasi_spbmd']]['kd_lokasi'];
                }
            }

            if (!empty($row['asal'])) {
                switch ($row['asal']) {
                    case 'Dibeli':
                        $asal = 'Pengadaan APBD';
                        break;
                    case 'Hibah':
                        $asal = 'Hibah';
                        break;
                    case 'Lainnya':
                    case 'Lainya':
                        $asal = 'Perolehan Lainnya';
                        break;
                    default:
                        $asal = 'Pengadaan APBD';
                        break;
                }
            } else {
                $asal = 'Pengadaan APBD';
            }

            switch (true) {
                case $harga == 0:
                case $harga >= $row['nil_min_kapital']:
                    $klasifikasi = 'Intracountable';
                    break;
                case $harga < $row['nil_min_kapital']:
                    $klasifikasi = 'Ekstracountable';
                    break;
                default:
                    $klasifikasi = 'Intracountable';
                    break;
            }

            $satuan = 'Buah';
            if (substr($row['kd_barang'], 0, 4) === '0202' || substr($row['kd_barang'], 0, 4) === '0203') {
                $satuan = 'Unit';
            }

            if (!empty($penyusutan)) {
                $nilai_buku = $penyusutan['nilai_buku_skr'];
                $akumulasi_penyusutan = $harga - $penyusutan['nilai_buku_skr'];
                $beban_penyusutan = $penyusutan['penyusutan_per_tahun'];
                $penyusutan_per_tahun = $penyusutan['penyusutan_per_tahun'];
            }
            $tanggal_pengadaan = date('d-m-Y', strtotime($row['tgl_pengadaan']));
            $tahun_pengadaan = date('Y', strtotime($row['tgl_pengadaan']));
            $masa_pakai = 0;
            $masa_pakai = ($tahun_pengadaan + $row['umur_ekonomis']) - 1;

            $data = array(
                'nama_skpd' => $nama_induk,
                'kode_skpd' => $kode_induk,
                'nama_unit' => $row['NAMA_sub_unit'],
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
                'asal_usul' => $asal,
                'pengguna' => ucfirst($row['nama_pengguna']),
                'keterangan' => $row['keterangan'],
                'merk' => ucfirst($row['merk']),
                'ukuran' => $row['ukuran'],
                'bahan' => ucfirst($row['bahan']),
                'no_pabrik' => $row['no_pabrik'],
                'no_mesin' => $row['no_mesin'],
                'no_kerangka' => $row['no_rangka'],
                'no_polisi' => $row['no_polisi'],
                'no_bpkb' => $row['no_bpkb'],
                'satuan' => $satuan,
                'bahan_bakar' => null,
                'no_bapp' => null,
                'warna' => null,
                'klasifikasi' => $klasifikasi,
                'umur_ekonomis' => $row['umur_ekonomis'],
                'masa_pakai' => $masa_pakai,
                'nilai_perolehan' => $harga,
                'nilai_aset' => $harga,
                'nilai_dasar_perhitungan' => $harga,
                'nilai_penyusutan_per_tahun' => $penyusutan_per_tahun,
                'beban_penyusutan' => $beban_penyusutan,
                'akumulasi_penyusutan' => $akumulasi_penyusutan,
                'nilai_buku' => $nilai_buku,
                'id_mesin' => $row['id_mesin'],
                'jumlah_barang' => 1,
                'active' => 1
            );

            if (empty($cek_id[$no_register])) {
                $insert_multi[] = $data;
            } else {
                $wpdb->update(
                    'data_laporan_kib_b',
                    $data,
                    array('id' => $cek_id[$no_register]['id'])
                );
            }
        }
        if (!empty($insert_multi)) {
            $new_insert_multi = array();
            foreach($insert_multi as $k => $v){
                $new_insert_multi[] = $v;
                if($k%100 == 0){
                    $wpdbx->insert_multiple('data_laporan_kib_b', $new_insert_multi);
                    $new_insert_multi = array();
                }
            }
            if(!empty($new_insert_multi)){
                $wpdbx->insert_multiple('data_laporan_kib_b', $new_insert_multi);
            }
        }
    }
    die();
} else {
    $sql = '
		SELECT
			COUNT(m.id_mesin) AS jml
		FROM mesin m
		LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
    $result = $dbh->query($sql);
    $jml_all = $result->fetch(PDO::FETCH_NAMED);

    $nomor_urut = $no;
    $next_page = 'hal=' . ($page + 1) . '&per_hal=' . $per_page . '&nomor_urut=' . $nomor_urut;

    $data_laporan_kib_b = $wpdb->get_results(
        $wpdb->prepare("
            SELECT *
            FROM data_laporan_kib_b
            WHERE active=1
            ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
            LIMIT %d, %d
        ", $start_page, $per_page),
        ARRAY_A
    );

    $total_data = $wpdb->get_var("
        SELECT COUNT(*)
        FROM data_laporan_kib_b
        WHERE active=1
        ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
    ");

    $body = '';
    foreach ($data_laporan_kib_b as $get_laporan) {
        $no++;

        $tanggal_pengadaan = date('d-m-Y', strtotime($get_laporan['tanggal_pengadaan']));
        $body .= '
		<tr>
            <td class="text-center">' . $no . '</td>
            <td class="text-left">' . $get_laporan['nama_skpd'] . '</td>
            <td class="text-center">' . $get_laporan['kode_skpd'] . '</td>
            <td class="text-left">' . $get_laporan['nama_lokasi'] . '</td>
            <td class="text-center">' . $get_laporan['kode_lokasi_mapping'] . '</td>
            <td class="text-left">' . $get_laporan['jenis_barang'] . '</td>
            <td class="text-center">' . $get_laporan['kode_barang'] . '</td>
            <td class="text-left">' . $get_laporan['nama_aset'] . '</td>
            <td class="text-center">' . $get_laporan['kode_aset'] . '</td>
            <td class="text-center">' . $tanggal_pengadaan . '</td>
            <td class="text-center">' . $tanggal_pengadaan . '</td>
            <td class="text-center">Baik</td>
            <td class="text-center">' . $get_laporan['no_register'] . '</td>
            <td class="text-left">' . $get_laporan['asal_usul'] . '</td>
            <td class="text-left">' . $get_laporan['pengguna'] . '</td>
            <td class="text-left">' . $get_laporan['keterangan'] . '</td>
            <td class="text-center">' . $get_laporan['merk'] . '</td>
            <td class="text-center">' . $get_laporan['ukuran'] . '</td>
            <td class="text-center">' . $get_laporan['bahan'] . '</td>
            <td class="text-center">' . $get_laporan['warna'] . '</td>
            <td class="text-center">' . $get_laporan['no_pabrik'] . '</td>
            <td class="text-center">' . $get_laporan['no_mesin'] . '</td>
            <td class="text-center">' . $get_laporan['no_kerangka'] . '</td>
            <td class="text-center">' . $get_laporan['no_polisi'] . '</td>
            <td class="text-center">' . $get_laporan['no_bpkb'] . '</td>
            <td class="text-center">' . $get_laporan['bahan_bakar'] . '</td>
            <td class="text-center">' . $get_laporan['satuan'] . '</td>
            <td class="text-center>"' . $get_laporan['no_bapp'] . '</td>
            <td class="text-center">' . $get_laporan['klasifikasi'] . '</td>
            <td class="text-center">' . $get_laporan['umur_ekonomis'] . '</td>
            <td class="text-center">' . $get_laporan['masa_pakai'] . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_perolehan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_aset'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_dasar_perhitungan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_penyusutan_per_tahun'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['beban_penyusutan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['akumulasi_penyusutan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_buku'], 0, ",", ".") . '</td>
            <td class="text-center">' . $get_laporan['jumlah_barang'] . '</td>
        </tr>';
    }
}

$get_skpd = $dbh->prepare("
    SELECT 
        *
    FROM 
        mst_kl_sub_unit
    GROUP BY kd_lokasi
");
$get_skpd->execute();
$skpd_all = $get_skpd->fetchAll(PDO::FETCH_ASSOC);

$skpd_list = '';
if ($skpd_all) {
    foreach ($skpd_all as $skpd) {
        $skpd_list .= '
        <tr>
        	<td class="text-center"><input type="checkbox" value="' . $skpd['kd_lokasi'] . '"></td>
        	<td>' . $skpd['kd_lokasi'] . '</td>
        	<td>' . $skpd['NAMA_sub_unit'] . '</td>
        	<td></td>
        	<td></td>
        </tr>';
    }
}
?>
<style type="text/css">
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px;margin:0 0 3rem 0;">
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB B</h1>
            <div id="option_import" class="row g-3 align-items-center justify-content-center" style="margin-bottom: 15px;">
            </div>
            <div style="margin-bottom: 25px;">
                <button class="btn btn-warning" onclick="showPopup();"><span class="dashicons dashicons-database-import"></span> Impor Data</button>
            </div>
            </div>
            <div class="info-section">
                <span class="label">Total Data :</span>
                <span class="value">
                    <?php echo $no ?> /
                    <?php echo $total_data; ?> /
                    <span id="page_count"></span> Halaman
                </span>
            </div>
            <table id="tabel_laporan_kib_b" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NAMA OPD</th>
                        <th>KODE OPD</th>
                        <th>NAMA LOKASI</th>
                        <th>KODE LOKASI</th>
                        <th>NAMA LAMA</th>
                        <th>KODE LAMA</th>
                        <th>NAMA ASET</th>
                        <th>KODE ASET 108</th>
                        <th>TANGGAL PEROLEHAN</th>
                        <th>TANGGAL PENGADAAN</th>
                        <th>KONDISI</th>
                        <th>NOMOR REGISTER</th>
                        <th>ASAL USUL</th>
                        <th>PENGGUNA</th>
                        <th>KETERANGAN</th>
                        <th>MERK/TYPE</th>
                        <th>UKURAN</th>
                        <th>BAHAN</th>
                        <th>WARNA</th>
                        <th>NO PABRIK</th>
                        <th>NO MESIN</th>
                        <th>NO RANGKA</th>
                        <th>NO POLISI</th>
                        <th>NO BPKB</th>
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
<div class="modal fade" id="modal_migrasi_data" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document" style="width: 80vw;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Migrasi Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            	<h3 class="text-center">Jika kosong maka akan dimigrasi semua lokasi dengan pagination dan jika lokasi dipilih maka filter pagination tidak dipakai atau selalu bernilai 1</h3>
                <table class="table table-bordered" id="pilih_skpd">
                	<thead>
                		<tr>
                			<th class="text-center"><input type="checkbox" class="check-all"></th>
                			<th class="text-center">Kode Lokasi</th>
                			<th class="text-center">Nama Lokasi</th>
                			<th class="text-center">Kode Satker</th>
                			<th class="text-center">Nama Satker</th>
                		</tr>
                	</thead>
                	<tbody><?php echo $skpd_list; ?></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary submitBtn" onclick="export_data();">Proses</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        window.jml_data = <?php echo $jml_all['jml']; ?>;
        window.per_hal = 200;
        window.all_page = Math.ceil(jml_data / per_hal);
        run_download_excel_bmd();
        jQuery('#pilih_skpd').DataTable({
		    "aoColumnDefs": [
		        { "bSortable": false, "aTargets": [0] },
		        { "bSearchable": false, "aTargets": [0] }
		    ],
		    "order": [[2, 'asc'], [3, 'asc']],
		    "lengthMenu": [[5, 20, 100, -1], [5, 20, 100, "All"]]
		});
        jQuery('.check-all').on('click', function(){
        	jQuery(this).closest('table').find('tbody > tr > td > input[type="checkbox"]').prop('checked', jQuery(this).is(':checked'));
        });

        var url = window.location.href.split('?')[0] + '?<?php echo $next_page; ?>';

        let extend_action = '';
        //next page
        extend_action += '<a class="btn btn-primary m-2" href="' + url + '" target="_blank"><span class="dashicons dashicons-controls-forward"></span> Halaman Selanjutnya</a>'

        //option import
        let option_import = '';
        option_import += '  <div class="col-auto">';
        option_import += '    <label for="start_page" class="col-form-label">Mulai dari Halaman:</label>';
        option_import += '  </div>';
        option_import += '  <div class="col-auto">';
        option_import += '    <input type="number" id="start_page" name="start_page" class="form-control" min="1" max="' + all_page + '" value="1">';
        option_import += '  </div>';
        option_import += '  <div class="col-auto">';
        option_import += '    <label for="end_page" class="col-form-label">Selesai di Halaman:</label>';
        option_import += '  </div>';
        option_import += '  <div class="col-auto">';
        option_import += '    <input type="number" id="end_page" name="end_page" class="form-control" min="1" max="' + all_page + '" value="10">';
        option_import += '  </div>';


        jQuery('#action-bmd').append(extend_action);
        jQuery('#option_import').html(option_import);
        jQuery('#page_count').text(all_page);

        jQuery('#tabel_laporan_kib_b').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            fixedHeader: true,
            scrollX: true, // Enables horizontal scrolling
            scrollY: '600px',
            scrollCollapse: true,
            pageLength: 10, // Default number of rows per page
            lengthMenu: [10, 25, 50, 100, 200], // Options for rows per page
        });

    });

    function export_data(no_confirm = false, startPage = false) {
        var start_asli = +jQuery('#start_page').val();
        if (startPage == false) {
            startPage = start_asli
        }
        let endPage = +jQuery('#end_page').val();
        let per_hal = window.per_hal;

        var skpd_list = [];
        jQuery('#pilih_skpd tbody > tr > td > input[type="checkbox"]').map(function(i, b){
        	if(jQuery(b).is(':checked')){
        		skpd_list.push(jQuery(b).val());
        	}
        });
        if(skpd_list.length >= 1){
        	startPage = 1;
        	endPage = 1;
        	start_asli = 1;
        }

        if (no_confirm || confirm('Apakah anda yakin untuk mengimpor data ke database?')) {
            jQuery('#wrap-loading').show();

            if (!startPage || !endPage) {
                alert('Opsi Impor Halaman Belum Dipilih!');
                return;
            }

            if (endPage > window.all_page) {
                alert('Melebihi Total Halaman! Max ' + window.all_page);
                return;
            }

            if (startPage > endPage) {
                alert('Halaman mulai tidak boleh lebih besar dari halaman akhir!');
                return;
            }

            var selisih = (endPage - start_asli) + 1;
            var start_awal = (startPage - start_asli) + 1;
            let progressPercentage = Math.round((start_awal / selisih) * 100);
            jQuery('#persen-loading').html(
                'Export data halaman ' + startPage + ', dari total ' + endPage + ' halaman.<h3>' + progressPercentage + '%</h3>'
            );

            jQuery.ajax({
                url: '?simpan_db=1&hal=' + startPage + '&per_hal=' + per_hal+'&kd_lokasi='+skpd_list.join(','),
                success: function(response) {
                    if (startPage < endPage) {
                        export_data(true, startPage + 1);
                    } else {
                        jQuery('#wrap-loading').hide();
                        alert('Data berhasil diimpor!');
                    }
                },
                error: function(xhr, status, error) {
                    jQuery('#wrap-loading').hide();
                    alert('Terjadi kesalahan: ' + error);
                }
            });
        }
    }

    function showPopup(){
    	jQuery('#modal_migrasi_data').modal('show');
    }
</script>