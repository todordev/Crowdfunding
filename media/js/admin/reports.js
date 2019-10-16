jQuery(document).ready(function() {
    
	jQuery(".js-cf-preview-report-btn").on("click", function(event) {
		
		event.preventDefault();
		
		var url    	  = jQuery(this).attr("href");
		var projectId = jQuery(this).data("project-id");

		jQuery.ajax({
			url: url,
			type: "GET",
			dataType: "text",
			success: function(response) {
				
				// Set title
				var title = jQuery("#js-title"+projectId).html();
				jQuery("#js-modal-title").html(title);
				
				// Set data
				jQuery("#js-modal-data").html(response);
				
				// Show Modal
				jQuery('#js-preview-report-modal').modal('show');

			}
				
		});
	});
	
	jQuery("#js-modal-close-btn").on("click", function(event) {
		
		event.preventDefault();
		
		// Hide Modal.
		jQuery('#js-preview-report-modal').modal('hide');
		
		// Clear title and data.
		jQuery("#js-modal-title").html("");
		jQuery("#js-modal-data").html("");
		
	});
	
});