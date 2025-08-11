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
            UPDATE data_laporan_kib_e m 
            set m.active=0 
            where m.active=1 
                ".str_replace('m.kd_lokasi', 'm.kode_lokasi', $where_skpd_list)
        );
    }else if ($page == 1) {
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
    if(!empty($where_skpd_list)){
        $sql = '
            SELECT 
                m.kd_lokasi as kd_lokasi_spbmd, 
                m.*, 
                s.*, 
                k.*
            FROM aset_tetap m
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
            FROM aset_tetap m
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
            FROM data_laporan_kib_e
            WHERE kode_aset = %s 
              AND kode_lokasi = %s
              AND id_aset_tetap = %d
        ", $kode_rek, $row['kd_lokasi_spbmd'], $row['id_aset_tetap']), ARRAY_A);

        $cek_id = array();
        foreach ($cek_ids as $val) {
            $cek_id[$val['no_register']] = $val;
        }

        $insert_multi = array();
        for ($no_register = 1; $no_register <= $row['jumlah']; $no_register++) {
            $harga = 0;
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
                'nilai_perolehan' => $harga,
                'buku_pencipta' => $row['buku_pencipta'],
                'spesifikasi' => $row['buku_spesifikasi'] . $spek,
                'pencipta' => $row['seni_pencipta'],
                'bahan' => $row['seni_bahan'],
                'jenis_hewan' => $row['hewan_tumbuhan_jenis'],
                'ukuran' => $row['hewan_tumbuhan_ukuran'],
                'jumlah' => 1,
                'satuan' => $satuan,
                'nilai_aset' => $harga,
                'klasifikasi' =>  $klasifikasi,
                'umur_ekonomis' => 0,
                'asal_daerah' => null,
                'nilai_buku' => null,
                'id_aset_tetap' => $row['id_aset_tetap'],
                'masa_pakai' => null,
                'active' => 1
            );

            if (empty($cek_id[$no_register])) {
                $insert_multi[] = $data;
            } else {
                $wpdb->update(
                    'data_laporan_kib_e',
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
                    $wpdbx->insert_multiple('data_laporan_kib_e', $new_insert_multi);
                    $new_insert_multi = array();
                }
            }
            if(!empty($new_insert_multi)){
                $wpdbx->insert_multiple('data_laporan_kib_e', $new_insert_multi);
            }
        }
    }
    die();
} else {
    $sql = '
        SELECT
            COUNT(m.id_aset_tetap) AS jml
        FROM aset_tetap m
        LEFT JOIN mst_kl_sub_unit s ON m.kd_lokasi=s.kd_lokasi';
    $result = $dbh->query($sql);
    $jml_all = $result->fetch(PDO::FETCH_NAMED);

    $nomor_urut = $no;
    $next_page = 'hal=' . ($page + 1) . '&per_hal=' . $per_page . '&nomor_urut=' . $nomor_urut;

    $filter_lokasi = '';
    if(!empty($_GET) && !empty($_GET['filter_lokasi'])){
        $filter_lokasi = ' AND kode_lokasi='.$wpdb->prepare('%s', $_GET['filter_lokasi']);
    }
    $leverage = 50;
    $data_laporan_kib_e = $wpdb->get_results(
        $wpdb->prepare("
            SELECT *
            FROM data_laporan_kib_e
            WHERE active=1
                $filter_lokasi
            ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
            LIMIT %d, %d
        ", $start_page*$leverage, $per_page*$leverage),
        ARRAY_A
    );

    // die($wpdb->last_query);

    $total_data = $wpdb->get_var("
        SELECT COUNT(*)
        FROM data_laporan_kib_e
        WHERE active=1
            $filter_lokasi
        ORDER by kode_skpd ASC, kode_lokasi ASC, kode_aset ASC, tanggal_pengadaan ASC
    ");

    $body = '';
    foreach ($data_laporan_kib_e as $get_laporan) {
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
            <td class="text-center">' . $get_laporan['kode_aset'] . '</td>
            <td class="text-left">' . $get_laporan['nama_aset'] . '</td>
            <td class="text-center">' . $tanggal_pengadaan . '</td>
            <td class="text-center">' . $tanggal_pengadaan . '</td>
            <td class="text-center">' . $get_laporan['kondisi'] . '</td>
            <td class="text-center">' . $get_laporan['no_register'] . '</td>     
            <td class="text-left">' . $get_laporan['asal_usul'] . '</td>
            <td class="text-left">' . $get_laporan['alamat'] . '</td>
            <td class="text-left">' . $get_laporan['keterangan'] . '</td>
            <td class="text-left">' . $get_laporan['buku_pencipta'] . '</td>
            <td class="text-left">' . $get_laporan['spesifikasi'] . '</td>
            <td class="text-left">' . $get_laporan['asal_daerah'] . '</td>
            <td class="text-left">' . $get_laporan['pencipta'] . '</td>
            <td class="text-center">' . $get_laporan['bahan'] . '</td>
            <td class="text-center">' . $get_laporan['jenis_hewan'] . '</td>
            <td class="text-center">' . $get_laporan['ukuran'] . '</td>
            <td class="text-center">' . $get_laporan['jumlah'] . '</td>
            <td class="text-center">' . $get_laporan['satuan'] . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_perolehan'], 0, ",", ".") . '</td>
            <td class="text-right">' . number_format($get_laporan['nilai_aset'], 0, ",", ".") . '</td>
            <td class="text-center">' . $get_laporan['umur_ekonomis'] . '</td>
            <td class="text-center">0</td>
            <td class="text-center">0</td>
            <td class="text-center">0</td>
            <td class="text-left">' . $get_laporan['nilai_buku'] . '</td>
            <td class="text-left">' . $get_laporan['klasifikasi'] . '</td>
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
$skpd_list_opsi = '';
if ($skpd_all) {
    foreach ($skpd_all as $skpd) {
        $selected = '';
        if(!empty($_GET) && !empty($_GET['filter_lokasi'])){
            if($_GET['filter_lokasi'] == $skpd['kd_lokasi']){
                $selected = 'selected';
            }
        }
        $skpd_list .= '
        <tr>
            <td class="text-center"><input type="checkbox" value="' . $skpd['kd_lokasi'] . '"></td>
            <td>' . $skpd['kd_lokasi'] . '</td>
            <td>' . $skpd['NAMA_sub_unit'] . '</td>
            <td></td>
            <td></td>
        </tr>';
        $skpd_list_opsi .= '<option value="'.$skpd['kd_lokasi'].'" '.$selected.'>'.$skpd['kd_lokasi'].' '.$skpd['NAMA_sub_unit'].'</option>';
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
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <h1 class="text-center" style="margin:3rem;">Halaman <?php echo $page ?> Laporan KIB E</h1>
        <div id="option_import" class="row g-3 align-items-center justify-content-center" style="margin-bottom: 15px;"></div>
        <div class="info-section" style="margin-bottom: 20px;">
            Jumlah per <?php echo $per_page; ?> baris : <span id="page_count"></span> Halaman
            <br>
            <span class="label">Total Baris KIB E : <?php echo number_format($jml_all['jml'], 0, ",", "."); ?></span>
            <br>
            <br>
            <select class="form-control" id="filter_lokasi" onchange="filter_lokasi();">
                <option value="">Filter OPD</option>
                <?php echo $skpd_list_opsi; ?>
            </select>
            <br>
            <br>
            <span class="label">Jumlah Aset KIB E yang sudah diimport : <?php echo number_format($total_data, 0, ",", "."); ?></span>
            <br>
            <span class="value">Data tampil dari nomor urut : <?php echo ($start_page*$leverage)+1; ?> sampai <?php echo $no ?></span>
        </div>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-warning" onclick="showPopup();"><span class="dashicons dashicons-database-import"></span> Impor Data</button>
        </div>
        <div id="cetak">
            <table id="tabel_laporan_kib_e" cellpadding="2" cellspacing="0" style="font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th class="text-center">NAMA OPD</th>
                        <th class="text-center">KODE OPD</th>
                        <th class="text-center">NAMA LOKASI</th>
                        <th class="text-center">KODE LOKASI</th>
                        <th class="text-center">NAMA LAMA</th>
                        <th class="text-center">KODE LAMA</th>
                        <th class="text-center">KODE ASET 108</th>
                        <th class="text-center">NAMA ASET 108</th>
                        <th class="text-center">TANGGAL PEROLEHAN</th>
                        <th class="text-center">TANGGAL PEMBUKUAN</th>
                        <th class="text-center">KONDISI</th>
                        <th class="text-center">NOMOR REGISTER</th>
                        <th class="text-center">SUMBER PEROLEHAN</th>
                        <th class="text-center">ALAMAT</th>
                        <th class="text-center">KETERANGAN</th>
                        <th class="text-center">BUKU PENCIPTA</th>
                        <th class="text-center">SPESIFIKASI</th>
                        <th class="text-center">ASALDAERAH</th>
                        <th class="text-center">PENCIPTA</th>
                        <th class="text-center">BAHAN</th>
                        <th class="text-center">HEWAN JENIS</th>
                        <th class="text-center">UKURAN</th>
                        <th class="text-center">JUMLAH</th>
                        <th class="text-center">SATUAN</th>
                        <th class="text-center">NILAI PEROLEHAN</th>
                        <th class="text-center">NILAI ASET</th>
                        <th class="text-center">UMUR EKONOMIS</th>
                        <th class="text-center">SISA MANFAAT</th>
                        <th class="text-center">BEBAN PENYUSUTAN</th>
                        <th class="text-center">AKUMULASI PENYUSUTAN</th>
                        <th class="text-center">NILAI BUKU</th>
                        <th class="text-center">KLASIFIKASI ASET</th>
                        <!-- <th class="text-center">MASA PAKAI</th> -->
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
                <h3 class="text-center">Jika OPD / lokasi kosong maka akan dimigrasi semua data sesuai jumlah halaman yang disetting</h3>
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
        jQuery('#filter_lokasi').select2();
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
        option_import += '    <input type="number" id="start_page" name="start_page" class="form-control text-right" min="1" max="' + all_page + '" value="1">';
        option_import += '  </div>';
        option_import += '  <div class="col-auto">';
        option_import += '    <label for="end_page" class="col-form-label">Selesai di Halaman:</label>';
        option_import += '  </div>';
        option_import += '  <div class="col-auto">';
        option_import += '    <input type="number" id="end_page" name="end_page" class="form-control text-right" min="1" max="' + all_page + '" value="'+all_page+'">';
        option_import += '  </div>';


        jQuery('#action-bmd').append(extend_action);
        jQuery('#option_import').html(option_import);
        jQuery('#page_count').text(all_page);

    });

    function filter_lokasi(){
        var filter = jQuery('#filter_lokasi').val();
        if(filter != ''){
            window.location = window.location.href.split('?')[0] + '?filter_lokasi='+filter;
        }
    }

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