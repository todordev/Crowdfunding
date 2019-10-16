;jQuery(document).ready(function () {
    "use strict";

    // Initialize symbol length indicator
    jQuery('#jform_short_desc').maxlength({
        alwaysShow: true,
        placement: 'bottom-right'
    });

    // Validate the fields.
    jQuery('#js-cf-project-form').parsley({
        uiEnabled: false
    });

    /** Location Manager **/
    var locationManager = {

        $formToken: '',
        $locationElement: {},
        $countryElement: {},
        $countryLoader: {},
        $regionElement: {},
        $locationIdElement: {},

        init: function() {
            this.$formToken           = jQuery('#js-form-token');
            this.$locationElement     = jQuery('#jform_location');
            this.$locationIdElement   = jQuery('#jform_location_id');

            this.initLocationAutocomplete();
        },

        initLocationAutocomplete: function() {

            var $this  = this;
            var params = {
                task: 'project.getLocations',
                format: 'raw'
            };

            // Set form token.
            params[this.$formToken.attr('name')] = 1;

            this.$locationElement.autocomplete({
                serviceUrl: '/index.php?option=com_crowdfunding',
                params: params,
                minChars: 3,
                onSearchStart: function(query) {
                    // query.country_code = $this.$countryElement.val();
                },
                onSelect: function (suggestion) {
                    $this.$locationIdElement.val(suggestion.data);
                },
                transformResult: function(response) {
                    var r = JSON.parse(response);

                    return {
                        suggestions: jQuery.map(r.data, function(dataItem) {
                            return { value: dataItem.name, data: dataItem.id};
                        })
                    };
                }
            });
        }
    };

    locationManager.init();

    /** Image Tools **/

    var imageTools = {

        projectId: 0,
        aspectWidth: 0,
        aspectHeight: 0,
        imageWidth: 0,
        imageHeight: 0,
        aspectRatio: '',

        $formToken: {},
        $uploaderLoader: {},
        $loader: {},

        $btnImageRemove: {},
        $pictureWrapper: {},
        $image: {},
        $cropperImage: {},
        cropperInitialized: false,
        token: {},
        fields: {},
        $modal: {},

        init: function() {
            this.imageWidth   = parseInt(crowdfundingOptions.imageWidth);
            this.imageHeight  = parseInt(crowdfundingOptions.imageHeight);
            this.aspectRatio  = crowdfundingOptions.aspectRatio;

            // Set picture wrapper size.
            this.$pictureWrapper = jQuery("#js-fixed-dragger-cropper");

            this.$image          = jQuery("#js-thumb-img");
            this.$cropperImage   = this.$pictureWrapper.find("#js-cropper-img");
            this.$btnImageRemove = jQuery("#js-btn-remove-image");

            // Prepare the token as an object.
            this.$formToken = jQuery("#js-form-token");
            this.token[this.$formToken.attr('name')] = 1;

            // Get the loader.
            this.$uploaderLoader  = jQuery("#js-fileupload-loader");
            this.$modalLoader     = jQuery("#js-modal-loader");

            // Set project ID.
            this.projectId        = parseInt(jQuery("#jform_id").val());
            if (!this.projectId) {
                this.projectId = 0;
            }

            // Prepare default form fields.
            this.fields   = jQuery.fn.extend({}, {id: this.projectId, format: 'raw'}, this.token);

            // Initialize the modal plugin.
            this.$modal   = jQuery("#js-modal-wrapper").remodal({
                hashTracking: false,
                closeOnConfirm: false,
                closeOnCancel: false,
                closeOnEscape: false,
                closeOnOutsideClick: false
            });

            this.initFileUploader();
            this.initButtonCrop();
            this.initButtonCancel();
            this.initButtonRemoveImage();
            this.initCloseModal();
        },

        calculateWrapperSize: function(fileData) {
            var imageWidth    = parseInt(fileData.width);
            var imageHeight   = parseInt(fileData.height);

            var wrapper = {
                width: imageWidth,
                height: imageHeight
            };

            if (imageWidth > 600) {
                var x = (imageWidth/600).toFixed(3);
                wrapper.width = Math.round(imageWidth / x);
            }

            if (imageHeight > 400) {
                var y = (imageHeight / 400).toFixed(3);
                wrapper.height = Math.round(imageHeight / y);
            }

            return wrapper;
        },

        changeCropperSize: function(wrapper) {
            this.$pictureWrapper.css({
                width: wrapper.width,
                height: wrapper.height
            });
        },

        initFileUploader: function() {

            var $this = this;

            jQuery('#js-thumb-fileupload').fileupload({
                dataType: 'json',
                formData: $this.fields,
                singleFileUploads: true,
                send: function() {
                    $this.$uploaderLoader.show();
                },
                fail: function() {
                    $this.$uploaderLoader.hide();
                },
                done: function (event, response) {

                    if(!response.result.success) {
                        PrismUIHelper.displayMessageFailure(response.result.title, response.result.text);
                    } else {

                        if ($this.cropperInitialized) {
                            $this.$cropperImage.cropper("replace", response.result.data.url);
                        } else {
                            $this.$cropperImage.attr("src", response.result.data.url);

                            // Calculate Wrapper Size.
                            var wrapper = $this.calculateWrapperSize(response.result.data);

                            $this.$cropperImage.cropper({
                                viewMode: 3,
                                aspectRatio: $this.aspectRatio,
                                autoCropArea: 0.6, // Center 60%
                                multiple: false,
                                dragCrop: false,
                                dashed: false,
                                movable: false,
                                resizable: true,
                                zoomable: false,
                                minContainerWidth: wrapper.width,
                                minContainerHeight: wrapper.height,
                                built: function() {
                                    $this.cropperInitialized = true;
                                }
                            });
                        }

                        $this.changeCropperSize(wrapper);

                        $this.$modal.open();
                    }

                    // Hide ajax loader.
                    $this.$uploaderLoader.hide();
                }
            });
        },

        initButtonCancel: function() {

            var $this = this;

            jQuery("#js-crop-btn-cancel").on("click", function() {

                // Prepare fields.
                var fields = jQuery.fn.extend({}, {task: 'project.cancelImageCrop'}, $this.fields);

                jQuery.ajax({
                    url: "index.php?option=com_crowdfunding",
                    type: "POST",
                    data: fields,
                    dataType: "text json",
                    beforeSend : function() {
                        $this.$modalLoader.show();
                    }
                }).done(function(){
                    $this.$modalLoader.hide();
                    $this.$modal.close();
                });
            });
        },

        initButtonCrop: function() {

            var $this = this;

            jQuery("#js-crop-btn").on("click", function(event) {
                var croppedData = $this.$cropperImage.cropper("getData");

                // Prepare data.
                var data = {
                    width: Math.round(croppedData.width),
                    height: Math.round(croppedData.height),
                    x: Math.round(croppedData.x),
                    y: Math.round(croppedData.y)
                };

                // Prepare fields.
                var fields = jQuery.fn.extend({task: 'project.cropImage'}, data, $this.fields);

                jQuery.ajax({
                    url: "index.php?option=com_crowdfunding",
                    type: "POST",
                    data: fields,
                    dataType: "text json",
                    beforeSend : function() {
                        $this.$modalLoader.show();
                    }

                }).done(function(response) {

                    if(response.success) {
                        $this.$modalLoader.hide();
                        $this.$modal.close();

                        $this.$image.attr("src", response.data.src);

                        // Display the button "Remove Image".
                        $this.$btnImageRemove.show();
                    } else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
                    }
                });
            });
        },

        initCloseModal: function() {

            var $this = this;

            jQuery(document).on('closed', '#js-modal-wrapper', function () {
                $this.$cropperImage.cropper("destroy");
                $this.$cropperImage.attr("src", '');
                $this.cropperInitialized = false;
            });
        },
        initButtonRemoveImage: function() {

            var $this = this;

            // Add confirmation question to the remove image button.
            this.$btnImageRemove.on("click", function(event){
                event.preventDefault();

                if (window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE'))) {

                    var task = 'project.removeCroppedImages';
                    if ($this.projectId > 0) {
                        task = 'project.removeImage';
                    }

                    // Prepare fields.
                    var fields = jQuery.fn.extend({}, {task: task}, $this.fields);

                    jQuery.ajax({
                        url: "index.php?option=com_crowdfunding",
                        type: "POST",
                        data: fields,
                        dataType: "text json",
                        beforeSend : function() {
                            $this.$uploaderLoader.show();
                        }

                    }).done(function(response) {

                        if(response.success) {
                            $this.$uploaderLoader.hide();

                            // Display the button "Remove Image".
                            $this.$btnImageRemove.hide();
                            $this.$image.attr("src", '/media/com_crowdfunding/images/no_image.png');

                            PrismUIHelper.displayMessageSuccess(response.title, response.text);
                        } else {
                            $this.$uploaderLoader.hide();
                            PrismUIHelper.displayMessageFailure(response.title, response.text);
                        }
                    });
                }
            });
        }
    };

    // Initialize image tools object and its properties.
    imageTools.init();
});