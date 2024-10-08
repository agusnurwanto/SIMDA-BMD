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
        'data_laporan_kib_d',
        array('active' => 0),
        array('active' => 1)
    );
}
$no = 0;
if ($simpan_db) {
    $mapping_opd = $this->get_mapping_skpd();
    $mapping_rek_db = $wpdb->get_results("
        SELECT *
        FROM data_mapping_rek_d
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
        FROM jalan_irigasi m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi
        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang=k.kd_barang
        WHERE m.milik = 12
        ORDER BY m.kd_lokasi ASC, m.kd_barang ASC, m.dok_tanggal ASC";

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
                FROM pemeliharaan_jalan_irigasi
                WHERE id_jalan_irigasi = %d
            ", $row['id_jalan_irigasi'])
        );
        $harga_pemeliharaan = $sql_harga_pemeliharaan->fetchcolumn();
        $sisa_bagi_pemeliharaan = $harga_pemeliharaan%$row['jumlah'];
        $harga_pemeliharaan_asli = ($harga_pemeliharaan - $sisa_bagi_pemeliharaan) / $row['jumlah'];

        for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
            if ($no_register == $row['jumlah']) {
                $harga = $harga_asli + $sisa_bagi;
                $harga_pemeliharaan = $harga_pemeliharaan_asli + $sisa_bagi_pemeliharaan;
                // if($row['id_jalan_irigasi'] == '33900202300010'){
                //  die('harga tess '.$harga.' = '.$sisa_bagi.' + '. $harga_asli.' | harga pemeliharaan '.$harga_pemeliharaan.' = '.$sisa_bagi_pemeliharaan.' + '.$harga_pemeliharaan_asli.' | total = '.($harga+$harga_pemeliharaan));
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

            // Fetch penyusutan jalan_irigasi
            $sql_penyusutan_jalan_irigasi_2023 = $dbh->query(
                $wpdb->prepare("
                    SELECT 
                        sisa_ue_stl_sst,
                        penyusutan_skr,
                        nilai_buku_skr,
                        penyusutan_per_tahun,
                        nilai_buku_skr
                    FROM penyusutan_jalan_irigasi_2023
                    WHERE id_jalan_irigasi = %d
                ", $row['id_jalan_irigasi'])
            );
            $data_penyusutan = $sql_penyusutan_jalan_irigasi_2023->fetch(PDO::FETCH_ASSOC);

            $kode_rek = $row['kd_barang'] . ' (Belum dimapping)';
            $nama_rek = '';
            if (!empty($mapping_rek[$row['kd_barang']])) {
                $kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
                $nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
            }

            if (!empty($row['kontruksi'])) {
                switch ($row['kontruksi']) {
                    case 1:
                        $kontruksi = 'Aspal';
                        break;
                    case 2:
                        $kontruksi = 'Beton';
                        break;
                    case 3:
                        $kontruksi = 'Lainnya';
                        break;
                    default:
                        $kontruksi = 'Lainnya';
                        break;
                }
            } else {
                $kontruksi = 'Lainnya';
            }

            if (!empty($row['asal'])) {
                switch ($row['asal']) {
                    case 'Dibeli':
                        $asal = 'Pengadaan APBD';
                        break;
                    case 'Lainnya':
                    case 'Lainya': // Handles potential typo
                        $asal = 'Perolehan Lainnya';
                        break;
                    case 'Hibah':
                        $asal = 'Hibah';
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
            if (!empty($row['dok_tanggal'])) {
                $tanggal_pengadaan = $row['dok_tanggal'];
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

            $umur_ekonomis = (2024 + $data_penyusutan['sisa_ue_stl_sst']) - $year;
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
                    FROM pemeliharaan_jalan_irigasi
                    WHERE id_jalan_irigasi = %d
                ", $row['id_jalan_irigasi'])
            );

            // Fetch tahun harga pemeliharaaan
            $sql_tahun_harga_pemeliharaan = $dbh->query(
                $wpdb->prepare("
                    SELECT 
                        tgl_pelihara,
                        biaya_pelihara
                    FROM pemeliharaan_jalan_irigasi
                    WHERE id_jalan_irigasi = %d
                ", $row['id_jalan_irigasi'])
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
                'tanggal_perolehan' => $formattedDate,
                'tanggal_pengadaan' => $formattedDate,
                'hak' => 'Hak Pakai',
                'no_register' => $no_register,
                'asal_usul' => $asal,
                'keterangan' => $row['keterangan'],
                'bahan_kontruksi' => $kontruksi,
                'panjang' => $row['panjang'],
                'lebar' => $row['lebar'],
                'luas' => $row['luas'],
                'alamat' => $row['letak'],
                'satuan' => 'Meter',
                'klasifikasi' => $klasifikasi,
                'umur_ekonomis' => $row['umur_ekonomis'],
                'masa_pakai' => null,
                'penyusutan_ke' => null,
                'penyusutan_per_tanggal' => null,
                'nilai_perolehan' => $harga,
                'nilai_aset' => $nilai_aset,
                'nilai_dasar_perhitungan' => $nilai_aset,
                'nilai_penyusutan_per_tahun' => $data_penyusutan['penyusutan_skr'],
                'beban_penyusutan' => $data_penyusutan['penyusutan_skr'],
                'akumulasi_penyusutan' => $akumulasi_penyusutan,
                'nilai_buku' => $data_penyusutan['nilai_buku_skr'],
                'id_jalan_irigasi' => $row['id_jalan_irigasi'],
                'jumlah_barang' => 1,
                'active' => 1
            );

            // Check if the record exists in the database and insert/update accordingly
            $cek_id = $wpdb->get_var(
                $wpdb->prepare("
                    SELECT id
                    FROM data_laporan_kib_d
                    WHERE kode_aset=%s 
                      AND kode_lokasi=%s 
                      AND no_register=%d
                      AND id_jalan_irigasi=%d
                ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register, $row['id_jalan_irigasi'])
            );

            if (empty($cek_id)) {
                $wpdb->insert('data_laporan_kib_d', $data);
            } else {
                $wpdb->update('data_laporan_kib_d', $data, array('id' => $cek_id));
            }
        }
    }
} else {
    $data_laporan_kib_d = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_d
        WHERE active=1
        ORDER BY nama_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
    ", ARRAY_A);

    $total_data = $wpdb->get_var("
        SELECT COUNT(*)
        FROM data_laporan_kib_d
        WHERE active=1
        ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
    ");

    $body = '';
    foreach ($data_laporan_kib_d as $get_laporan) {
        $no++;
        $body .= '
            <tr data-id="'.$get_laporan['id_jalan_irigasi'].'">
                <td class="text-center">' . $no . '</td>
                <td class="text-left">' . $get_laporan['nama_skpd'] . '</td>
                <td class="text-center">' . $get_laporan['kode_skpd'] . '</td>
                <td class="text-left">' . $get_laporan['nama_lokasi'] . '</td>
                <td class="text-center">' . $get_laporan['kode_lokasi_mapping'] . '</td>
                <td class="text-left">' . $get_laporan['jenis_barang'] . '</td>
                <td class="text-center">' . $get_laporan['kode_barang'] . '</td>
                <td class="text-left">' . $get_laporan['nama_aset'] . '</td>
                <td class="text-center">' . $get_laporan['kode_aset'] . '</td>
                <td class="text-center">' . $get_laporan['tanggal_pengadaan'] . '</td>
                <td class="text-left">' . $get_laporan['tanggal_pengadaan'] . '</td>
                <td class="text-left">' . $get_laporan['hak'] . '</td>
                <td class="text-center">' . $get_laporan['no_register'] . '</td>
                <td class="text-left">' . $get_laporan['asal_usul'] . '</td>
                <td class="text-left">' . $get_laporan['keterangan'] . '</td>
                <td class="text-left">' . $get_laporan['bahan_kontruksi'] . '</td>
                <td class="text-center">' . $get_laporan['panjang'] . '</td>
                <td class="text-center">' . $get_laporan['lebar'] . '</td>
                <td class="text-center">' . $get_laporan['luas'] . '</td>
                <td class="text-left">' . $get_laporan['alamat'] . '</td>
                <td class="text-left">' . $get_laporan['satuan'] . '</td>
                <td class="text-left">' . $get_laporan['klasifikasi'] . '</td>
                <td class="text-left">' . $get_laporan['umur_ekonomis'] . '</td>
                <td class="text-left">' . $get_laporan['masa_pakai'] . '</td>
                <td class="text-left">' . $get_laporan['penyusutan_ke'] . '</td>
                <td class="text-left">' . $get_laporan['penyusutan_per_tanggal'] . '</td>
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
?>
<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 100vh;
        width: 100%;
    }

    #tabel_laporan_kib_d th,
    #tabel_laporan_kib_d td {
        text-align: center;
        vertical-align: middle;
    }

    #tabel_laporan_kib_d thead {
        position: sticky;
        top: -6px;
        background: #ffc491;
    }

    #tabel_laporan_kib_d tfoot {
        position: sticky;
        bottom: -6px;
        background: #ffc491;
    }
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px; margin: 0 0 3rem 0;">
            <h1 class="text-center" style="margin: 3rem;">Halaman Laporan KIB D</h1>
            <div style="margin-bottom: 25px;">
                <button class="btn btn-warning" onclick="export_data();"><span class="dashicons dashicons-database-import"></span> Impor Data</button>
            </div>
            <div class="info-section">
                    <span class="label">Total Data :</span>
                    <span class="value"><?php echo $no ?> / <?php echo $total_data; ?></span>
                </div>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_d" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; border-collapse: collapse; width: 100%; overflow-wrap: break-word;" class="table table-bordered">
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
                            <th class="text-center">TANGGAL PEROLEHAN</th>
                            <th class="text-center">TANGGAL PENGADAAN</th>
                            <th class="text-center">HAK</th>
                            <th class="text-center">NOMOR REGISTER</th>
                            <th class="text-center">ASALUSUL</th>
                            <th class="text-center">KETERANGAN</th>
                            <th class="text-center">BAHAN KONSTRUKSI</th>
                            <th class="text-center">PANJANG</th>
                            <th class="text-center">LEBAR</th>
                            <th class="text-center">LUAS</th>
                            <th class="text-center">ALAMAT</th>
                            <th class="text-center">SATUAN</th>
                            <th class="text-center">KLASIFIKASI ASET</th>
                            <th class="text-center">UMUR EKONOMIS</th>
                            <th class="text-center">MASA PAKAI</th>
                            <th class="text-center">PENYUSUTAN TAHUN KE -</th>
                            <th class="text-center">PENYUSUTAN PER TANGGAL</th>
                            <th class="text-center">NILAI PEROLEHAN</th>
                            <th class="text-center">NILAI ASET + KAPITALISASI</th>
                            <th class="text-center">NILAI DASAR PERHITUNGAN SUSUT</th>
                            <th class="text-center">NILAI PENYUSUTAN PER TAHUN</th>
                            <th class="text-center">BEBAN PENYUSUTAN</th>
                            <th class="text-center">AKUMULASI PENYUSUTAN</th>
                            <th class="text-center">NILAI BUKU</th>
                            <th class="text-center">KUANTITAS/ JUMLAH BARANG</th>
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
                    // location.reload();
                }
            });
        }
    }
</script>