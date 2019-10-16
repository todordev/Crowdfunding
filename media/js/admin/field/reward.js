jQuery(document).ready(function() {

    window.closeRewardModal = function(){
        jQuery('#js-modal-reward').modal('hide');
    };

    jQuery(".jform_reward_id").on('click', function(event){
        event.preventDefault();
        jQuery('#js-modal-reward').modal('show');
    });
    
});