;jQuery(document).ready(function() {
	"use strict";

    // Remove an update record.
	jQuery(".js-cfupdates-btn-remove").bind("click", function(event) {
		
		event.preventDefault();
		
		var question  = jQuery("#cf-hidden-question").val();

		var $answer = window.confirm(question);
		if( false === $answer ) {
			return;
		}
		
		var id 		  = parseInt(jQuery(this).data("id"));
		var elementId = "update"+id;
		
		var data 	  = {"id": id};
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=update.remove",
			type: "POST",
			data: data,
			dataType: "text json",
			success: function(response) {
				
				if(response.success) {
					jQuery("#"+elementId).fadeOut("slow", function() {
						jQuery(this).remove();
					});

                    PrismUIHelper.displayMessageSuccess(response.title, response.text);
				} else {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
				}
				
				// Reset form data if the element has been loaded for editing.
				var currentElementId = parseInt(jQuery("#jform_id").val());
				if(id === currentElementId) {
					jQuery("#jform_title").val("");
					jQuery("#jform_description").val("");
					jQuery("#jform_id").val("");
				}
			}
				
		});
		
	});
	
	
	jQuery(".js-cfupdates-btn-edit").bind("click", function(event) {
		
		event.preventDefault();
		
		jQuery.ajax({
            url: "index.php?option=com_crowdfunding&format=raw&task=update.getData&id="+jQuery(this).data("id"),
			method: "get",
			dataType: "text json",
            cache: false
		}).done(function(response){

            console.log(response);

            if(!response.success) {
                PrismUIHelper.displayMessageFailure(response.title, response.text);
            }

            jQuery("#jform_title").val(response.data.title);
            jQuery("#jform_description").val(response.data.description);
            jQuery("#jform_id").val(response.data.id);

        });
		
	});
	
	
	jQuery("#js-cfupdates-btn-reset").bind("click", function(event) {
		
		event.preventDefault();

		jQuery("#jform_title").val("");
		jQuery("#jform_description").val("");
		jQuery("#jform_id").val("");

	});

});
	
