jQuery(document).ready(function(){
	var loading = ''
		+'<div id="wrap-loading">'
	        +'<div class="lds-hourglass"></div>'
	        +'<div id="persen-loading"></div>'
	    +'</div>';
	if(jQuery('#wrap-loading').length == 0){
		jQuery('body').prepend(loading);
	}

});

function migrasi_data(_type){
	var data = {};
	if(_type == 'A'){
		data.type = _type;
	}
	if(data.type){
		jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'migrasi_data',
                data: data
            },
            success: function(res){
                jQuery('#wrap-loading').hide();
                res = JSON.parse(res);
            }
        });
    }
}