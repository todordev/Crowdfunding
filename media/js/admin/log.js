jQuery(document).ready(function() {
	
	var logFilesLayout = jQuery("#js-log-files");
	
	if(logFilesLayout.length > 0) { // Only initialize evens if it is files layout.
		
		jQuery(".js-log-file").on("click", function(event) {
			
			event.preventDefault();
			
			var url    = jQuery(this).attr("href");
			var rowId  = jQuery(this).data("row-id");
			
			jQuery.ajax({
				url: url,
				type: "GET",
				dataType: "text",
				beforeSend: function() {
					
					// Show ajax loader.
					jQuery("#js-ajaxload-icon"+rowId).show();
					
				},
				success: function(response) {
					
					// Set title
					jQuery("#js-file-title").html(jQuery(this).html());
					
					// Set data
					jQuery("#js-file-preview").html(response);
					
				},
				complete: function() {
					
					// Hide ajax loader.
					jQuery("#js-ajaxload-icon"+rowId).hide();
				}
					
			});
		});


        PNotify.prototype.options.history = false;
		jQuery(".js-log-file-remove-btn").on("click", function(event) {
			
			event.preventDefault();
			
			if(confirm(Joomla.JText._('COM_CROWDFUNDING_DELETE_FILE_QUESTION'))) {
				
				var url        = jQuery(this).attr("href");
				var filename   = jQuery(this).data("filename");
				var rowId	   = jQuery(this).data("row-id");
				
				var fields = {
					file: filename,
					format: "raw"
				};
			
				// Destroy the tooltip from this element.
				jQuery(this).tooltip('destroy');
				
				jQuery.ajax({
					url: url,
					type: "POST",
					dataType: "text json",
					data: fields,
					success: function(response) {
						
						if(response.success) {
							// Set title
							jQuery("#js-file-title").html("");
							
							// Set data
							jQuery("#js-file-preview").html("");

                            PrismUIHelper.displayMessageSuccess(response.title, response.text);
							
							// Remove the row.
							jQuery("#"+rowId).remove();
							
						} else {
                            PrismUIHelper.displayMessageFailure(response.title, response.text);
						}
					}
				
				});
			}
		});
		
	}

});