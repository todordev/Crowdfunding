jQuery(document).ready(function() {

    window.closeProjectModal = function(){
        jQuery('#js-modal-project').modal('hide');
    };

    jQuery(".jform_project_id").on('click', function(event){
        event.preventDefault();
        jQuery('#js-modal-project').modal('show');
    });
    
});