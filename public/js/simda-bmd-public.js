jQuery(document).ready(function(){
	var loading = ''
		+'<div id="wrap-loading">'
	        +'<div class="lds-hourglass"></div>'
	        +'<div id="persen-loading"></div>'
	    +'</div>';
	if(jQuery('#wrap-loading').length == 0){
		jQuery('body').prepend(loading);
	}

	jQuery(document).on('hidden.bs.modal', function () {
	  	if(jQuery('.modal.show').length){
	    	jQuery('body').addClass('modal-open');
	  	}
	});
});

function to_number_bmd(text){
	if(typeof text == 'number'){
		return text;
	}
	text = +(text.replace(/\./g, '').replace(/,/g, '.'));
	if(typeof text == 'NaN'){
		text = 0;
	}
	return text;
}

function run_download_excel_bmd(type){
	var current_url = window.location.href;
	var body = '<a id="excel" onclick="return false;" href="#" class="btn btn-primary">DOWNLOAD EXCEL</a>';
	var download_excel = ''
		+'<div id="action-sipd" class="hide-print">'
			+body
		+'</div>';
	jQuery('body').prepend(download_excel);

	var style = '';

	style = jQuery('.cetak').attr('style');
	if (typeof style == 'undefined'){ style = ''; };
	jQuery('.cetak').attr('style', style+" font-family:'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; padding:0; margin:0; font-size:13px;");
	
	jQuery('.bawah').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" border-bottom:1px solid #000;");
	});
	
	jQuery('.kiri').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" border-left:1px solid #000;");
	});

	jQuery('.kanan').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" border-right:1px solid #000;");
	});

	jQuery('.atas').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" border-top:1px solid #000;");
	});

	jQuery('.text_tengah').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" text-align: center;");
	});

	jQuery('.text_kiri').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" text-align: left;");
	});

	jQuery('.text_kanan').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" text-align: right;");
	});

	jQuery('.text_block').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" font-weight: bold;");
	});

	jQuery('.text_15').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" font-size: 15px;");
	});

	jQuery('.text_20').map(function(i, b){
		style = jQuery(b).attr('style');
		if (typeof style == 'undefined'){ style = ''; };
		jQuery(b).attr('style', style+" font-size: 20px;");
	});

	var td = document.getElementsByTagName("td");
	for(var i=0, l=td.length; i<l; i++){
		style = td[i].getAttribute('style');
		if (typeof style == 'undefined'){ style = ''; };
		td[i].setAttribute('style', style+'; mso-number-format:\\@;');
	};

	jQuery('#excel').on('click', function(){
		var name = "Laporan";
		var title = jQuery('#cetak').attr('title');
		if(title){
			name = title;
		}
		
		jQuery("a").removeAttr("href");
		
		var cek_hide_excel = jQuery('#cetak .hide-excel');
		if(cek_hide_excel.length >= 1){
			cek_hide_excel.remove();
			setTimeout(function(){
				alert('Ada beberapa fungsi yang tidak bekerja setelah melakukan donwload excel. Refresh halaman ini!');
				location.reload();
			}, 5000);
		}

		tableHtmlToExcel('cetak', name);
	});
}