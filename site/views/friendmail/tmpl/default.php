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
<div class="friendmail<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo JText::_('COM_CROWDFUNDING_SEND_TO_FRIEND'); ?></h2>
            <p><?php echo JText::_('COM_CROWDFUNDING_SEND_TO_FRIEND_HELP'); ?></p>
            <form method="post" action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" class="bs-docs-example mt-0" id="form-send-to-friend" autocomplete="off">
                <div class="form-group">
                    <?php echo $this->form->getLabel('subject'); ?>
                    <?php echo $this->form->getInput('subject'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('sender_name'); ?>
                    <?php echo $this->form->getInput('sender_name'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('sender'); ?>
                    <?php echo $this->form->getInput('sender'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('receiver'); ?>
                    <?php echo $this->form->getInput('receiver'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('message'); ?>
                    <?php echo $this->form->getInput('message'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('captcha'); ?>
                    <?php echo $this->form->getInput('captcha'); ?>
                </div>

                <?php echo $this->form->getInput('id'); ?>
                <?php echo JHtml::_('form.token'); ?>

                <input type="hidden" name="task" value="friendmail.send"/>
                <button type="submit" class="btn btn-primary"><?php echo JText::_('COM_CROWDFUNDING_SEND'); ?></button>
            </form>
        </div>
    </div>
</div>