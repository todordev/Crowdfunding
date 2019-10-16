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

<?php echo $this->form->renderField('goal'); ?>
<?php echo $this->form->renderField('funded'); ?>
<?php echo $this->form->renderField('funding_type'); ?>
<?php echo $this->form->renderField('funding_start'); ?>

<div class="control-group">
    <div class="control-label">
        <label for="jform_funding_duration_type" id="jform_funding_duration_type-lbl">
            <?php echo JText::_('COM_CROWDFUNDING_FIELD_FUNDING_DURATION'); ?>
        </label>
    </div>
    <div class="controls">
        <?php
        if (empty($this->fundingDuration) or strcmp('days', $this->fundingDuration) === 0) { ?>
            <input type="radio" value="days" name="jform[funding_duration_type]" id="js-funding-duration-days" <?php echo $this->checkedDays; ?> />
            <?php echo $this->form->getLabel('funding_days'); ?>
            <div class="clearfix"></div>
            <?php echo $this->form->getInput('funding_days'); ?>
        <?php
        } ?>

        <br/><br/><br/>

        <?php
        if (empty($this->fundingDuration) or strcmp('date', $this->fundingDuration) === 0) { ?>
            <div class="clearfix"></div>
            <input type="radio" value="date" name="jform[funding_duration_type]" id="js-funding-duration-date" <?php echo $this->checkedDate; ?> />
            <?php echo $this->form->getLabel('funding_end'); ?>
            <div class="clearfix"></div>
            <?php echo $this->form->getInput('funding_end'); ?>
        <?php
        } ?>
    </div>
</div>