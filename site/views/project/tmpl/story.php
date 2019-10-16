<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if (strcmp('five_steps', $this->wizardType) === 0) {
    $layout      = new JLayoutFile('project_wizard');
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps');
}
echo $layout->render($this->layoutData);
?>
<div class="row">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-story-form" class="form-validate" enctype="multipart/form-data">
        
        <div class="col-md-12">
        
            <div class="form-group">
                <?php echo $this->form->getLabel('pitch_video'); ?>
                <?php echo $this->form->getInput('pitch_video'); ?>
				<span class="help-block"><?php echo JText::_('COM_CROWDFUNDING_FIELD_FUNDING_VIDEO_HELP_BLOCK');?></span>
            </div>

            <div class="form-group">
                <span class="btn btn-default fileinput-button">
                    <span class="fa fa-upload"></span>
                    <span><?php echo JText::_('COM_CROWDFUNDING_UPLOAD_IMAGE');?></span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="js-image-fileupload" type="file" name="pitch_image" data-url="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=story.uploadImage&format=raw');?>" />
                </span>

                <a href="javascript: void(0);" id="js-wizard-btn-remove-image" class="btn btn-danger" style="display: <?php echo $this->displayRemoveButton; ?>" role="button">
                    <span class="fa fa-trash"></span>
                    <?php echo JText::_('COM_CROWDFUNDING_REMOVE_IMAGE');?>
                </a>

                <span class="fa fa-spinner fa-spin" id="js-uploader-loader" style="display: none;" aria-hidden="true"></span>
                <span class="help-block"><?php echo JText::sprintf('COM_CROWDFUNDING_FIELD_IMAGE_HELP_BLOCK_S', $this->pWidth, $this->pHeight);?></span>
            </div>

            <div class="mtb-20" style="display: <?php echo $this->imageStyleDisplay; ?>" id="js-wizard-image-wrapper">
                <img src="<?php echo $this->pitchImage; ?>" class="img-thumbnail" id="js-wizard-image" />
            </div>

            <div class="form-group">
            <?php echo $this->form->getLabel('description'); ?>
            <?php echo $this->form->getInput('description'); ?>
        	</div>
            
            <?php echo $this->form->getInput('id'); ?>
            <input type="hidden" name="task" value="story.save" />
            <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" id="js-wizard-form-token" />

            <button type="submit" class="btn btn-primary mtb-15-0" <?php echo $this->disabledButton;?>>
                <span class="fa fa-check"></span>
                <?php echo JText::_('COM_CROWDFUNDING_SAVE_AND_CONTINUE');?>
            </button>
        </div>
    </form>
</div>

<div id="js-cropper-modal-wrapper">
    <div id="js-cropper-fixed-dragger">
        <img src="" id="js-cropper-img" />
    </div>

    <div class="mt-10">
        <a href="javascript: void(0);" class="btn btn-primary" id="js-cropper-btn-crop">
            <span class="fa fa-check-circle"></span>
            <?php echo JText::_('COM_CROWDFUNDING_CROP_IMAGE');?>
        </a>

        <a href="javascript: void(0);" class="btn btn-default" id="js-cropper-btn-cancel">
            <span class="fa fa-ban"></span>
            <?php echo JText::_('COM_CROWDFUNDING_CANCEL');?>
        </a>

        <span class="fa fa-spinner fa-spin" id="js-cropper-loader" style="display: none;" aria-hidden="true"></span>
    </div>
</div>