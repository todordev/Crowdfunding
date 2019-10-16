;jQuery(document).ready(function() {
	"use strict";

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
        $imageWrapper: {},
        $cropperImage: {},
        cropperInitialized: false,
        token: {},
        fields: {},
        $modal: {},

        init: function() {
            this.imageWidth   = parseInt(projectWizardBasic.imageWidth);
            this.imageHeight  = parseInt(projectWizardBasic.imageHeight);
            this.aspectRatio  = projectWizardBasic.aspectRatio;

            // Set picture wrapper size.
            this.$pictureWrapper = jQuery("#js-cropper-fixed-dragger");

            this.$image          = jQuery("#js-wizard-image");
            this.$imageWrapper   = jQuery("#js-wizard-image-wrapper");
            this.$cropperImage   = this.$pictureWrapper.find("#js-cropper-img");
            this.$btnImageRemove = jQuery("#js-wizard-btn-remove-image");

            // Prepare the token as an object.
            this.$formToken = jQuery("#js-wizard-form-token");
            this.token[this.$formToken.attr('name')] = 1;

            // Get the loader.
            this.$uploaderLoader  = jQuery("#js-uploader-loader");
            this.$modalLoader     = jQuery("#js-cropper-loader");

            this.projectId        = parseInt(jQuery("#jform_id").val());
            if (!this.projectId) {
                this.projectId = 0;
            }

            // Prepare default form fields.
            this.fields   = jQuery.fn.extend({}, {id: this.projectId, format: 'raw'}, this.token);

            // Initialize the modal plugin.
            this.$modal   = jQuery("#js-cropper-modal-wrapper").remodal({
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

            jQuery('#js-image-fileupload').fileupload({
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

                        // Calculate Wrapper Size.
                        var wrapper = $this.calculateWrapperSize(response.result.data);

                        if ($this.cropperInitialized) {
                            $this.$cropperImage.cropper("replace", response.result.data.url);
                        } else {
                            $this.$cropperImage.attr("src", response.result.data.url);

                            $this.$cropperImage.cropper({
                                viewMode: 3,
                                aspectRatio: $this.aspectRatio,
                                autoCropArea: 0.8, // Center 80%
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

            jQuery("#js-cropper-btn-cancel").on("click", function() {

                // Prepare fields.
                var fields = jQuery.fn.extend({}, {task: 'story.cancelImageCrop'}, $this.fields);

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

            jQuery("#js-cropper-btn-crop").on("click", function(event) {
                var croppedData = $this.$cropperImage.cropper("getData");

                // Prepare data.
                var data = {
                    width: Math.round(croppedData.width),
                    height: Math.round(croppedData.height),
                    x: Math.round(croppedData.x),
                    y: Math.round(croppedData.y)
                };

                // Prepare fields.
                var fields = jQuery.fn.extend({task: 'story.cropImage'}, data, $this.fields);

                jQuery.ajax({
                    url: "index.php?option=com_crowdfunding",
                    type: "POST",
                    data: fields,
                    dataType: "text json",
                    beforeSend : function() {
                        $this.$modalLoader.show();
                    }

                }).done(function(response) {

                    if(!response.success) {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
                    } else {
                        $this.$modalLoader.hide();
                        $this.$modal.close();

                        $this.$image.attr("src", response.data.url);

                        // Display the button "Remove Image".
                        if ($this.projectId > 0) {
                            $this.$btnImageRemove.show();
                            $this.$imageWrapper.show();
                        }
                    }
                });
            });
        },

        initCloseModal: function() {

            var $this = this;

            jQuery(document).on('closed', '#js-cropper-modal-wrapper', function () {
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

                    // Prepare fields.
                    var fields = jQuery.fn.extend({}, {task: 'story.removeImage'}, $this.fields);

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
                            $this.$imageWrapper.hide();
                            $this.$image.attr("src", '');

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