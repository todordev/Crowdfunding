<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('title'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('amount'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('amount'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('number'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('number'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('distributed'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('distributed'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('delivery'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('delivery'); ?></div>
                </div>
                <?php
                if ($this->allowedImages) { ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
                    <div class="controls">
                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <span class="btn btn-file">
                                <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE"); ?></span>
                                <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_("COM_CROWDFUNDING_CHANGE"); ?></span>
                                <?php echo $this->form->getInput('image'); ?>
                            </span>
                            <span class="fileupload-preview"></span>
                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                        </div>
                    </div>
                </div>
                <?php
                } ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                </div>

            <?php echo $this->form->getInput('project_id'); ?>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

    <?php if (!empty($this->item->id) and !empty($this->item->image_thumb)) {?>
        <div class="span6">
            <div class="thumbnail">
                <img src="<?php echo $this->rewardsImagesUri . '/' . $this->item->image_thumb; ?>"  />
            </div>

            <div class="clearfix"></div>
            <br/>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=reward.removeImage&id=' . (int)$this->item->id . '&' . JSession::getFormToken() . '=1'); ?>"
               class="btn btn-danger">
                <i class="icon-trash icon-white"></i>
                <?php echo JText::_('COM_CROWDFUNDING_REMOVE_IMAGE'); ?>
            </a>

        </div>
    <?php } ?>
</div>
