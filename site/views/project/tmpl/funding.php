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
    <div class="col-md-12">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="fundingForm" id="js-cf-funding-form" novalidate="novalidate" autocomplete="off" >

            <div class="row">
                <div class="col-md-2"><?php echo $this->form->getLabel('goal'); ?></div>
                <div class="col-md-10">
                    <?php echo $this->form->getInput('goal'); ?>
                    <span class="help-block"><?php echo JHtml::_('crowdfunding.infoMinMaxAmount', $this->minAmount, $this->maxAmount, $this->moneyFormatter, $this->currency); ?></span>
                </div>
            </div>

            <div class="row mt-20 bt-dashed-thin pt-20">
                <div class="col-md-2"><?php echo $this->form->getLabel('funding_type'); ?></div>
                <div class="col-md-10">
                    <?php echo $this->form->getInput('funding_type'); ?>
                </div>
            </div>
        
        	<div class="row mt-20 bt-dashed-thin pt-20">
                <div class="col-md-2">
                	<label <?php echo JHtml::_('Prism.ui.popoverText', JText::_('COM_CROWDFUNDING_FIELD_FUNDING_DURATION_DESC'),JText::_('COM_CROWDFUNDING_FIELD_FUNDING_DURATION'));?> class="hasPopover" for="jform_funding_duration_type" id="jform_funding_duration_type-lbl">
                	<?php echo JText::_('COM_CROWDFUNDING_FIELD_FUNDING_DURATION');?><span class="star">&nbsp;*</span>
                	</label>
                </div>
                
                <div class="col-md-10">
                    <?php if (!$this->fundingDuration or (strcmp('days', $this->fundingDuration) === 0)) {?>
                        <input type="radio" value="days" name="jform[funding_duration_type]" id="js-funding-duration-days" <?php echo $this->checkedDays;?>/>
                        <?php echo $this->form->getLabel('funding_days'); ?>
                        <?php echo $this->form->getInput('funding_days'); ?>
                        <?php if(!empty($this->maxDays)) {?>
                        <span class="help-block"><?php echo JText::sprintf('COM_CROWDFUNDING_MINIMUM_MAXIMUM_DAYS', $this->minDays, $this->maxDays);?></span>
                        <?php } else {?>
                        <span class="help-block"><?php echo JText::sprintf('COM_CROWDFUNDING_MINIMUM_DAYS', $this->minDays);?></span>
                        <?php }?>
        			<?php }?>
        			
        			<?php if(!$this->fundingDuration or (strcmp('date', $this->fundingDuration) === 0)) {?>
            			<input type="radio" value="date" name="jform[funding_duration_type]" id="js-funding-duration-date" <?php echo $this->checkedDate;?> />
                        <?php echo $this->form->getLabel('funding_end'); ?>
                        <?php echo $this->form->getInput('funding_end'); ?>
                    <?php } ?>

                    <?php if ($this->isValidStartingDate) {?>
                    <p class="alert alert-info mt-20">
                        <span class="fa fa-info-circle"></span>
                        <?php echo JText::sprintf('COM_CROWDFUNDING_CAMPAIGN_STARTING_DATE_S', JHtml::_('date', $this->item->funding_start, $this->dateFormat)); ?>
                    </p>
                    <?php } ?>
                </div>
            </div>
            
            <?php echo $this->form->getInput('id'); ?>
            <input type="hidden" name="task" value="funding.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <button type="submit" class="btn btn-primary mtb-15-0" <?php echo $this->disabledButton;?>>
                <span class="fa fa-check"></span>
                <?php echo JText::_('COM_CROWDFUNDING_SAVE_AND_CONTINUE'); ?>
            </button>
        </form>
    </div>
</div>