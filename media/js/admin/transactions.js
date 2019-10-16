jQuery(document).ready(function() {

    jQuery(".js-reward-state").on('change', function(event){
        event.preventDefault();

        var fields = {
            'id': jQuery(this).data("id"),
            'state': jQuery(this).val(),
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
            } else {
                PrismUIHelper.displayMessageFailure(response.title, response.text);
            }
        });
    });

    jQuery(".js-txn-status").on('change', function(event){
        event.preventDefault();

        var status = jQuery(this).val();
        var txnId  = jQuery(this).data("id");

        var fields = {
            'id': txnId,
            'status': status,
            'format': 'raw'
        };

        var token = jQuery('#js-form-token').attr('name');
        fields[token] = 1;

        jQuery.ajax({
            url: 'index.php?option=com_crowdfunding&task=transaction.changeStatus',
            type: "POST",
            dataType: "text json",
            data: fields
        }).done(function(response) {

            if (response.success) {
                PrismUIHelper.displayMessageSuccess(response.title, response.text);

                var txnRow = jQuery('#txn-row-'+txnId);

                switch (status) {
                    case 'completed':
                        txnRow.removeClass('error').removeClass('warning2').removeClass('warning').addClass('success');
                        break;

                    case 'failed':
                        txnRow.removeClass('success').removeClass('warning2').removeClass('warning').addClass('error');
                        break;

                    case 'canceled':
                        txnRow.removeClass('success').removeClass('error').removeClass('warning').addClass('warning2');
                        break;

                    case 'refunded':
                        txnRow.removeClass('success').removeClass('warning2').removeClass('error').addClass('warning');
                        break;

                    case 'pending':
                        txnRow.removeClass('success').removeClass('warning2').removeClass('warning').removeClass('error');
                        break;
                }

            } else {
                PrismUIHelper.displayMessageFailure(response.title, response.text);
            }
        });
    });
    
});