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
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
            <fieldset>
                <?php echo $this->form->renderField('name'); ?>
                <?php echo $this->form->renderField('country_code'); ?>
                <?php echo $this->form->renderField('admincode_id'); ?>
                <?php echo $this->form->renderField('id'); ?>
            </fieldset>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="code" value="<?php echo $this->escape($this->countryCode); ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>