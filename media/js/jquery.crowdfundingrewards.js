;(function ( $, window, document, undefined ) {

	"use strict";
	
    // Create the defaults once
    var pluginName = "CrowdfundingRewards";
    var defaults = {};

    // The actual plugin constructor
    function CrowdfundingRewards(element, options) {
        this.element = element;
        
        this.options = $.extend( {}, defaults, options );

        this._defaults = defaults;
        this._name     = pluginName;

        this.init();
        
    }

    CrowdfundingRewards.prototype = {

        init: function() {
        	
        	$(this.element).on("click", function(event) {
        		event.preventDefault();
        		
        		var txnId = $(this).data("txn-id");
        		
        		var fields = {
    				txn_id: txnId
        		};
        		
    			$.ajax({
    				type: "POST",
    				url: "index.php?option=com_crowdfunding&format=raw&task=rewards.changeState",
    				data: fields,
    				dataType: "text json"
    			}).done(function(response){
    				
    			});

        	});
        	
        }
        
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new CrowdfundingRewards( this, options ));
            }
        });
    };

})( jQuery, window, document );

