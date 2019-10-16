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

// Prepare availability number
$availability = Joomla\Utilities\ArrayHelper::getValue($this->formItem, 'number', 0, 'int');
if (!$availability) {
    $availability = '';
}

// Prepare delivery date
$deliveryDate = Joomla\Utilities\ArrayHelper::getValue($this->formItem, 'delivery');
if ((int)$deliveryDate > 0) {
    $dateValidator = new Prism\Validator\Date($deliveryDate);

    if (!$dateValidator->isValid()) {
        $deliveryDate = '';
    }
}
?>
<div class="panel panel-reward js-cfreward-panel" id="reward_box_<?php echo $this->formIndex;?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-11">
                <div class="dropdown pull-left">
                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" id="js-reward-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <span class="fa fa-caret-down"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li>
                            <a href="javascript: void(0);" class="js-cfreward-move-up cf-hover-element" role="button" data-action="up">
                                <span class="fa fa-long-arrow-up"></span> <?php echo JText::_('COM_CROWDFUNDING_MOVE_UP')?>
                            </a>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="js-cfreward-move-down cf-hover-element" role="button" data-action="down">
                                <span class="fa fa-long-arrow-down"></span> <?php echo JText::_('COM_CROWDFUNDING_MOVE_DOWN')?>
                            </a>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="js-cfreward-copy cf-hover-element" role="button" data-action="copy">
                                <span class="fa fa-clone"></span> <?php echo JText::_('COM_CROWDFUNDING_COPY')?>
                            </a>
                        </li>
                    </ul>
                </div>&nbsp;
                <button type="button" class="btn btn-warning btn-xs js-cfreward-unsaved-tt" title="<?php echo JText::_('COM_CROWDFUNDING_TOOLTIP_NOT_SAVED_REWARD'); ?>" style="display: none;" id="js-cfreward-unsaved-tt<?php echo $this->formIndex;?>">
                    <span class="fa fa-warning"></span>
                </button>
                <?php echo JText::_('COM_CROWDFUNDING_REWARDS_REWARD'); ?>
            </div>

            <div class="col-md-1 text-right">
                <?php if (!$this->debugMode) {?>
                    <button class="btn btn-danger btn-xs js-btn-remove-reward" title="<?php echo JText::_('COM_CROWDFUNDING_REMOVE_REWARD')?>">
                        <span class="fa fa-times"></span>
                    </button>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-<?php echo $this->options['column_left'];?>">
                <div class="form-group">
                    <label class="hasTooltip" data-placement="left" for="reward_amount_<?php echo $this->formIndex;?>" title="<?php echo JText::_('COM_CROWDFUNDING_REWARDS_AMOUNT_DESC');?>"><?php echo JText::_('COM_CROWDFUNDING_AMOUNT'); ?><span class="star">&nbsp;*</span></label>
                    <div class="input-group">
                        <?php if ($this->currency->getSymbol()){?>
                        <div class="input-group-addon"><?php echo $this->currency->getSymbol();?></div>
                        <?php }?>
                        <input name="rewards[<?php echo $this->formIndex;?>][amount]" id="reward_amount_<?php echo $this->formIndex;?>" type="text" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  'amount')?>" class="form-control" />
                        <div class="input-group-addon"><?php echo $this->currency->getCode();?></div>
                    </div>
                </div>

                <div class="form-group">
                <label class="hasTooltip" data-placement="left" for="reward_title_<?php echo $this->formIndex;?>" title="<?php echo JText::_('COM_CROWDFUNDING_REWARDS_TITLE_DESC');?>"><?php echo JText::_('COM_CROWDFUNDING_TITLE'); ?><span class="star">&nbsp;*</span></label>
                <input name="rewards[<?php echo $this->formIndex;?>][title]" id="reward_title_<?php echo $this->formIndex;?>" type="text" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "title")?>" class="form-control" />
                </div>

                <div class="form-group">
                <label class="hasTooltip" data-placement="left" for="reward_description_<?php echo $this->formIndex;?>" title="<?php echo JText::_('COM_CROWDFUNDING_REWARDS_DESCRIPTION_DESC');?>"><?php echo JText::_('COM_CROWDFUNDING_DESCRIPTION'); ?><span class="star">&nbsp;*</span></label>
                <textarea name="rewards[<?php echo $this->formIndex;?>][description]" id="reward_description_<?php echo $this->formIndex;?>" rows="6" class="form-control"><?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  'description')?></textarea>
                </div>

                <div class="form-group">
                <label class="hasTooltip" data-placement="left" for="reward_number_<?php echo $this->formIndex;?>" title="<?php echo JText::_('COM_CROWDFUNDING_REWARDS_AVAIABLE_DESC');?>"><?php echo JText::_('COM_CROWDFUNDING_AVAILABLE'); ?></label>
                <input name="rewards[<?php echo $this->formIndex;?>][number]" id="reward_number_<?php echo $this->formIndex;?>" type="text" value="<?php echo $availability; ?>" />
                </div>

                <div class="form-group">
                <label class="hasTooltip" data-placement="left" for="reward_delivery_<?php echo $this->formIndex;?>" title="<?php echo JText::_('COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY_DESC');?>"><?php echo JText::_('COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY');?></label>
                <?php echo JHtml::_('Prism.ui.calendar', $deliveryDate, "rewards[".$this->formIndex."][delivery]", "reward_delivery_".$this->formIndex, $this->dateFormatCalendar, array('class' => 'form-control'));?>
                </div>

                <input name="rewards[<?php echo $this->formIndex;?>][id]" type="hidden" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  'id', 0)?>" class="js-cfreward-reward-id" />
                <input name="rewards[<?php echo $this->formIndex;?>][ordering]" type="hidden" value="<?php echo $this->formIndex;?>" class="js-cfreward-ordering"/>
                <input name="cfreward_index_id" type="hidden" value="<?php echo $this->formIndex;?>" class="js-cfreward-index-id" disabled="disabled"/>
            </div>

            <?php if ($this->options['column_right'] > 0) { ?>
            <div class="col-md-<?php echo $this->options['column_right'];?>">
                <?php if($this->rewardsImagesEnabled and count($this->formItem) > 0) {
                    echo $this->loadTemplate('image');
                } ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
