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

/**
 * @var stdClass $displayData
 * @var array $steps
 * @var array $wizardSteps
 */

$steps = array();
$steps['default'] = false;

// Prepare custom steps.
$wizardSteps = $displayData->wizard_steps;
foreach ($wizardSteps as $step) {
    $steps[$step['layout']] = false;
}

$steps['payment'] = false;
$steps['share']   = false;

// Prepare active steps.
foreach ($steps as $step => $value) {
    if (strcmp($displayData->layout, $step) === 0) {
        $steps[$step] = true;
        break;
    }
}
?>
<div class="navbar navbar-default cf-backing-navigation" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="javascript:void(0);"><?php echo JText::_('COM_CROWDFUNDING_PAYMENT_PROCESS'); ?></a>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li <?php echo $steps['default'] ? 'class="active"' : ''; ?>>
                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug)); ?>">
                        (1) <?php echo (!$displayData->rewards_enabled) ? JText::_('COM_CROWDFUNDING_STEP_PLEDGE') : JText::_('COM_CROWDFUNDING_STEP_PLEDGE_REWARDS'); ?>
                    </a>
                </li>

                <?php
                $i = 2;
                foreach ($wizardSteps as $step) {
                    $isActive = array_key_exists('is_active', $step) ? (bool)$step['is_active'] : false;
                    $url =  $isActive ? JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, $step['layout'])) : 'javascript: void(0);';
                ?>
                <li <?php echo $steps[$step['layout']] ? 'class="active"' : ''; ?>>
                    <a href="<?php echo $url; ?>">
                        (<?php echo $i; ?>) <?php echo htmlspecialchars($step['title'], ENT_COMPAT, 'UTF-8'); ?>
                    </a>
                </li>
                <?php $i++;
                } ?>

                <li <?php echo $steps['payment'] ? 'class="active"' : ''; ?>>
                    <?php if ((bool)$displayData->paymentSession->step1 === true) { ?>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, 'payment')); ?>">
                            (<?php echo $i++; ?>) <?php echo JText::_('COM_CROWDFUNDING_STEP_PAY'); ?>
                        </a>
                    <?php } else { ?>
                        <a href="javascript: void(0);" class="disabled">(<?php echo $i++; ?>) <?php echo JText::_('COM_CROWDFUNDING_STEP_PAY'); ?></a>
                    <?php } ?>
                </li>

                <li <?php echo $steps['share'] ? 'class="active"' : ''; ?>>
                    <?php if ((bool)$displayData->paymentSession->step2 === true) { ?>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, 'share')); ?>">
                            (<?php echo $i++; ?>) <?php echo JText::_('COM_CROWDFUNDING_STEP_SHARE'); ?>
                        </a>
                    <?php } else { ?>
                        <a href="javascript: void(0);" class="disabled">(<?php echo $i++; ?>) <?php echo JText::_('COM_CROWDFUNDING_STEP_SHARE'); ?></a>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</div>
