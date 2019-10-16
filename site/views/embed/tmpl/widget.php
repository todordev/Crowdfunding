<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>
<?php if (is_object($this->item)) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="thumbnail cf-project">
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>" class="cf-thumnails-thumb" target="_blank">
                    <img src="<?php echo $this->item->link_image; ?>" alt="<?php echo $this->escape($this->item->title); ?>"
                         width="<?php echo $this->params->get('image_width'); ?>"
                         height="<?php echo $this->params->get('image_height'); ?>">
                </a>

                <div class="caption">
                    <h3>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>" target="_blank">
                            <?php echo $this->escape($this->item->title); ?>
                        </a>
                    </h3>
                    <?php if ($this->socialProfileLink !== null) { ?>
                        <div class="font-xxsmall">
                            <?php echo JText::sprintf('COM_CROWDFUNDING_BY_S', JHtml::_('crowdfunding.socialProfileLink', $this->socialProfileLink, $this->item->user_name, array('target' => '_blank'))); ?>
                        </div>
                    <?php } ?>

                    <p><?php echo JHtmlString::truncate($this->item->short_desc, $this->params->get('discover_description_length', 255), true, false); ?></p>

                    <?php echo JHtml::_('crowdfunding.progressbar', $this->item->funded_percents, $this->item->days_left, $this->item->funding_type, false, $this->item->funding_start); ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->item->funded_percents; ?>%</div>
                            <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_FUNDED'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($this->item->funded, $this->currency)); ?></div>
                            <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_RAISED'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->item->days_left; ?></div>
                            <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_DAYS_LEFT'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
    