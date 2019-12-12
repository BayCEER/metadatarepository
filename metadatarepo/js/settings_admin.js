
$(document).ready(function () {
    $('#metadatarepo_settings input, #metadatarepo_settings textarea').on('change', function() {
    	var e =$(this);
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/metadatarepo/admin'),
            data: {
                'type': e.attr('id'),
                'value': e.val()
            },
            success: function () {
                OC.Notification.showTemporary(t('metadatarepo', 'metadata repository configuration saved'));
            }
        });
    });

    function set_field_onchange(){
        $("input[type=checkbox]" ).change(function(){
        	var res=[];
        	$("input[type=checkbox]:checked").each(function(){
        		res.push($(this).val());
        	});
        	$.ajax({
                type: 'POST',
                url: OC.generateUrl('apps/metadatarepo/admin'),
                data: {
                    'type': "search_fields",
                    'value': res.join()
                },
                success: function () {
                    OC.Notification.showTemporary(t('metadatarepo', 'metadata repository configuration saved'));
                }
            });
        })

    }
    
    function load_selected_fields(){
        $.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/metadatarepo/query/fields'),
            data: {
            	'selected':1
            },
            success: function (data) {
            	$.each(data,function(){
            		$("#field_"+this).prop("checked",true);
            	})
                //set up onChange handler
            	set_field_onchange();
            }
        });
    	
    }
    
    //Load all fields
    $.ajax({
        type: 'GET',
        url: OC.generateUrl('apps/metadatarepo/query/fields'),
        data: {
        },
        success: function (data) {
        	$.each(data,function(){
        		$("#search_fields").append('<input type="checkbox" value="'+this+'" id="field_'+this+'"><label for="field_'+this+'">'+
        				this+'</label><br/>');
        	});
            //Load selected fields
        	load_selected_fields();
        }
    });


   
});