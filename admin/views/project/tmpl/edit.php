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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data" autocomplete="off">
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'project-settings', array('active' => 'basic')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'project-settings', 'basic', JText::_('COM_CROWDFUNDING_BASIC')); ?>
        <div class="row-fluid">
            <div class="span6">
                <?php echo $this->loadTemplate('basic'); ?>
            </div>

            <div class="span3">
            <?php if (!empty($this->item->image)) { ?>
                <img src="<?php echo $this->imagesUrl . '/' . $this->item->image; ?>"/>

                <div class="clearfix"></div>
                <br/>
                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=project.removeImage&image_type=main&id=' . (int)$this->item->id . '&' . JSession::getFormToken() . '=1'); ?>"
                   class="btn btn-danger">
                    <i class="icon-trash icon-white"></i>
                    <?php echo JText::_('COM_CROWDFUNDING_REMOVE_IMAGE'); ?>
                </a>
            <?php } else { ?>
                <img src="../media/com_crowdfunding/images/no_image.png"/>
            <?php } ?>
            </div>
            <div class="span3">
                <fieldset class="form-vertical">
                    <?php echo $this->form->renderField('catid'); ?>
                    <?php echo $this->form->renderField('type_id'); ?>
                    <?php echo $this->form->renderField('published'); ?>
                    <?php echo $this->form->renderField('approved'); ?>
                    <?php echo $this->form->renderField('access'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'project-settings', 'funding', JText::_('COM_CROWDFUNDING_FUNDING')); ?>
        <div class="row-fluid">
            <div class="span12">
                <?php echo $this->loadTemplate('funding'); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'project-settings', 'story', JText::_('COM_CROWDFUNDING_STORY')); ?>
        <div class="row-fluid">
            <div class="span6">
                <?php echo $this->loadTemplate('story'); ?>
            </div>
            <div class="span6">
                <?php if (!empty($this->item->pitch_image)) { ?>
                    <img src="<?php echo $this->imagesUrl . "/" . $this->item->pitch_image; ?>"/>

                    <div class="clearfix"></div>
                    <br/>
                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=project.removeImage&image_type=pitch&id=' . (int)$this->item->id . '&' . JSession::getFormToken() . '=1'); ?>"
                       class="btn btn-danger">
                        <i class="icon-trash icon-white"></i>
                        <?php echo JText::_('COM_CROWDFUNDING_REMOVE_IMAGE'); ?>
                    </a>

                <?php } else { ?>
                    <img src="../media/com_crowdfunding/images/no_image_large.png"/>
                <?php } ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'project-settings', 'publishing', JText::_('COM_CROWDFUNDING_PUBLISHING', true)); ?>
        <?php echo $this->loadTemplate('publishing'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'project-settings', 'options', JText::_('COM_CROWDFUNDING_OPTIONS', true)); ?>
        <?php echo $this->loadTemplate('options'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>

</form>
