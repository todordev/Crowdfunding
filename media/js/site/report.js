;jQuery(document).ready(function () {
    "use strict";

    // Initialize symbol length indicator
    var shortDesc = jQuery('#cfreport_description');
    shortDesc.attr("maxlength", 255);
    shortDesc.maxlength({
        alwaysShow: true,
        placement: 'bottom-right'
    });

    var $projectTitleElement = jQuery("#cfreport_project");

    if ($projectTitleElement) {

        // Load locations from the server
        $projectTitleElement.typeahead({
            minLength: 3,
            hint: false
        }, {
            source: function(query, syncResults, asyncResults) {

                jQuery.ajax({
                    url: "index.php?option=com_crowdfunding&format=raw&task=project.loadProject",
                    type: "GET",
                    data: {query: query},
                    dataType: "text json",
                    async: true
                }).done(function(response){
                    if (response.success === false) {
                        return false;
                    }

                    return asyncResults(response.data);
                });

            },
            async: true,
            limit: 5,
            display: "name"
        });

        $projectTitleElement.bind('typeahead:select', function(event, suggestion) {
            jQuery("#cfreport_id").attr("value", suggestion.id);
        });

    }

});