<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();
$simpan_db = false;
if (!empty($_GET) && !empty($_GET['simpan_db'])) {
    $simpan_db = true;
    $wpdb->update('data_laporan_kib_d', array('active' => 0), array('active' => 1));
}

$no = 0;
if ($simpan_db) {
    $mapping_opd = $this->get_mapping_skpd();

    $mapping_rek_db = $wpdb->get_results("
        SELECT *
        FROM data_mapping_rek_d
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
        	s.*, 
        	k.*
        FROM jalan_irigasi m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi = s.kd_lokasi
        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang = k.kd_barang
        ORDER by m.kd_lokasi ASC, m.kd_barang ASC, m.dok_tanggal ASC';

    $result = $dbh->query($sql);

    $no = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {

            if ($no_register == $row['jumlah']) {
                $row['harga'] = ceil($row['harga']);
            } else {
                $row['harga'] = floor($row['harga']);
            }
            $kode_rek = $row['kd_barang'] . ' (Belum dimapping)';
            $nama_rek = '';
            if (!empty($mapping_rek[$row['kd_barang']])) {
                $kode_rek = $mapping_rek[$row['kd_barang']]['kode_rekening_ebmd'];
                $nama_rek = $mapping_rek[$row['kd_barang']]['uraian_rekening_ebmd'];
            }

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
            $harga_pemeliharaan = $sql_harga_pemeliharaan->fetch(PDO::FETCH_ASSOC);

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
            $nama_unit = '';
            if (!empty($row['kd_satker'])) {
                $sql_master_sub_unit = $dbh->query(
                    $wpdb->prepare("
                        SELECT 
                            NAMA_sub_unit
                        FROM mst_kl_sub_unit
                        WHERE kd_satker = %d
                    ", $row['kd_satker'])
                );
                $nama_unit = $sql_master_sub_unit->fetchColumn();
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

            switch (true) {
                case $row['harga'] == 0:
                case $row['harga'] >= $row['nil_min_kapital']:
                    $klasifikasi = 'Intra Countable';
                    break;
                case $row['harga'] < $row['nil_min_kapital']:
                    $klasifikasi = 'Ekstra Countable';
                    break;
                default:
                    $klasifikasi = 'Intra Countable';
                    break;
            }

            $penyusutan_per_tahun = 0;
            $beban_penyusutan = 0;
            $akumulasi_penyusutan = 0;
            $nilai_buku = 0;
            $penyusutan = $wpdb->get_row(
                $wpdb->prepare("
                    SELECT
                        penyusutan_per_tahun,
                        nilai_buku_skr
                    FROM penyusutan_jalan_irigasi_2023
                    WHERE id_jalan_irigasi = %d
	            ", $row['id_jalan_irigasi']),
                ARRAY_A
            );

            if (!empty($penyusutan)) {
                $nilai_buku = $penyusutan['nilai_buku_skr'];
                $akumulasi_penyusutan = $row['harga'] - $penyusutan['nilai_buku_skr'];
                $beban_penyusutan = $penyusutan['penyusutan_skr'];
                $penyusutan_per_tahun = $penyusutan['penyusutan_skr'];
            }

            if (!empty($row['dok_tanggal'])) {
                $tanggal_pengadaan = $row['dok_tanggal'];
                $timestamp = strtotime($tanggal_pengadaan);
                if ($timestamp !== false) {
                    $formattedDate = date('d-m-Y', $timestamp);
                } else {
                    $formattedDate = "Format tanggal tidak valid!";
                }
            } else {
                $formattedDate = "Tanggal tidak valid atau kosong";
            }
            $umur_ekonomis = 2024 + $sisa_ue_stl_sst - $tahun_dokumen;
            $data = array(
                'nama_skpd' => $nama_induk,
                'kode_skpd' => $kode_induk,
                'nama_unit' => $nama_unit,
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
                'asal_usul' => $row['asal'],
                'keterangan' => $row['keterangan'],
                'bahan_kontruksi' => $row['kontruksi'],
                'panjang' => $row['panjang'],
                'lebar' => $row['lebar'],
                'luas' => $row['luas'],
                'alamat' => $row['letak'],
                'satuan' => 'Meter',
                'klasifikasi' => $klasifikasi,
                'umur_ekonomis' => $umur_ekonomis,
                'masa_pakai' => '',
                'penyusutan_ke' => '',
                'penyusutan_per_tanggal' => '',
                'nilai_perolehan' => $row['harga'],
                'nilai_aset' => $row['harga'],
                'nilai_dasar_perhitungan' => $row['harga'],
                'nilai_penyusutan_per_tahun' => $penyusutan_per_tahun,
                'beban_penyusutan' => $beban_penyusutan,
                'akumulasi_penyusutan' => $akumulasi_penyusutan,
                'nilai_buku' => $nilai_buku,
                'jumlah_barang' => 1,
                'active' => 1
            );
            $cek_id = $wpdb->get_var($wpdb->prepare("
                SELECT id
                FROM data_laporan_kib_d
                WHERE kode_aset=%s AND kode_lokasi=%s AND no_register=%d
            ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register));
            if (empty($cek_id)) {
                $wpdb->insert('data_laporan_kib_d', $data);
            } else {
                $wpdb->update('data_laporan_kib_d', $data, array('id' => $cek_id));
            }
        }
    }
    die();
} else {
    $data_laporan_kib_d = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_d
        WHERE active=1
        ORDER by nama_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC 
        LIMIT 500
    ", ARRAY_A);

    $body = '';
    foreach ($data_laporan_kib_d as $get_laporan) {
        $no++;
        $body .= '
        <tr>
            <td>' . $no . '</td>
            <td>' . $get_laporan['kode_skpd'] . '</td>
            <td>' . $get_laporan['nama_skpd'] . '</td>
            <td>' . $get_laporan['nama_unit'] . '</td>
            <td>' . $get_laporan['kode_lokasi'] . '</td>
            <td>' . $get_laporan['kode_lokasi_mapping'] . '</td>
            <td>' . $get_laporan['kode_barang'] . '</td>
            <td>' . $get_laporan['jenis_barang'] . '</td>
            <td>' . $get_laporan['kode_aset'] . '</td>
            <td>' . $get_laporan['nama_aset'] . '</td>
            <td>' . $get_laporan['tanggal_pengadaan'] . '</td>
            <td>' . $get_laporan['hak'] . '</td>
            <td>' . $get_laporan['no_register'] . '</td>
            <td>' . $get_laporan['asal_usul'] . '</td>
            <td>' . $get_laporan['keterangan'] . '</td>
            <td>' . $get_laporan['bahan_kontruksi'] . '</td>
            <td>' . $get_laporan['panjang'] . '</td>
            <td>' . $get_laporan['lebar'] . '</td>
            <td>' . $get_laporan['luas'] . '</td>
            <td>' . $get_laporan['alamat'] . '</td>
            <td>' . $get_laporan['satuan'] . '</td>
            <td>' . $get_laporan['klasifikasi'] . '</td>
            <td>' . $get_laporan['umur_ekonomis'] . '</td>
            <td>' . $get_laporan['masa_pakai'] . '</td>
            <td>' . $get_laporan['penyusutan_ke'] . '</td>
            <td>' . $get_laporan['penyusutan_per_tanggal'] . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_perolehan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_aset'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_dasar_perhitungan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_penyusutan_per_tahun'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['beban_penyusutan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['akumulasi_penyusutan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_buku'], 0, ",", ".") . '</td>
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
                <button class="btn btn-warning" onclick="export_data();">Export Data</button>
            </div>
            <div class="wrap-table">
                <table id="tabel_laporan_kib_d" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; border-collapse: collapse; width: 100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>KODE OPD</th>
                            <th>NAMA OPD</th>
                            <th>NAMA UNIT</th>
                            <th>KODE LOKASI</th>
                            <th>KODE LAMA</th>
                            <th>NAMA LAMA</th>
                            <th>KODE ASET 108</th>
                            <th>NAMA ASET</th>
                            <th>TANGGAL PEROLEHAN</th>
                            <th>TANGGAL PENGADAAN</th>
                            <th>HAK</th>
                            <th>NOMOR REGISTER</th>
                            <th>ASALUSUL</th>
                            <th>KETERANGAN</th>
                            <th>BAHAN KONSTRUKSI</th>
                            <th>PANJANG</th>
                            <th>LEBAR</th>
                            <th>LUAS</th>
                            <th>ALAMAT</th>
                            <th>SATUAN</th>
                            <th>KLASIFIKASI ASET</th>
                            <th>UMUR EKONOM IS</th>
                            <th>MASA PAKAI</th>
                            <th>PENYUSUTAN TAHUN KE -</th>
                            <th>PENYUSUTAN PER TANGGAL</th>
                            <th>NILAI PEROLEHAN + KAPITALISASI</th>
                            <th>NILAI ASET</th>
                            <th>NILAI DASAR PERHITUNGAN SUSUT</th>
                            <th>NILAI PENYUSUTAN PER TAHUN</th>
                            <th>BEBAN PENYUSUTAN</th>
                            <th>AKUMULASI PENYUSUTAN</th>
                            <th>NILAI BUKU</th>
                            <th>KUANTITAS/ JUMLAH BARANG</th>
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
        if (confirm('Apakah anda yakin untuk mengirim data ini ke database?')) {
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