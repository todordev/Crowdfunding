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
<div class="panel panel-reward js-cfreward-panel" id="reward_tmpl" style="display: none;">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-11 text-left">
                <div class="dropdown pull-left">
                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
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
                <button type="button" class="btn btn-warning btn-xs" title="<?php echo JText::_('COM_CROWDFUNDING_TOOLTIP_NOT_SAVED_REWARD'); ?>" id="js-cfreward-unsaved-tt-d">
                    <span class="fa fa-warning"></span>
                </button>
                <?php echo JText::_('COM_CROWDFUNDING_REWARDS_REWARD'); ?>
            </div>
            <div class="col-md-1 text-right">
                <?php if (!$this->debugMode) {?>
                    <button type="button" class="btn btn-xs btn-danger js-btn-remove-reward" title="<?php echo JText::_('COM_CROWDFUNDING_REMOVE_REWARD')?>" id="reward_remove_d">
                        <span class="fa fa-times"></span>
                    </button>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="reward_amount_d" id="reward_amount_label_d"><?php echo JText::_('COM_CROWDFUNDING_AMOUNT'); ?><span class="star">&nbsp;*</span></label>
                    <div class="input-group">
                        <?php if ($this->currency->getSymbol()){?>
                        <div class="input-group-addon"><?php echo $this->currency->getSymbol();?></div>
                        <?php }?>
                        <input name="rewards[][amount]" id="reward_amount_d" type="text" value=""  class="form-control"/>
                        <div class="input-group-addon"><?php echo $this->currency->getCode();?></div>
                    </div>
                </div>

                <div class="form-group">
                <label for="reward_title_d" id="reward_title_title_d"><?php echo JText::_('COM_CROWDFUNDING_TITLE'); ?><span class="star">&nbsp;*</span></label>
                <input name="rewards[][title]" id="reward_title_d" type="text" value="" class="form-control" />
                </div>

                <div class="form-group">
                <label for="reward_description_d" id="reward_description_title_d"><?php echo JText::_('COM_CROWDFUNDING_DESCRIPTION'); ?><span class="star">&nbsp;*</span></label>
                <textarea name="rewards[][description]" id="reward_description_d" rows="6" class="form-control"></textarea>
                </div>

                <div class="form-group">
                <label for="reward_number_d" id="reward_number_title_d"><?php echo JText::_('COM_CROWDFUNDING_AVAILABLE');?></label>
                <input name="rewards[][number]" id="reward_number_d" type="text" value="" />
                </div>

                <div class="form-group">
                    <label for="reward_delivery_d" id="reward_delivery_title_d"><?php echo JText::_('COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY');?></label>
                    <?php echo JHtml::_('prism.ui.calendar', '', 'rewards[][delivery]', 'reward_delivery_d', $this->dateFormatCalendar, array('class' => 'form-control'));?>
                </div>

                <input name="rewards[][id]" type="hidden" value="" id="reward_id_d" class="js-cfreward-reward-id"/>
                <input name="rewards[][ordering]" type="hidden" value="" class="js-cfreward-ordering"/>
                <input name="cfreward_index_id" type="hidden" value="" class="js-cfreward-index-id" disabled="disabled"/>
            </div>
        </div>
    </div>
</div>
