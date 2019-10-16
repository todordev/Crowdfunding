jQuery(document).ready(function() {

	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'report.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };
    
});