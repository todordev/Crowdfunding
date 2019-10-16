jQuery(document).ready(function () {
	"use strict";

    var btnFollowElement = jQuery("#js-plgsocialshare-btn-follow");

    btnFollowElement.on("click", function(event){
		event.preventDefault();

        var userId = jQuery(this).data('uid');
        if (!userId) {
            window.location = "index.php?option=com_users&view=registration";
            return;
        }

        var state = jQuery(this).data('state');
        state = (!state) ? 1 : 0;

        var data = {
            pid: jQuery(this).data('pid'),
            state: state
        };

        var $this = this;
        var $textElement = jQuery($this).find("#js-plgsocialshare-btn-text");

        jQuery.ajax({
            url: "index.php?option=com_crowdfunding&format=raw&task=project.follow",
            type: "post",
            data: data,
            dataType: "text json",
            async: false
        }).done(function(response){

            if (response.success) {
                if (response.data.state) {
                    jQuery($this).removeClass("btn-default").addClass("btn-primary");
                    $textElement.text(Joomla.JText._('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOWING'));
                } else {
                    jQuery($this).removeClass("btn-primary").removeClass("btn-danger").addClass("btn-default");
                    $textElement.text(Joomla.JText._('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOW'));
                }

                jQuery($this).data("state", response.data.state);
            }

        });
	});

    btnFollowElement.on({
        mouseenter: function () {

            var $textElement = jQuery(this).find("#js-plgsocialshare-btn-text");

            var state = jQuery(this).data('state');

            if (state) {
                jQuery(this).removeClass("btn-primary").addClass("btn-danger");
                $textElement.text(Joomla.JText._('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_UNFOLLOW'));
            }
        },
        mouseleave: function () {
            var $textElement = jQuery(this).find("#js-plgsocialshare-btn-text");

            var state = jQuery(this).data('state');

            if (state) {
                jQuery(this).removeClass("btn-danger").addClass("btn-primary");
                $textElement.text(Joomla.JText._('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOWING'));
            }
        }
    });
});