<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$tipe_rekening = "mapping_rekening_jalan";
?>

<h3 class="text-center">Import EXCEL data mapping</h3>
<div style="padding: 10px;">
	<p>Pilih file excel .xlsx : <input type="file" id="import_data" onchange="filePickedbmd(event);">
	<br>Contoh format file excel bisa <a target="_blank" href="<?php echo SIMDA_BMD_PLUGIN_URL; ?>public/media/data_mapping.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>.</p>
	<p>Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea></p>
	<p><a onclick="import_excel_mapping_rek(); return false" href="javascript:void(0);" class="button button-primary">Import</a></p>
</div>
<div class="cetak" style="padding: 10px;">
	<input type="hidden" value="<?php echo get_option( SIMDA_BMD_APIKEY ); ?>" id="api_key">
	<h1 class="text-center">Halaman Mapping Rekening Aset Jalan</h1>
	<table class="table table-bordered" id="table_mapping_rekening">
		<thead>
			<tr>
				<th>No</th>
				<th>Kode Rekening SPBMD</th>
				<th>Uraian Rekening SPBMD</th>
				<th>Kode Rekening E-BMD</th>
				<th>Uraian Rekening E-BMD</th>
				<th>Aksi</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/jszip.js"></script>
<script type="text/javascript" src="<?php echo SIMDA_BMD_PLUGIN_URL; ?>admin/js/xlsx.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    get_mapping_rek();
});

function get_mapping_rek() {
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        url: ajax.url,
        type: 'post',
        data: {
            action: 'get_mapping_rek',
            api_key: jQuery("#api_key").val(),
            tipe_rekening: '<?php echo $tipe_rekening; ?>',
        },
        dataType: 'json',
        success: function(response) {
            jQuery('#wrap-loading').hide();
            console.log(response);
            if (response.status === 'success') {
                jQuery('#table_mapping_rekening tbody').html(response.data);
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            jQuery('#wrap-loading').hide();
            console.error(xhr.responseText);
            alert('Terjadi kesalahan saat memuat data!');
        }
    });
}

function import_excel_mapping_rek(){
    var import_data = jQuery('#data-excel').val();
    if(typeof import_data == 'undefined'){
        return alert('Upload file dulu!');
    }

    jQuery('#wrap-loading').show();
    let tempData = new FormData();
    tempData.append('action', 'import_excel_mapping_rek');
    tempData.append('api_key', '<?php echo get_option(SIMDA_BMD_APIKEY); ?>');
    tempData.append('import_data', import_data);
    tempData.append('tipe_rekening', '<?php echo $tipe_rekening; ?>');
    jQuery.ajax({
        method: 'post',
        url: ajax.url,
        dataType: 'json',
        data: tempData,
        processData: false,
        contentType: false,
        cache: false,
        success: function(res){
            jQuery('#wrap-loading').hide();
            alert(res.message);
            if(res.status == 'success'){
                get_mapping_rek();
            }
        }
    });
}
</script>