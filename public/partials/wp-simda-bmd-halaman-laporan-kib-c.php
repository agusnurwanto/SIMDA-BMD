<?php
// Ensure this file is being included by a parent file
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = !empty($_GET['simpan_db']);

// Deactivate existing data if saving new data to the database
if ($simpan_db) {
    $wpdb->update(
        'data_laporan_kib_c',
        array('active' => 0),
        array('active' => 1)
    );
}
$no = 0;
if ($simpan_db) {
    $mapping_opd = $this->get_mapping_skpd();
    $mapping_rek_db = $wpdb->get_results("
        SELECT *
        FROM data_mapping_rek_c
        WHERE active=1
    ", ARRAY_A);

    // Create an associative array for easy access to mapping records by 'kode_rekening_spbmd'
    $mapping_rek = [];
    foreach ($mapping_rek_db as $mapping) {
        $mapping_rek[$mapping['kode_rekening_spbmd']] = $mapping;
    }

    $sql = "
        SELECT
            m.kd_lokasi AS kd_lokasi_spbmd,
            m.*,
            s.*, 
            k.* 
        FROM gedung m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang=k.kd_barang
        WHERE m.milik != 00
        ORDER BY m.kd_lokasi ASC, m.kd_barang ASC, m.tgl_dok_gedung ASC";

    $result = $dbh->query($sql);

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    	$sisa_bagi = $row['harga']%$row['jumlah'];
    	$harga_asli = ($row['harga']-$sisa_bagi)/$row['jumlah'];

        $harga_pemeliharaan = 0;

        // Fetch harga pemeliharaaan
        $sql_harga_pemeliharaan = $dbh->query(
            $wpdb->prepare("
                SELECT 
                    SUM(biaya_pelihara) as total_biaya_pemeliharaan
                FROM pemeliharaan_gedung
                WHERE id_gedung = %d
            ", $row['id_gedung'])
        );
        $harga_pemeliharaan = $sql_harga_pemeliharaan->fetchcolumn();
        $sisa_bagi_pemeliharaan = $harga_pemeliharaan%$row['jumlah'];
        $harga_pemeliharaan_asli = ($harga_pemeliharaan - $sisa_bagi_pemeliharaan) / $row['jumlah'];

        for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
            if ($no_register == $row['jumlah']) {
                $harga = $harga_asli + $sisa_bagi;
	        	$harga_pemeliharaan = $harga_pemeliharaan_asli + $sisa_bagi_pemeliharaan;
                // if($row['id_gedung'] == '33900202300010'){
	            // 	die('harga tess '.$harga.' = '.$sisa_bagi.' + '. $harga_asli.' | harga pemeliharaan '.$harga_pemeliharaan.' = '.$sisa_bagi_pemeliharaan.' + '.$harga_pemeliharaan_asli.' | total = '.($harga+$harga_pemeliharaan));
                // }
            }else{
            	$harga = $harga_asli;
	        	$harga_pemeliharaan = $harga_pemeliharaan_asli;
            }


            $nilai_aset = 0;
            $akumulasi_penyusutan = 0;
            $umur_ekonomis = 0;
            $kd_lokasi_mapping = null;
            $data_penyusutan = null;

            // Fetch penyusutan gedung
            $sql_penyusutan_gedung_2023 = $dbh->query(
                $wpdb->prepare("
                    SELECT 
                        sisa_ue_stl_sst,
                        penyusutan_skr,
                        nilai_buku_skr,
                        penyusutan_per_tahun,
                        nilai_buku_skr
                    FROM penyusutan_gedung_2023
                    WHERE id_gedung = %d
                ", $row['id_gedung'])
            );
            $data_penyusutan = $sql_penyusutan_gedung_2023->fetch(PDO::FETCH_ASSOC);

            $kode_rek = $row['kd_barang'] . ' (Belum dimapping)';
            $nama_rek = '';
            if (!empty($mapping_rek[$row['kd_barang']])) {
                $kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
                $nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
            }

            if (!empty($row['kondisi'])) {
                switch ($row['kondisi']) {
                    case 1:
                        $kondisi = 'Baik';
                        break;
                    case 2:
                        $kondisi = 'Rusak Ringan';
                        break;
                    case 3:
                        $kondisi = 'Rusak';
                        break;
                    case 4:
                        $kondisi = 'Rusak Berat';
                        break;
                    default:
                        $kondisi = 'Baik';
                        break;
                }
            } else {
                $kondisi = 'Baik';
            }

            if (!empty($row['kontruksi_beton'])) {
                switch ($row['kontruksi_beton']) {
                    case 1:
                        $kontruksi_beton = 'Beton';
                        break;
                    case 2:
                        $kontruksi_beton = 'Tidak';
                        break;
                    default:
                        $kontruksi_beton = 'Tidak';
                        break;
                }
            } else {
                $kontruksi_beton = 'Tidak';
            }

            if (!empty($row['kontruksi_tingkat'])) {
                switch ($row['kontruksi_tingkat']) {
                    case 1:
                        $kontruksi_tingkat = 'Bertingkat';
                        break;
                    case 2:
                        $kontruksi_tingkat = 'Tidak';
                        break;
                    default:
                        $kontruksi_tingkat = 'Tidak';
                        break;
                }
            } else {
                $kontruksi_tingkat = 'Tidak';
            }

            if (!empty($row['asal'])) {
                switch ($row['asal']) {
                    case 1:
                        $asal = 'Pengadaan APBD';
                        break;
                    case 2:
                        $asal = 'Hibah';
                        break;
                    case 99:
                        $asal = 'Perolehan Lainnya';
                        break;
                    default:
                        $asal = 'Pengadaan APBD';
                        break;
                }
            } else {
                $asal = 'Pengadaan APBD';
            }
            $nilai_aset = $harga + $harga_pemeliharaan;

            if (
                !empty($row['kd_barang'])
                && !empty($harga)
            ) {
                $sql_master_kelompok = $dbh->query(
                    $wpdb->prepare("
                        SELECT 
                            nil_min_kapital
                        FROM mst_kb_ss_kelompok
                        WHERE kd_barang = %d
                    ", $row['kd_barang'])
                );

                $nilai_min_kapital = $sql_master_kelompok->fetchColumn();

                if ($nilai_aset < $nilai_min_kapital) {
                    $klasifikasi = "Ekstracountable";
                } else {
                    $klasifikasi = "Intracountable";
                }
            }
            $akumulasi_penyusutan = $nilai_aset - $data_penyusutan['nilai_buku_skr'];
            $year = 0;
            if (!empty($row['tgl_dok_gedung'])) {
                $tanggal_pengadaan = $row['tgl_dok_gedung'];
                $timestamp = strtotime($tanggal_pengadaan);
                $year = date('Y', strtotime($tanggal_pengadaan));
                if ($timestamp !== false) {
                    $formattedDate = date('d-m-Y', $timestamp);
                } else {
                    $formattedDate = "Format tanggal tidak valid!";
                }
            } else {
                $formattedDate = "Tanggal tidak valid atau kosong";
            }
            $kode_induk = '';
            $nama_induk = $row['NAMA_sub_unit'];
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

			// Fetch harga pemeliharaaan
	        $sql_harga_pemeliharaan = $dbh->query(
	            $wpdb->prepare("
	                SELECT 
	                    biaya_pelihara
	                FROM pemeliharaan_gedung
	                WHERE id_gedung = %d
	            ", $row['id_gedung'])
	        );

	        // Fetch tahun harga pemeliharaaan
	        $sql_tahun_harga_pemeliharaan = $dbh->query(
	            $wpdb->prepare("
	                SELECT 
	                    tgl_pelihara,
	                    biaya_pelihara
	                FROM pemeliharaan_gedung
	                WHERE id_gedung = %d
	            ", $row['id_gedung'])
	        );

	        // Fetch masa manfaat
	        $sql_masa_manfaaat = $dbh->query(
	            $wpdb->prepare("
	                SELECT                    
	                    maks_pemeliharaan,
	                    tambah_ue
	                FROM mst_masa_manfaat_renovasi
	                WHERE kd_barang = %d
	                ORDER by maks_pemeliharaan ASC
	            ", $row['kd_barang'])
	        );

	        $masa_manfaat = $sql_masa_manfaaat->fetchAll(PDO::FETCH_ASSOC);
	        $tahun_harga_pemeliharaan = $sql_tahun_harga_pemeliharaan->fetchAll(PDO::FETCH_ASSOC);
	        $harga_pemeliharaan = $sql_harga_pemeliharaan->fetchcolumn();
            // Fetch masa manfaat from mst_masa_manfaat_renovasi
	       	if (
                !empty($masa_manfaat)
                && !empty($tahun_harga_pemeliharaan)
            ) {
                $total_per_tahun = array();

                // print_r($tahun_harga_pemeliharaan);
                
                foreach ($tahun_harga_pemeliharaan as $data) {
                    // die(print_r($data));

                    $tahun = date('Y', strtotime($data['tgl_pelihara'])); // error disini

                    if (isset($total_per_tahun[$tahun])) {
                        $total_per_tahun[$tahun] += $data['biaya_pelihara'];
                    } else {
                        $total_per_tahun[$tahun] = $data['biaya_pelihara'];
                    }
                }

                // print_r($total_per_tahun);
                // print_r($masa_manfaat);

                $tambah_ue_per_tahun = array();
                if (!empty($total_per_tahun)) {
                    foreach ($total_per_tahun as $tahun => $total_biaya) {
                        $total_tambah_ue = 0;
                        $maks_pemeliharaan_terkini = 0;
                        foreach ($masa_manfaat as $manfaat) {

                        	// jika nilai biaya lebih kecil dari nilai maksimal pemeliharaan dan nilai max db lebih kecil dari max saat ini
                            if (
                            	$total_biaya < $manfaat['maks_pemeliharaan']
                            	&& $maks_pemeliharaan_terkini == 0
                            ) {
                            	// echo $total_biaya.' < '.$manfaat['maks_pemeliharaan'].' | ';
                                $total_tambah_ue = $manfaat['tambah_ue'];
                                $maks_pemeliharaan_terkini = $manfaat['maks_pemeliharaan'];
                            }
                        }

                        $tambah_ue_per_tahun[$tahun] = $total_tambah_ue;
                    }
                }

                // die(print_r($tambah_ue_per_tahun));

                if (!empty($tambah_ue_per_tahun)) {
                    foreach ($tambah_ue_per_tahun as $tahun => $tambah_ue) {
                        $row['umur_ekonomis'] += $tambah_ue;
                    }
                }
            }

            $data = array(
                'nama_skpd' => $nama_induk,
                'kode_skpd' => $kode_induk,
                'nama_lokasi' => $row['NAMA_sub_unit'],
                'kode_lokasi' => $row['kd_lokasi_spbmd'],
                'kode_lokasi_mapping' => $kd_lokasi_mapping,
                'kode_barang' => $row['kd_barang'],
                'jenis_barang' => $row['jenis_barang'],
                'kode_aset' => $kode_rek,
                'nama_aset' => $nama_rek,
                'kondisi' => $kondisi,
                'tanggal_perolehan' => $formattedDate,
                'tanggal_pengadaan' => $formattedDate,
                'no_register' => $no_register,
                'asal_usul' => $asal,
                'keterangan' => $row['keterangan'],
                'tingkat' => $kontruksi_tingkat,
                'beton' => $kontruksi_beton,
                'luas_bangunan' => $row['luas_lantai'],
                'alamat' => $row['alamat'],
                'luas_tanah' => $row['luas_tanah'],
                'kode_tanah' => $row['no_kode_tanah'],
                'satuan' => 'Meter Persegi',
                'klasifikasi' => $klasifikasi,
                'umur_ekonomis' => $row['umur_ekonomis'],
                'masa_pakai' => null,
                'bulan_terpakai' => null,
                'total_bulan_terpakai' => null,
                'penyusutan_ke' => null,
                'penyusutan_per_tanggal' => null,
                'nilai_dasar_perhitungan' => $nilai_aset,
                'nilai_penyusutan_per_tahun' => $data_penyusutan['penyusutan_skr'],
                'nilai_perolehan' => $harga,
                'nilai_aset' => $nilai_aset,
                'beban_penyusutan' => $data_penyusutan['penyusutan_skr'],
                'akumulasi_penyusutan' => $akumulasi_penyusutan,
                'nilai_buku' =>  $data_penyusutan['nilai_buku_skr'],
                'id_gedung' =>  $row['id_gedung'],
                'jumlah_barang' => 1,
                'active' => 1
            );

            // Check if the record exists in the database and insert/update accordingly
            $cek_id = $wpdb->get_var(
                $wpdb->prepare("
                    SELECT id
                    FROM data_laporan_kib_c
                    WHERE kode_aset=%s 
                      AND kode_lokasi=%s 
                      AND no_register=%d
                      AND id_gedung=%d
                ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register, $row['id_gedung'])
            );

            if (empty($cek_id)) {
                $wpdb->insert('data_laporan_kib_c', $data);
            } else {
                $wpdb->update('data_laporan_kib_c', $data, array('id' => $cek_id));
            }
        }
    }
} else {
    $data_laporan_kib_c = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_c
        WHERE active=1
        ORDER BY nama_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
    ", ARRAY_A);

    $total_data = $wpdb->get_var("
        SELECT COUNT(*)
        FROM data_laporan_kib_c
        WHERE active=1
        ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
    ");

    $body = '';
    foreach ($data_laporan_kib_c as $get_laporan) {
        $no++;
        $body .= '
            <tr data-id="'.$get_laporan['id_gedung'].'">
                <td class="text-center">' . $no . '</td>
                <td class="text-left">' . $get_laporan['nama_skpd'] . '</td> 
                <td class="text-center">' . $get_laporan['kode_skpd'] . '</td> 
                <td class="text-left">' . $get_laporan['nama_lokasi'] . '</td> 
                <td class="text-center">' . $get_laporan['kode_lokasi_mapping'] . '</td>
                <td class="text-left">' . $get_laporan['jenis_barang'] . '</td>
                <td class="text-center">' . $get_laporan['kode_barang'] . '</td>
                <td class="text-left">' . $get_laporan['nama_aset'] . '</td> 
                <td class="text-left">' . $get_laporan['kode_aset'] . '</td> 
                <td class="text-center">' . $get_laporan['kondisi'] . '</td> 
                <td class="text-center">' . $get_laporan['tanggal_perolehan'] . '</td> 
                <td class="text-center">' . $get_laporan['tanggal_pengadaan'] . '</td> 
                <td class="text-center">' . $get_laporan['no_register'] . '</td> 
                <td class="text-left">' . $get_laporan['asal_usul'] . '</td> 
                <td class="text-left">' . $get_laporan['keterangan'] . '</td>
                <td class="text-left">' . $get_laporan['tingkat'] . '</td>
                <td class="text-left">' . $get_laporan['beton'] . '</td>
                <td class="text-center">' . $get_laporan['luas_bangunan'] . '</td>
                <td class="text-left">' . $get_laporan['alamat'] . '</td> 
                <td class="text-center">' . $get_laporan['luas_tanah'] . '</td> 
                <td class="text-left">' . $get_laporan['kode_tanah'] . '</td> 
                <td class="text-left">' . $get_laporan['satuan'] . '</td> 
                <td class="text-left">' . $get_laporan['klasifikasi'] . '</td> 
                <td class="text-center">' . $get_laporan['umur_ekonomis'] . '</td> 
                <td class="text-left">' . $get_laporan['masa_pakai'] . '</td> 
                <td class="text-left">' . $get_laporan['bulan_terpakai'] . '</td> 
                <td class="text-left">' . $get_laporan['total_bulan_terpakai'] . '</td>                          
                <td class="text-left">' . $get_laporan['penyusutan_ke'] . '</td> 
                <td class="text-left">' . $get_laporan['penyusutan_per_tanggal'] . '</td> 
                <td class="text-right">' . number_format((float) ($get_laporan['nilai_perolehan'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['nilai_aset'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['nilai_dasar_perhitungan'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['nilai_penyusutan_per_tahun'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['beban_penyusutan'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['akumulasi_penyusutan'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-right">' . number_format((float) ($get_laporan['nilai_buku'] ?? 0), 0, ",", ".") . '</td>
                <td class="text-center">' . $get_laporan['jumlah_barang'] . '</td> 
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

    #tabel_laporan_kib_c th,
    #tabel_laporan_kib_c td {
        text-align: center;
        vertical-align: middle;
    }

    #tabel_laporan_kib_c thead {
        position: sticky;
        top: -6px;
        background: #ffc491;
    }

    #tabel_laporan_kib_c tfoot {
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
                <button class="btn btn-warning" onclick="export_data();"><span class="dashicons dashicons-database-import"></span> Impor Data</button>
            </div>
            <div class="info-section">
                    <span class="label">Total Data :</span>
                    <span class="value"><?php echo $no ?> / <?php echo $total_data; ?></span>
                </div>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_c" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; border-collapse: collapse; width: 100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">NAMA OPD</th>
                            <th class="text-center">KODE OPD</th>
                            <th class="text-center">NAMA LOKASI</th>
                            <th class="text-center">KODE LOKASI</th>
                            <th class="text-center">NAMA LAMA</th>
                            <th class="text-center">KODE LAMA</th>
                            <th class="text-center">NAMA ASET</th>
                            <th class="text-center">KODE ASET 108</th>
                            <th class="text-center">KONDISI</th>
                            <th class="text-center">TANGGAL PEROLEHAN</th>
                            <th class="text-center">TANGGAL PENGADAAN</th>
                            <th class="text-center">NOMOR REGISTER</th>
                            <th class="text-center">ASALUSUL</th>
                            <th class="text-center">KETERANGAN</th>
                            <th class="text-center">BERTINGKAT</th>
                            <th class="text-center">BETON</th>
                            <th class="text-center">LUAS BANGUNAN</th>
                            <th class="text-center">ALAMAT</th>
                            <th class="text-center">LUAS TANAH</th>
                            <th class="text-center">NO KODE TANAH</th>
                            <th class="text-center">SATUAN</th>
                            <th class="text-center">KLASIFIKASI ASET</th>
                            <th class="text-center">UMUR EKONOMIS</th>
                            <th class="text-center">MASA PAKAI</th>
                            <th class="text-center">BULAN TERPAKAI</th>
                            <th class="text-center">TOTAL BULAN TERPAKAI</th>
                            <th class="text-center">PENYUSUTAN TAHUN KE -</th>
                            <th class="text-center">PENYUSUTAN PER TANGGAL</th>
                            <th class="text-center">NILAI PEROLEHAN</th>
                            <th class="text-center">NILAI ASET</th>
                            <th class="text-center">NILAI DASAR PERHITUNGAN SUSUT</th>
                            <th class="text-center">NILAI PENYUSUTAN PER TAHUN</th>
                            <th class="text-center">BEBAN PENYUSUTAN</th>
                            <th class="text-center">AKUMULASI PENYUSUTAN</th>
                            <th class="text-center">NILAI BUKU</th>
                            <th class="text-center">KUANTITAS / JUMLAH BARANG</th>
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
    jQuery(document).ready(function() {
        run_download_excel_bmd();
    });

    function export_data() {
        if (confirm('Apakah anda yakin untuk mengimpor data ke database?')) {
            jQuery('#wrap-loading').show();
            jQuery.ajax({
                url: '?simpan_db=1',
                success: function(response) {
                    jQuery('#wrap-loading').hide();
                    alert('Data berhasil diimpor!');
                }
            });
        }
    }
</script>