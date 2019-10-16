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
?>
<?php if (!$this->item->published) { ?>
    <a class="btn btn-default btn-lg" href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>" target="_blank">
        <span class="fa fa-eye"></span>
        <?php echo JText::_('COM_CROWDFUNDING_PREVIEW');?>
    </a>
    <a class="btn btn-primary btn-lg" id="js-btn-project-publish" href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=projects.savestate&id='.(int)$this->item->id.'&state=1&'.JSession::getFormToken().'=1&return='.base64_encode($this->returnUrl)); ?>">
        <span class="fa fa-check"></span>
        <?php echo JText::_('COM_CROWDFUNDING_LAUNCH');?>
    </a>
    <p class="alert alert-info mt-10 p-5 text-justify font-small">
        <span class="fa fa-info-circle"></span>
        <?php echo JText::_('COM_CROWDFUNDING_NOTE_LAUNCH_PROJECT'); ?>
    </p>
<?php } else { ?>
    <a class="btn btn-danger btn-lg" id="js-btn-project-unpublish" href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=projects.savestate&id='.(int)$this->item->id.'&state=0&'.JSession::getFormToken().'=1&return='.base64_encode($this->returnUrl)); ?>">
        <span class="fa fa-stop"></span>
        <?php echo JText::_('COM_CROWDFUNDING_STOP');?>
    </a>
<?php } ?>
