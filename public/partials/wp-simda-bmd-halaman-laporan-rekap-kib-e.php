<?php
global $wpdb;

if (!defined('WPINC')) {
	die;
}

$results = $wpdb->get_results("
    SELECT 
        nama_skpd, 
        kode_skpd,
        nama_lokasi, 
        kode_lokasi,
        SUM(CASE WHEN klasifikasi = 'Intracountable' THEN nilai_perolehan ELSE 0 END) AS intra_countable,
        SUM(CASE WHEN klasifikasi = 'Ekstracountable' THEN nilai_perolehan ELSE 0 END) AS ekstra_countable
    FROM data_laporan_kib_e
    WHERE active=1
    GROUP BY kode_lokasi
   	ORDER by kode_skpd ASC, kode_lokasi ASC
");
// print_r($results); die($wpdb->last_query);
$total_all = array(
	'intra_countable' => 0,
	'ekstra_countable' => 0
);
$data_all = array();
foreach ($results as $row) {
	if(empty($data_all[$row->kode_skpd])){
		$data_all[$row->kode_skpd] = array(
			'data' => array(),
			'intra_countable' => 0,
			'ekstra_countable' => 0,
			'kode' => $row->kode_skpd,
			'nama' => $row->nama_skpd
		);
	}
	$data_all[$row->kode_skpd]['data'][$row->kode_lokasi] = $row;
	$data_all[$row->kode_skpd]['intra_countable'] += $row->intra_countable;
	$total_all['intra_countable'] += $row->intra_countable;
	$total_all['ekstra_countable'] += $row->ekstra_countable;
}

$body = '';
$no = 1;
foreach ($data_all as $row) {
    $body .= '<tr>';
    $body .= '<td class="text-left">' . $no . '</td>';
    $body .= '<td class="text-left">' . esc_html($row['kode']) . '</td>';
    $body .= '<td class="text-left" colspan="3">' . esc_html($row['nama']) . '</td>';
    $body .= '<td class="text-right">' . number_format($row['intra_countable'],0,",",".").'</td>';
    $body .= '<td class="text-right">' . number_format($row['ekstra_countable'],0,",",".") . '</td>';
    $body .= '</tr>';
	$no2 = 1;
	foreach ($row['data'] as $row2) {
	    $body .= '<tr>';
	    $body .= '<td class="text-left">' . $no.'.'.$no2 . '</td>';
	    $body .= '<td class="text-left">' . esc_html($row['kode']) . '</td>';
	    $body .= '<td class="text-left">' . esc_html($row['nama']) . '</td>';
	    $body .= '<td class="text-left">' . esc_html($row2->kode_lokasi) . '</td>';
	    $body .= '<td class="text-left">' . esc_html($row2->nama_lokasi) . '</td>';
	    $body .= '<td class="text-right">' . number_format($row2->intra_countable,0,",",".").'</td>';
	    $body .= '<td class="text-right">' . number_format($row2->ekstra_countable,0,",",".") . '</td>';
	    $body .= '</tr>';
	    $no2++;
	}
    $no++;
}

?>

<div class="container-md">
	<div id="cetak" style="padding: 5px; overflow: auto; max-height: 80vh;">
		<div style="padding: 10px; margin: 0 0 3rem 0;">
			<h1 class="text-center table-title">Halaman Rekapitulasi KIB E ( Aset Tetap )</h1>
			<table>
				<thead>
					<tr>
						<th>No</th>
						<th>Kode SKPD</th>
						<th>Nama SKPD</th>
						<th>Kode Lokasi</th>
						<th>Nama Lokasi</th>
						<th>Intra Countable</th>
						<th>Ekstra Countable</th>
					</tr>
					<tr>
						<th colspan="5">Total</th>
						<th class="text-right"><?php echo number_format($total_all['intra_countable'],0,",","."); ?></th>
						<th class="text-right"><?php echo number_format($total_all['ekstra_countable'],0,",","."); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php echo $body; ?>
				</tbody>
					<tr>
						<th colspan="5">Total</th>
						<th class="text-right"><?php echo number_format($total_all['intra_countable'],0,",","."); ?></th>
						<th class="text-right"><?php echo number_format($total_all['ekstra_countable'],0,",","."); ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/jszip.js"></script>
<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/xlsx.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        run_download_excel_bmd();
    });
</script>
