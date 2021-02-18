
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
            		key=this.replace(/[\W]/g,'_')
            		$("#field_"+key).prop("checked",key);
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
        		//key=this.replace(/[: ]/g,'_')
        		key=this.replace(/[\W]/g,'_')

        		$("#search_fields").append('<input type="checkbox" value="'+key+'" id="field_'+key+'"><label for="field_'+key+'">'+
        				key+'</label><br/>');
        	});
            //Load selected fields
        	load_selected_fields();
        }
    });


   
});