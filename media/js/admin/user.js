jQuery(document).ready(function() {

    jQuery(".js-reward-state").on('change', function(event){
        event.preventDefault();

        var state  = jQuery(this).val();
        var txnId  = jQuery(this).data('id');

        var fields = {
            'id': txnId,
            'state': state,
            'format': 'raw'
        };

        var token = jQuery('#js-form-token').attr('name');
        fields[token] = 1;

        jQuery.ajax({
            url: 'index.php?option=com_crowdfunding&task=transaction.changeRewardsState',
            type: "POST",
            dataType: "text json",
            data: fields
        }).done(function(response) {

            if (response.success) {
                PrismUIHelper.displayMessageSuccess(response.title, response.text);

                if (state == 1) {
                    jQuery('#js-reward-row-'+txnId).addClass('success');
                } else {
                    jQuery('#js-reward-row-'+txnId).removeClass('success');
                }

            } else {
                PrismUIHelper.displayMessageFailure(response.title, response.text);
            }

        });

    });
    
});