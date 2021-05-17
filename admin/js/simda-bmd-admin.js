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
	if(
        _type == 'A'
        || _type == 'B'
    ){
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
                if(res.status == 'success'){
                    var text_error = [];
                    res.data.map(function(b, i){
                    	if(b.status == 'error'){
                    		text_error.push(b.message);
                    	}
                    });
                    alert(res.message+' | Error:'+text_error.length+' ('+text_error.join('; ')+')');
                }else{
                    alert(res.message);
                }
            }
        });
    }else{
        alert('Rekening table KD_KIB_'+_type+' masih dalam pengembangan!');
    }
}