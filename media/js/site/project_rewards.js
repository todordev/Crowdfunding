;jQuery(document).ready(function() {
    "use strict";

    jQuery(".js-cfreward-unsaved-tt").tooltip();

    var $rewardsWrapperElement = jQuery("#rewards_wrapper");

	jQuery("#cf_add_new_reward").bind("click", function(event) {
		event.preventDefault();
        createRewardBox();
	});

    $rewardsWrapperElement.on("click", ".js-btn-remove-reward", function(event) {
		event.preventDefault();

        var $theElement = jQuery(this).closest(".js-cfreward-panel");

        var rewardId    = $theElement.find(".js-cfreward-reward-id").val();
        var indexId     = $theElement.find(".js-cfreward-index-id").val();

		var rewardTitle = jQuery("#reward_title_"+indexId).val();
		var rewardDesc 	= jQuery("#reward_description_"+indexId).val();

		// Confirm reward removing.
		if((rewardId > 0) || (rewardTitle.length > 0) || (rewardDesc.length > 0)) {
			if(!window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_REWARD')) ) {
				return;
			}
		}
		
		if(rewardId) { // Delete the reward in database and remove the element it from UI.
			
			var data = "rid[]="+rewardId;
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding&format=raw&task=rewards.remove",
				type: "POST",
				data: data,
				dataType: "text json",
				success: function(response) {
					
					if(response.success) {
						jQuery("#reward_box_"+indexId).remove();
                        PrismUIHelper.displayMessageSuccess(response.title, response.text);
					} else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
					}
					
				}
					
			});
			
		} else { // Remove the element 
			jQuery("#reward_box_"+indexId).remove();
		}
		
	});


    // Check for image elements and enabled rewards functionality.
    var imageWrappers = jQuery(".js-reward-image-wrapper");
    if (imageWrappers.length > 0) {
        // Style file input
        var $fileInputs = jQuery('.js-reward-image');

        $fileInputs.fileinput({
            browseLabel: Joomla.JText._('COM_CROWDFUNDING_PICK_IMAGE'),
            browseClass: "btn btn-success",
            browseIcon: '<span class="fa fa-picture-o"></span> ',
            showUpload: false,
            showPreview: false,
            removeLabel: "",
            removeClass: "btn btn-danger",
            removeIcon: '<span class="fa fa-trash"></span> '
        });

        $fileInputs.on('fileloaded', function() {
            var $theElement = jQuery(this).closest(".js-cfreward-panel");
            var indexId     = $theElement.find(".js-cfreward-index-id").val();

            jQuery('#js-cfreward-unsaved-tt'+indexId).show();
        });

        $fileInputs.on('fileclear', function() {
            var $theElement = jQuery(this).closest(".js-cfreward-panel");
            var indexId     = $theElement.find(".js-cfreward-index-id").val();

            jQuery('#js-cfreward-unsaved-tt'+indexId).hide();
        });
    }

    $rewardsWrapperElement.on("click", ".js-btn-remove-reward-image", function(event) {
		event.preventDefault();
		
		var rewardId = jQuery(this).data("reward-id");
		
		// Confirm reward removing.
		if(rewardId) {
			if(!window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE')) ) {
				return;
			}
		}
		
		var self = this;
		
		// Delete the reward image.
		if(rewardId) { 
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding",
				type: "POST",
				data: {
                    rid: rewardId,
                    format: "raw",
                    task: "rewards.removeImage"
                },
				dataType: "text json",
				success: function(response) {
					
					if(response.success) {
						jQuery("#js-reward-image-"+rewardId).attr("src", "media/com_crowdfunding/images/no_image.png");
						jQuery(self).remove();

                        PrismUIHelper.displayMessageSuccess(response.title, response.text);
					} else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
					}
					
				}
					
			});
			
		}
	});

    $rewardsWrapperElement.on("click", ".js-cfreward-move-up, .js-cfreward-move-down", function(event) {
        var $theElement = jQuery(this).closest(".js-cfreward-panel");
        var indexId     = $theElement.find(".js-cfreward-index-id").val();

        var action  = jQuery(this).data("action");

        if (indexId > 0) {
            var currentPanel = jQuery("#reward_box_" + indexId);

            // Move the panel down.
            if (action == "down") {
                var nextPanel = currentPanel.next(".js-cfreward-panel");

                if (nextPanel.length > 0 && nextPanel.hasClass("js-cfreward-panel")) {
                    jQuery(currentPanel).hide("slow", function() {
                        currentPanel.insertAfter(nextPanel);
                        jQuery(currentPanel).show("fast");

                        var nextPanelOrderingIndex = jQuery(nextPanel).find(".js-cfreward-ordering").val();
                        var currentPanelOrderingIndex  = jQuery(currentPanel).find(".js-cfreward-ordering").val();

                        jQuery(nextPanel).find(".js-cfreward-ordering").val(currentPanelOrderingIndex);
                        jQuery(currentPanel).find(".js-cfreward-ordering").val(nextPanelOrderingIndex);
                    });
                }
            }

            // Move the panel up.
            if (action == "up") {
                var previousPanel = currentPanel.prev(".js-cfreward-panel");

                if (previousPanel.length > 0 && previousPanel.hasClass("js-cfreward-panel")) {
                    jQuery(currentPanel).hide("slow", function() {
                        currentPanel.insertBefore(previousPanel);
                        jQuery(currentPanel).show("fast");

                        var previousPanelOrderingIndex = jQuery(previousPanel).find(".js-cfreward-ordering").val();
                        var currentPanelOrderingIndex  = jQuery(currentPanel).find(".js-cfreward-ordering").val();

                        jQuery(previousPanel).find(".js-cfreward-ordering").val(currentPanelOrderingIndex);
                        jQuery(currentPanel).find(".js-cfreward-ordering").val(previousPanelOrderingIndex);
                    });
                }
            }
        }

    });

    // Copy element.
    $rewardsWrapperElement.on("click", ".js-cfreward-copy", function(event) {

        var $theElement = jQuery(this).closest(".js-cfreward-panel");

        var indexId = $theElement.find(".js-cfreward-index-id").val();
        var $reward = jQuery("#reward_box_"+indexId);

        var amount = $reward.find("#reward_amount_"+indexId).val();
        var title = $reward.find("#reward_title_"+indexId).val();
        var description = $reward.find("#reward_description_"+indexId).val();
        var available = $reward.find("#reward_number_"+indexId).val();
        var delivery = $reward.find("#reward_delivery_"+indexId).val();

        createRewardBox(amount, title, description, available, delivery);
    });

    function createRewardBox(amount, title, description, available, delivery) {

        var $itemsNumberElement = jQuery("#items_number");

        var item 		= jQuery("#reward_tmpl").clone();
        var itemsNumber = parseInt($itemsNumberElement.attr("value"));

        if (itemsNumber > 30) {
            itemsNumber = 20;
        }
        var indexNumber = itemsNumber + 1;

        // Set the new number of the elements.
        $itemsNumberElement.attr("value", indexNumber);

        // Clone element
        jQuery(item).attr("id", "reward_box_d");
        jQuery(item).appendTo("#rewards_wrapper");

        var rewardBoxId = "reward_box_"+indexNumber;

        // Element wrapper
        jQuery("#reward_box_d", "#rewards_wrapper").attr("id", rewardBoxId);

        // Element
        var theElement = jQuery("#" + rewardBoxId);

        theElement.find(".js-cfreward-index-id").val(indexNumber);

        // Amount
        theElement.find("#reward_amount_label_d").attr("for", "reward_amount_"+indexNumber).removeAttr("id");
        if (!amount) {
            theElement.find("#reward_amount_d").attr("name", "rewards["+indexNumber+"][amount]").attr("id", "reward_amount_"+indexNumber);
        } else {
            theElement.find("#reward_amount_d").attr("name", "rewards["+indexNumber+"][amount]").attr("id", "reward_amount_"+indexNumber).val(amount);
        }

        // Title
        theElement.find("#reward_title_title_d").attr("for", "reward_title_"+indexNumber).removeAttr("id");
        if (!title) {
            theElement.find("#reward_title_d").attr("name", "rewards["+indexNumber+"][title]").attr("id", "reward_title_"+indexNumber);
        } else {
            theElement.find("#reward_title_d").attr("name", "rewards["+indexNumber+"][title]").attr("id", "reward_title_"+indexNumber).val(title);
        }

        // Description
        theElement.find("#reward_description_title_d").attr("for", "reward_description_"+indexNumber).removeAttr("id");
        if (!description) {
            theElement.find("#reward_description_d").attr("name", "rewards["+indexNumber+"][description]").attr("id", "reward_description_"+indexNumber);
        } else {
            theElement.find("#reward_description_d").attr("name", "rewards["+indexNumber+"][description]").attr("id", "reward_description_"+indexNumber).val(description);
        }

        // Available
        theElement.find("#reward_number_title_d").attr("for", "reward_number_"+indexNumber).removeAttr("id");
        if (!available) {
            theElement.find("#reward_number_d").attr("name", "rewards["+indexNumber+"][number]").attr("id", "reward_number_"+indexNumber);
        } else {
            theElement.find("#reward_number_d").attr("name", "rewards["+indexNumber+"][number]").attr("id", "reward_number_"+indexNumber).val(available);
        }

        // Delivery
        theElement.find("#reward_delivery_title_d").attr("for", "reward_delivery_"+indexNumber).removeAttr("id");
        if (!delivery) {
            theElement.find("#reward_delivery_d").attr("name", "rewards["+indexNumber+"][delivery]").attr("id", "reward_delivery_"+indexNumber);
        } else {
            theElement.find("#reward_delivery_d").attr("name", "rewards["+indexNumber+"][delivery]").attr("id", "reward_delivery_"+indexNumber).val(delivery);
        }

        // Reward ID
        theElement
            .find("#reward_id_d")
            .attr("name", "rewards["+indexNumber+"][id]")
            .removeAttr("id");

        // Prepare the element ordering.
        theElement
            .find(".js-cfreward-ordering")
            .attr("name", "rewards["+indexNumber+"][ordering]")
            .val(indexNumber);

        // The button "remove".
        theElement
            .find("#reward_remove_d")
            .attr("id", "reward_remove_"+indexNumber);

        // Display form
        jQuery(item).show();

        // Calendar
        jQuery("#reward_delivery_d_datepicker", "#"+rewardBoxId).attr("id", "reward_delivery_"+indexNumber+"_datepicker");
        jQuery("#reward_delivery_d_img", "#"+rewardBoxId).attr("id", "reward_delivery_"+indexNumber+"_img");
        jQuery("#reward_delivery_d_icon", "#"+rewardBoxId).attr("id", "reward_delivery_"+indexNumber+"_icon");
        jQuery("#reward_delivery_"+indexNumber+"_datepicker").datetimepicker({
            format: projectWizard.dateFormat,
            locale: projectWizard.locale,
            allowInputToggle: true
        });

        jQuery("#js-cfreward-unsaved-tt-d", "#"+rewardBoxId).attr("id", "js-cfreward-unsaved-tt"+indexNumber);
        jQuery("#js-cfreward-unsaved-tt"+indexNumber).tooltip();
    }
});