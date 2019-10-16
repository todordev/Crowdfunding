<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="panel panel-default mt-10">
    <div class="panel-heading"><?php echo JText::_('COM_CROWDFUNDING_CONTRIBUTORS'); ?></div>
    <div class="panel-body">
        <?php if (count($this->items) === 0) {?>
            <?php echo JText::sprintf('COM_CROWDFUNDING_NOT_CONTRIBUTORS_BE_FIRST_S', JRoute::_(CrowdfundingHelperRoute::getBackingRoute($this->item->id, $this->item->catid))); ?>
        <?php } ?>
<?php
foreach ($this->items as $item) {
    $socialProfile = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->id);

    $socialAvatar  = (!$this->socialProfiles) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($item->id, $this->avatarsSize);
    if (!$socialAvatar) {
        $socialAvatar = $this->defaultAvatar;
    }

    $socialLocation  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLocation($item->id);
    $socialCountryCode  = (!$this->socialProfiles) ? null: $this->socialProfiles->getCountryCode($item->id);
    ?>
    <div class="cf-funder-row">
        <div class="media">
            <div class="media-left">
                <a class="cf-funder-picture" href="<?php echo (!$socialProfile) ? 'javascript: void(0);' : $socialProfile; ?>">
                    <img class="media-object" src="<?php echo $socialAvatar; ?>" />
                </a>
            </div>

            <div class="media-body">
                <div class="pull-left cf-funder-info">
                    <h5 class="media-heading">
                        <?php if (!empty($socialProfile)) { ?>
                            <a href="<?php echo $socialProfile; ?>">
                                <?php echo $this->escape($item->name); ?>
                            </a>
                        <?php } else { ?>
                            <?php echo (!$item->name) ? JText::_('COM_CROWDFUNDING_ANONYMOUS') : $this->escape($item->name); ?>
                        <?php } ?>
                    </h5>
                    <?php echo JHtml::_('crowdfunding.profileLocation', $socialLocation, $socialCountryCode); ?>
                </div>

                <?php if ($this->displayAmounts) { ?>
                <div class="pull-right cf-funder-amount">
                    <?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($item->txn_amount, $this->currency)); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>
    </div>
</div>
