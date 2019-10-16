;jQuery(document).ready(function() {
    "use strict";

	jQuery(".js-reward-amount").on("click", function() {
		
		var elements  	  = jQuery(this).find(".js-reward-amount-radio");
		var radio  	  	  = elements[0];
		
		if(jQuery(radio).is(':checked') === false) {
			jQuery(".js-reward-amount-radio").attr('checked', false);
			jQuery(radio).attr('checked', true);
	    }
		
		var rewardId      = jQuery(radio).data("id");
        var amountElement = jQuery("#js-current-amount");
		
		var amount  	  = parseFloat( jQuery(radio).val() );

		jQuery(amountElement).val(amount);
		jQuery("#js-reward-id").val(rewardId);
	});
	
});