;jQuery(document).ready(function() {

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
    
});