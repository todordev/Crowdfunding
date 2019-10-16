jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'logs.removeall') {
        	if(confirm(Joomla.JText._('COM_CROWDFUNDING_DELETE_ALL_ITEMS'))) {
        		Joomla.submitform(task, document.getElementById('adminForm'));
        	}
        } else {
        	Joomla.submitform(task, document.getElementById('adminForm'));
        }
        
    };
    
	jQuery(".js-preview-log").on("click", function(event) {
		
		event.preventDefault();
		
		var url    = jQuery(this).attr("href");
		var itemId = jQuery(this).data("item-id");
		
		jQuery.ajax({
			url: url,
			type: "GET",
			dataType: "text",
			success: function(response) {
				
				// Set title
				var title = jQuery("#js-title"+itemId).html();
				jQuery("#js-modal-title").html(title);
				
				// Set data
				jQuery("#js-modal-data").html(response);
				
				// Show Modal
				jQuery('#js-preview-log-modal').modal('show');
				
			}
				
		});
	});
	
	jQuery("#js-modal-close-btn").on("click", function(event) {
		
		event.preventDefault();
		
		// Hide Modal.
		jQuery('#js-preview-log-modal').modal('hide');
		
		// Clear title and data.
		jQuery("#js-modal-title").html("");
		jQuery("#js-modal-data").html("");
		
	});
	
});