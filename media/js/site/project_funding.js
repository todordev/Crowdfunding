;jQuery(document).ready(function() {
    "use strict";

    var $durationDaysElement = jQuery('#js-funding-duration-days');
    var $durationDateElement = jQuery("#js-funding-duration-date");

	// Disable input of date and hide calendar icon
	if($durationDaysElement.is(':checked')) {
		disableDate();
	}
	
	// Disable input of date and hide calendar icon
	if($durationDateElement.is(':checked')) {
		disableDays();
	}
	
	// Event for days
    $durationDaysElement.on("click", function() {
		disableDate();
	});
	
	// Event for date
    $durationDateElement.on("click", function() {
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
		jQuery("#jform_funding_end").attr('disabled','disabled');
        jQuery("#jform_funding_end_img").attr('disabled','disabled');

        jQuery("#jform_funding_days").removeAttr("disabled");
    }
	
	function disableDays() {
		jQuery("#jform_funding_end").removeAttr("disabled");
		jQuery("#jform_funding_end_img").removeAttr("disabled");

        jQuery("#jform_funding_days").attr('disabled','disabled');
    }
	
	
	// Initialize form validation using Parslay
	jQuery('#js-cf-funding-form').parsley({
		uiEnabled: false,
		messages: {
			required: Joomla.JText._('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED')
		}
	});
});