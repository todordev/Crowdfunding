;jQuery(document).ready(function() {
    "use strict";

	jQuery(".js-cfcomments-btn-remove").bind("click", function(event) {
		
		event.preventDefault();
		
		var question  = jQuery("#cf-hidden-question").val();
		
		var $answer   = window.confirm(question);
		if( false === $answer ) {
			return;
		}
		
		var id 		  = parseInt(jQuery(this).data("id"));
		var elementId = "comment"+id;
		
		var data 	  = {"id": id};
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=comment.remove",
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
					jQuery("#jform_comment").val("");
					jQuery("#jform_id").val("");
				}
			}
				
		});
		
	});
	
	
	jQuery(".js-cfcomments-btn-edit").bind("click", function(event) {
		
		event.preventDefault();
		
		var id = jQuery(this).data("id");
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=comment.getdata&id="+id,
			type: "GET",
			dataType: "text json",
			success: function(response) {
				
				if(!response.success) {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
				}
				
				jQuery("#jform_comment").val(response.data.comment);
				jQuery("#jform_id").val(response.data.id);
				
			}
				
		});
		
	});
	
	
	jQuery("#js-cfcomments-btn-reset").bind("click", function(event) {
		
		event.preventDefault();
		
		jQuery("#jform_comment").val("");
		jQuery("#jform_id").val("");
		
	});
	
});
