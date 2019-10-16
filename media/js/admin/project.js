jQuery(document).ready(function() {

    "use strict";

	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'project.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };
    
    jQuery('.fileupload').fileinput();
    
    // Disable input of date and hide calendar icon
	if(jQuery('#js-funding-duration-days').is(':checked')) {
		disableDate();
	}
	
	// Disable input of date and hide calendar icon
	if(jQuery('#js-funding-duration-date').is(':checked')) {
		disableDays();
	}
	
	// Event for days
	jQuery("#js-funding-duration-days").on("click", function() {
		disableDate();
	});
	
	// Event for date
	jQuery("#js-funding-duration-date").on("click", function() {
		disableDays();
	});
	
	// Event for label days
	jQuery("#jform_funding_days-lbl").on("click", function() {
		jQuery('#js-funding-duration-days').prop("checked", true);
		disableDate();
	});
	
	// Event for date
	jQuery("#jform_funding_end-lbl").on("click", function() {
		jQuery('#js-funding-duration-date').prop("checked", true);
		disableDays();
	});
	
	function disableDate() {
		jQuery("#jform_funding_days").removeAttr("disabled");
		jQuery("#jform_funding_end").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").hide();
	}
	
	function disableDays() {
		jQuery("#jform_funding_end").removeAttr("disabled");
		jQuery("#jform_funding_days").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").show();
	}

    jQuery('#jform_location_preview').typeahead({
        displayField: 'text',
        valueField: 'value',
        ajax : {
            url: "index.php?option=com_crowdfunding&format=raw&task=project.loadLocation",
            method: "get",
            triggerLength: 3,
            preProcess: function (response) {

                if (response.success === false) {
                    return false;
                }

                return response.data;
            }
        },
        onSelect: function(item) {
            jQuery("#jform_location_id").attr("value", item.value);
        }
    });
});