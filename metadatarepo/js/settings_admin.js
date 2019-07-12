
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
});