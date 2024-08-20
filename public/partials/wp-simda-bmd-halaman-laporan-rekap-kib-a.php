<?php
global $wpdb;

if (!defined('WPINC')) {
	die;
}

$results = $wpdb->get_results("
    SELECT 
        nama_skpd, 
        SUM(nilai_perolehan) AS intra_countable,
        0 AS ekstra_countable
    FROM 
        data_laporan_kib_a
    GROUP BY 
        nama_skpd
");

$body = '';
$no = 1;

foreach ($results as $row) {
    $body .= '<tr>';
    $body .= '<td>' . $no . '</td>';
    $body .= '<td class="text-left">' . esc_html($row->nama_skpd) . '</td>';
    $body .= '<td class="text-right">' . number_format($row->intra_countable,0,",",".").'</td>';
    $body .= '<td class="text-right">' . number_format($row->ekstra_countable,0,",",".") . '</td>';
    $body .= '</tr>';
    $no++;
}

?>

<div class="container-md">
	<div class="cetak">
		<div style="padding: 10px; margin: 0 0 3rem 0;">
			<h1 class="text-center table-title">Halaman Rekapitulasi KIB D</h1>
			<table>
				<thead>
					<tr>
						<th>No</th>
						<th>Nama SKPD</th>
						<th>Intra Countable</th>
						<th>Ekstra Countable</th>
					</tr>
				</thead>
				<tbody>
					<?php echo $body; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
