<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$dbh = $this->connect_spbmd();

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
    if ($page == 1) {
        $wpdb->update(
            'data_laporan_kib_e',
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
        FROM data_mapping_rek_e
        WHERE active=1
    ", ARRAY_A);
    $mapping_rek = array();
    foreach ($mapping_rek_db as $key => $value) {
        $mapping_rek[$value['kode_rekening_spbmd']] = $value;
    }

    $sql = $wpdb->prepare('
        SELECT 
            m.kd_lokasi as kd_lokasi_spbmd, 
            m.*, 
            s.*, 
            k.*
        FROM aset_tetap m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi = s.kd_lokasi
        LEFT JOIN mst_kb_ss_kelompok k ON m.kd_barang = k.kd_barang
        LIMIT %d, %d', $start_page, $per_page);

    $result = $dbh->query($sql);

    $no = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $row['harga'] = $row['harga'] / $row['jumlah'];
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

            switch (true) {
                case $row['harga'] == 0:
                case $row['harga'] >= $row['nil_min_kapital']:
                    $klasifikasi = 'Intracountable';
                    break;
                case $row['harga'] < $row['nil_min_kapital']:
                    $klasifikasi = 'Ekstracountable';
                    break;
                default:
                    $klasifikasi = 'Intracountable';
                    break;
            }

            //jika jenis selain hewan maka satuan buah
            $satuan = 'Buah';
            if (substr($row['kd_barang'], 0, 6) === '051901') {
                $satuan = 'Ekor';
            }

            //jika jenis buku maka tampilkan judul
            $spek = '';
            if (
                substr($row['kd_barang'], 0, 6) === '051701'
                && !empty($row['buku_judul'])
            ) {
                $spek = "\n Judul Buku = " . $row['buku_judul'];
            }

            $tanggal_pengadaan = date('d-m-Y', strtotime($row['tgl_beli']));
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
                'no_register' => $no_register,
                'asal_usul' => $asal,
                'keterangan' => $row['keterangan'],
                'kondisi' => 'Baik',
                'nilai_perolehan' => $row['harga'],
                'buku_pencipta' => $row['buku_pencipta'],
                'spesifikasi' => $row['buku_spesifikasi'] . $spek,
                'pencipta' => $row['seni_pencipta'],
                'bahan' => $row['seni_bahan'],
                'jenis_hewan' => $row['hewan_tumbuhan_jenis'],
                'ukuran' => $row['hewan_tumbuhan_ukuran'],
                'jumlah' => 1,
                'satuan' => $satuan,
                'nilai_aset' => $row['harga'],
                'klasifikasi' =>  $klasifikasi,
                'umur_ekonomis' => 0,
                'asal_daerah' => null,
                'nilai_buku' => null,
                'masa_pakai' => null,
                'active' => 1
            );

            $cek_id = $wpdb->get_var(
                $wpdb->prepare("
                    SELECT id
                    FROM data_laporan_kib_e
                    WHERE kode_aset = %s 
                      AND kode_lokasi = %s 
                      AND no_register = %d
                      AND id_aset_tetap = %d
                ", $kode_rek, $row['kd_lokasi_spbmd'], $no_register, $row['id_aset_tetap'])
            );

            if (empty($cek_id)) {
                $wpdb->insert(
                    'data_laporan_kib_e',
                    $data
                );
            } else {
                $wpdb->update(
                    'data_laporan_kib_e',
                    $data,
                    ['id' => $cek_id]
                );
            }
        }
    }
} else {
    $sql = '
        SELECT
            COUNT(m.id_mesin) AS jml
        FROM mesin m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
    $result = $dbh->query($sql);
    $jml_all = $result->fetch(PDO::FETCH_NAMED);

    $total_data = $wpdb->get_var("
        SELECT COUNT(*)
        FROM data_laporan_kib_e
        WHERE active=1
        ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
    ");

    $nomor_urut = $no;
    $next_page = 'hal=' . ($page + 1) . '&per_hal=' . $per_page . '&nomor_urut=' . $nomor_urut;

    $data_laporan_kib_e = $wpdb->get_results("
        SELECT *
        FROM data_laporan_kib_e
        WHERE active=1
        ORDER by kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
        LIMIT $start_page, $per_page
    ", ARRAY_A);

    $body = '';
    foreach ($data_laporan_kib_e as $get_laporan) {
        $no++;
        $tanggal_pengadaan = date('d-m-Y', strtotime($get_laporan['tanggal_pengadaan']));
        $body .= '
            <tr>
                <td class="text-center">' . $no . '</td>
                <td class="text-left">' . $get_laporan['nama_skpd'] . '</td>
                <td class="text-center">' . $get_laporan['kode_skpd'] . '</td>
                <td class="text-left">' . $get_laporan['nama_unit'] . '</td>
                <td class="text-left">' . $get_laporan['nama_lokasi'] . '</td>
                <td class="text-center">' . $get_laporan['kode_lokasi_mapping'] . '</td>
                <td class="text-left">' . $get_laporan['jenis_barang'] . '</td>
                <td class="text-center">' . $get_laporan['kode_barang'] . '</td>
                <td class="text-center">' . $get_laporan['kode_aset'] . '</td>
                <td class="text-left">' . $get_laporan['nama_aset'] . '</td>
                <td class="text-center">' . $tanggal_pengadaan . '</td>
                <td class="text-center">' . $tanggal_pengadaan . '</td>
                <td class="text-left">' . $get_laporan['asal_usul'] . '</td>
                <td class="text-left">' . $get_laporan['keterangan'] . '</td>
                <td class="text-center">' . $get_laporan['umur_ekonomis'] . '</td>
                <td class="text-center">' . $get_laporan['kondisi'] . '</td>
                <td class="text-center">' . $get_laporan['no_register'] . '</td>     
                <td class="text-right">' . number_format($get_laporan['nilai_perolehan'], 0, ",", ".") . '</td>
                <td class="text-left">' . $get_laporan['buku_pencipta'] . '</td>
                <td class="text-left">' . $get_laporan['spesifikasi'] . '</td>
                <td class="text-left">' . $get_laporan['asal_daerah'] . '</td>
                <td class="text-left">' . $get_laporan['pencipta'] . '</td>
                <td class="text-center">' . $get_laporan['bahan'] . '</td>
                <td class="text-center">' . $get_laporan['jenis_hewan'] . '</td>
                <td class="text-center">' . $get_laporan['ukuran'] . '</td>
                <td class="text-center">' . $get_laporan['jumlah'] . '</td>
                <td class="text-center">' . $get_laporan['satuan'] . '</td>
                <td class="text-right">' . number_format($get_laporan['nilai_aset'], 0, ",", ".") . '</td>
                <td class="text-left">' . $get_laporan['klasifikasi'] . '</td>
                <td class="text-left">' . $get_laporan['nilai_buku'] . '</td>
                <td class="text-left">' . $get_laporan['masa_pakai'] . '</td>
        </tr>';
    }
}
?>
<style type="text/css">
</style>
<div class="container-md">
    <div id="cetak">
        <div style="padding: 10px;margin:0 0 3rem 0;">
            <h1 class="text-center" style="margin:3rem;">Halaman Laporan KIB E</h1>
            <div class="wrap-table">
                <div style="margin-bottom: 25px;">
                    <button class="btn btn-warning" onclick="export_data(false, 1);"><span class="dashicons dashicons-database-import"></span> Impor Data</button>
                </div>
                <div class="info-section">
                    <span class="label">Total Data :</span>
                    <span class="value"><?php echo $no ?> / <?php echo $total_data; ?></span>
                </div>
                <table id="tabel_laporan_kib_e" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">NAMA OPD</th>
                            <th class="text-center">KODE OPD</th>
                            <th class="text-center">NAMA LOKASI</th>
                            <th class="text-center">NAMA LOKASI</th>
                            <th class="text-center">KODE LOKASI</th>
                            <th class="text-center">NAMA LAMA</th>
                            <th class="text-center">KODE LAMA</th>
                            <th class="text-center">KODE ASET 108</th>
                            <th class="text-center">NAMA ASET</th>
                            <th class="text-center">TANGGAL PEROLEHAN</th>
                            <th class="text-center">TANGGAL PENGADAAN</th>
                            <th class="text-center">ASAL USUL</th>
                            <th class="text-center">KETERANGAN</th>
                            <th class="text-center">UMUR EKONOMIS</th>
                            <th class="text-center">KONDISI</th>
                            <th class="text-center">NOMOR REGISTER</th>
                            <th class="text-center">NILAI PEROLEHAN</th>
                            <th class="text-center">BUKU PENCIPTA</th>
                            <th class="text-center">SPESIFIKASI</th>
                            <th class="text-center">ASALDAERAH</th>
                            <th class="text-center">PENCIPTA</th>
                            <th class="text-center">BAHAN</th>
                            <th class="text-center">HEWAN JENIS</th>
                            <th class="text-center">UKURAN</th>
                            <th class="text-center">JUMLAH</th>
                            <th class="text-center">SATUAN</th>
                            <th class="text-center">NILAI ASET</th>
                            <th class="text-center">KLASIFIKASI ASET</th>
                            <th class="text-center">NILAI BUKU</th>
                            <th class="text-center">MASA PAKAI</th>
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
        var url = window.location.href.split('?')[0] + '?<?php echo $next_page; ?>';
        let extend_action = '';
        extend_action += '<a class="btn btn-primary m-2" href="' + url + '" target="_blank"><span class="dashicons dashicons-controls-forward"></span> Halaman Selanjutnya</a>'
        jQuery('#action-bmd').append(extend_action);

        jQuery('#tabel_laporan_kib_e').DataTable({
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

        window.jml_data = <?php echo $jml_all['jml']; ?>;
        window.per_hal = 200;
        window.all_page = Math.ceil(jml_data / per_hal);
    });

    function export_data(no_confirm = false, page = 1) {
        if (no_confirm || confirm('Apakah anda yakin untuk mengimpor data ke database?')) {
            jQuery('#wrap-loading').show();
            jQuery('#persen-loading').html('Export data halaman ' + page + ', dari total ' + all_page + ' halaman.<h3>' + Math.round((page / all_page) * 100) + '%</h3>');
            jQuery.ajax({
                url: '?simpan_db=1&hal=' + page + '&per_hal=' + per_hal,
                success: function(response) {
                    if (page < all_page) {
                        export_data(true, page + 1);
                    } else {
                        jQuery('#wrap-loading').hide();
                        alert('Data berhasil diimpor!.');
                    }
                }
            });
        }
    }
</script>