<h3 class="text-center">Import EXCEL data mapping</h3>
<div style="padding: 10px;">
	<p>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSimpeg(event);">
	<br>Contoh format file excel bisa <a target="_blank" href="<?php echo SIMDA_BMD_PLUGIN_URL; ?>public/media/data_mapping.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>.</p>
	<p>Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea></p>
	<p><a onclick="import_excel_mapping_rek(); return false" href="javascript:void(0);" class="button button-primary">Import</a></p>
</div>
<div class="cetak" style="padding: 10px;">
	<input type="hidden" value="<?php echo get_option( SIMDA_BMD_APIKEY ); ?>" id="api_key">
	<h1 class="text-center">Halaman Mapping Rekening Aset Tanah</h1>
	<table class="table table-bordered" id="table-tanah">
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
<script type="text/javascript">
jQuery(document).ready(function(){
	//get_mapping_rek();
});

function get_mapping_rek(){
	jQuery("#wrap-loading").show();
	jQuery('#table-tanah').DataTable({
		"processing": true,
		"serverSide": true,
        "ajax": {
        	url: ajax.url,
			type:"post",
			data:{
				'action' : "get_mapping_rek",
				'api_key' : jQuery("#api_key").val(),
				'tipe' : 'tanah',
			}
		},
		"initComplete":function( settings, json){
			jQuery("#wrap-loading").hide();
		},
		"columns": [
            { 
            	"data": "no",
            	className: "text-center"
            },
            { 
            	"data": "kode_rekening_spbmd",
            	className: "text-center"
            },
            { "data": "uraian_rekening_spbmd" },
            { 
            	"data": "kode_rekening_ebmd",
            	className: "text-center"
            },
            { "data": "uraian_rekening_ebmd" },
            { 
            	"data": "aksi",
            	className: "text-center"
            }
        ]
    });
}
</script>