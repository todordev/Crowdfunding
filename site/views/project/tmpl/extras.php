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

<?php
if (!empty($this->item->event->onExtrasDisplay)) {
    echo $this->item->event->onExtrasDisplay;
}
?>

<form action="javascript: void(0);" method="post" id="js-cfextras-form-token">
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" id="js-form-token" />
    <input type="hidden" name="item_id" value="<?php echo (int)$this->item->id;?>" id="js-form-item-id" />
</form>

<div class="row">
    <div class="col-md-12">
        <a class="btn btn-primary" <?php echo $this->disabledButton;?> href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=project&layout=manager&id='.(int)$this->item->id); ?>">
            <span class="fa fa-check"></span>
            <?php echo JText::_('COM_CROWDFUNDING_CONTINUE_NEXT_STEP');?>
        </a>
    </div>
</div>
