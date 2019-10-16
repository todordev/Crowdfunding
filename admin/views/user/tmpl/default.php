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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="<?php echo JSession::getFormToken();?>" value="1" id="js-form-token"/>
</form>

<div class="row-fluid">
    <div class="span4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo JHtml::_('crowdfundingbackend.profileLink', $this->socialProfile, $this->item->name, $this->item->id); ?>
            </div>
            <div class="panel-body">
                <img src="../../media/com_crowdfunding/images/no_image.png" class="img-rounded">
                <div class="small"><?php echo JText::sprintf('COM_CROWDFUNDING_REGISTERED_S', JHtml::_('date', $this->item->registerDate, JText::_('DATE_FORMAT_LC3'))); ?></div>
            </div>
        </div>
    </div>
    <div class="span4">
        <?php echo $this->loadTemplate('basic'); ?>
    </div>
    <div class="span4">
        <?php echo $this->loadTemplate('investor'); ?>
    </div>
</div>

<?php echo $this->loadTemplate('rewards'); ?>