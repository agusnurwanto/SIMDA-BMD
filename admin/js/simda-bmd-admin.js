jQuery(document).ready(function(){
	var loading = ''
		+'<div id="wrap-loading">'
	        +'<div class="lds-hourglass"></div>'
	        +'<div id="persen-loading"></div>'
	    +'</div>';
	if(jQuery('#wrap-loading').length == 0){
		jQuery('body').prepend(loading);
	}
    jQuery('body').on('click', '#check-all', function(){
        var val = jQuery(this).is(':checked');
        jQuery(this).closest('table').find('>tbody>tr>td>input[type="checkbox"]').prop('checked', val);
    });
});

function migrasi_data(_type){
    if(confirm('Apakah anda yakin untuk migrasi database SIMDA BMD type "'+_type+'"?')){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'get_skpd_mapping'
            },
            success: function(res){
                jQuery('#wrap-loading').hide();
                res = JSON.parse(res);
                if(res.status == 'success'){
                    var body = '<h3>SKPD belum ada yang dimapping!</h3>';
                    if(res.data.length >= 1){
                        body = ''
                            +'<h3>Pilih SKPD Hasil Mapping</h3>'
                            +'<table class="wp-list-table widefat striped table-view-list" id="singkron_skpd_mapping">'
                                +'<thead>'
                                    +'<tr>'
                                        +'<th><input type="checkbox" style="margin:0;" id="check-all"></th>'
                                        +'<th>Kode SIMDA BMD</th>'
                                        +'<th>Kode Lokasi SPBMD</th>'
                                        +'<th>Nama Sub Unit</th>'
                                    +'</tr>'
                                +'</thead>'
                                +'</tbody>'
                        res.data.map(function(b, i){
                            body += ''
                                +'<tr>'
                                    +'<td>'
                                        +'<input type="checkbox" value="'+b.val_mapping+'" class="val_mapping">'
                                    +'</td>'
                                    +'<td>'+b.val_mapping+'</td>'
                                    +'<td class="kd_spbmd">'+b.kd_lokasi+'</td>'
                                    +'<td class="nama_sub_unit">'+b.NAMA_sub_unit+'</td>'
                                +'</tr>';
                        });
                        body += ''
                                +'</tbody>'
                            +'</table>'
                            +'<br><button class="button button-primary" onclick="migrasi_data_skpd(\''+_type+'\')">Proses Migrasi</button><br><br>';
                    }
                    jQuery('#my-content-id').html(body);
                    jQuery('#open-popup').click();
                }else{
                    alert(res.message);
                }
            }
        });
    }
}

function migrasi_data_skpd(_type){
    var skpd = {};
    var skpd_nama = [];
    var skpd_mapping = {};
    jQuery('#singkron_skpd_mapping tbody .val_mapping').map(function(){
        var tr = jQuery(this).closest('tr');
        var cek_skpd_simda = jQuery(this).is(':checked');
        if(cek_skpd_simda){
            var val_mapping = tr.find('.val_mapping').val();
            skpd[val_mapping] = {
                simda: val_mapping,
                spbmd: tr.find('.kd_spbmd').text().trim(),
                nama: tr.find('.nama_sub_unit').text().trim()
            }
            skpd_nama.push(val_mapping+' '+skpd[val_mapping].spbmd+' '+skpd[val_mapping].nama);
            skpd_mapping[skpd[val_mapping].spbmd] = val_mapping;
        }
    });
    if(skpd_nama.length == 0){
        alert('Pilih sub unit dulu!');
    }else{
        if(confirm('Apakah anda yakin untuk migrasi database SIMDA BMD type "'+_type+'" Sub Unit ('+skpd_nama.join(', ')+')?')){
            jQuery('#wrap-loading').show();
            var data = {};
            if(
                _type == 'A'
                || _type == 'B'
                || _type == 'C'
            ){
                data.type = _type;
            }
            if(data.type){
                data.skpd = skpd_mapping;
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
                            var text_success_insert = [];
                            var text_success_update = [];
                            var total_row = 0;
                            res.data.map(function(b, i){
                                if(b.status == 'error'){
                                    text_error.push(b.message);
                                }else{
                                    if(b.sql_insert_simda){
                                        text_success_insert.push(b.kd_lok_simda+' '+b.kd_lokasi_spbmd+' '+b.jenis_barang);
                                    }else{
                                        text_success_update.push(b.kd_lok_simda+' '+b.kd_lokasi_spbmd+' '+b.jenis_barang);
                                    }
                                    total_row += +b.jumlah;
                                }
                            });
                            alert(res.message+' | Double: '+res.double.length+' ('+res.double.join('; ')+') | Total row: ('+total_row+') | Berhasil Insert: '+text_success_insert.length+' ('+text_success_insert.join('; ')+') '+' | Berhasil Update: '+text_success_update.length+' ('+text_success_update.join('; ')+') '+' | Error:'+text_error.length+' ('+text_error.join('; ')+')');
                            console.log('res.double, text_success_insert, text_success_update, text_error', res.double, text_success_insert, text_success_update, text_error);
                        }else{
                            alert(res.message);
                        }
                    }
                });
            }else{
                alert('Rekening table KD_KIB_'+_type+' masih dalam pengembangan!');
            }
        }
    }
}

function sql_migrate_ebmd() {
    jQuery("#wrap-loading").show();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
            action: "sql_migrate_ebmd",
        },
        dataType: "json",
        success: function (data) {
            jQuery("#wrap-loading").hide();
            return alert(data.message);
        },
        error: function (e) {
            console.log(e);
            return alert(data.message);
        },
    });
}