jQuery(document).ready(function() {

    jQuery("#acy_pid").chosen().change(function() {

        var projectId = parseInt(jQuery(this).val());
        var listId = parseInt(jQuery('#acy_lid').val());

        if (projectId > 0 && listId > 0) {
            getStats(projectId, listId);
        } else {
            resetStats();
        }

    });

    jQuery("#acy_lid").chosen().change(function() {

        var projectId = parseInt(jQuery('#acy_pid').val());
        var listId = parseInt(jQuery(this).val());

        if (projectId > 0 && listId > 0) {
            getStats();
        } else {
            resetStats();
        }

    });

    jQuery("#js-cftools-acy-addfunders").on('click', function() {

        var projectId = parseInt(jQuery('#acy_pid').val());
        var listId = parseInt(jQuery('#acy_lid').val());

        if (projectId > 0 && listId > 0) {
            getStats(projectId, listId);

            var fields = jQuery('#js-cftools-acyfunders').serializeJSON();
            jQuery.extend(fields, {task: "tools.addFunders"});

            jQuery.ajax({
                url: 'index.php?option=com_crowdfunding',
                type: "POST",
                data: fields,
                dataType: "text json",
                cache: false,
                beforeSend: function() {
                    jQuery("#js-cfacy-ajax-loader").show();
                    jQuery("#js-cftools-acy-addfunders").prop('disabled', true);
                }

            }).done(function(response) {
                jQuery("#js-cfacy-ajax-loader").hide();

                if (!response.success) {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
                } else {
                    PrismUIHelper.displayMessageSuccess(response.title, response.text);
                }

                getStats();

                jQuery("#js-cftools-acy-addfunders").prop('disabled', false);

            });

        } else {
            resetStats();
        }

    });

    function getStats() {

        var fields = jQuery('#js-cftools-acyfunders').serializeJSON();

        jQuery.extend(fields, {task: "tools.getAcyStats"});

        jQuery.ajax({
            url: 'index.php?option=com_crowdfunding',
            type: "GET",
            data: fields,
            dataType: "text json",
            cache: false,
            beforeSend: function() {
                jQuery("#js-cfacy-ajax-loader").show();
            }

        }).done(function(response) {
            jQuery("#js-cfacy-ajax-loader").hide();

            if (!response.success) {
                PrismUIHelper.displayMessageError(response.title, response.text);
            } else {
                jQuery('#js-acymailing-total-value').text(response.data.total);
                jQuery('#js-acymailing-forimporting-value').text(response.data.for_importing);
            }

        });

    }

    function resetStats() {
        jQuery('#js-acymailing-total-value').text(0);
        jQuery('#js-acymailing-forimporting-value').text(0);
    }
});