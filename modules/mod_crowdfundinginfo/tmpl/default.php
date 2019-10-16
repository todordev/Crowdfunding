<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */
 
// no direct access
defined('_JEXEC') or die;

/**
 * @var Prism\Money\Money $money
 * @var Crowdfunding\Project $project
 * @var Joomla\Registry\Registry $componentParams
 * @var string $fundedAmount
 */
?>
<div class="cfinfo<?php echo $moduleclassSfx; ?>">
    <div class="cfinfo-raised">
    	<?php echo $funded; ?>
    </div>
    <div class="cfinfo-raised-of">
        <?php echo JText::sprintf('MOD_CROWDFUNDINGINFO_RAISED_OF', $goal);?>
	</div>
    <?php echo JHtml::_('crowdfunding.progressbar', $project->getFundedPercent(), $project->getDaysLeft(), $project->getFundingType(), (bool)$params->get('show_percentage', false), $project->getFundingStart()); ?>
	<div class="row">
		<?php if ($params->get('show_days_left', 1)) { ?>
    	<div class="col-xs-6">
    		<div class="cfinfo-stats">
        		<img src="media/com_crowdfunding/images/clock.png" width="25" height="25" />
        		<?php echo $project->getDaysLeft();?>
    		</div>
    		<div class="fzmfwbu"><?php echo JText::_('MOD_CROWDFUNDINGINFO_DAYS_LEFT');?></div>
		</div>
		<?php } ?>
		<?php if ($params->get('show_funded', 1)) { ?>
		<div class="col-xs-6">
			<div class="cfinfo-stats">
    			<img src="media/com_crowdfunding/images/piggy-bank.png" width="27" height="20" />
        		<?php echo $project->getFundedPercent();?>%
    		</div>
    		<div class="fzmfwbu"><?php echo JText::_('MOD_CROWDFUNDINGINFO_FUNDED');?></div>
		</div>
		<?php } ?>

        <?php if ($params->get('show_backers', 0)) { ?>
            <div class="col-xs-6">
                <div class="cfinfo-stats">
                    <img src="media/com_crowdfunding/images/group.png" width="27" height="18" />
                    <?php echo $project->getBackers();?>
                </div>
                <div class="fzmfwbu"><?php echo JText::_('MOD_CROWDFUNDINGINFO_BACKERS');?></div>
            </div>
        <?php } ?>

        <?php if ((int)$params->get('show_funding_type', 0) === 2) { ?>
            <div class="col-xs-6">
                <div class="cfinfo-stats">
                    <img src="media/com_crowdfunding/images/gavel.png" width="23" height="23" />
                </div>
                <div class="fzmfwbu"><?php echo JText::_('MOD_CROWDFUNDINGINFO_'.strtoupper($project->getFundingType()));?></div>
            </div>
        <?php }?>

	</div>
	<?php if ((int)$params->get('show_funding_type', 0) === 1) { ?>
    <div class="cfinfo-funding-type">
        <?php echo JText::_('MOD_CROWDFUNDINGINFO_FUNDING_TYPE_'. strtoupper($project->getFundingType())); ?>
    </div>
	<?php }?>

	<?php if($isValidEndDate and $project->isCompleted()) {?>
	<div class="well">
		<div class="cf-fund-result-state pull-center"><?php echo JHtml::_('crowdfunding.resultState', $project->getFundedPercent(), $project->getFundingType());?></div>
		<div class="cf-frss pull-center"><?php echo JHtml::_('crowdfunding.resultStateText', $project->getFundedPercent(), $project->getFundingType());?></div>
	</div>
	<?php } else { ?>
	<div class="cfinfo-funding-action">
		<a class="btn btn-default btn-large btn-block <?php echo !$isValidEndDate ? 'disabled' : '';?>" href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug()));?>">
			<?php
			if (!$params->get('button_title_custom')) {
				echo JText::_($params->get('button_title', 'MOD_CROWDFUNDINGINFO_BUTTON_CONTRIBUTE'));
			} else {
				echo htmlspecialchars($params->get('button_title_custom'), ENT_COMPAT, 'UTF-8');
			}
			?>
        </a>
	</div>
	<?php }?>

	<?php if ((bool)$params->get('show_funding_info', true)) { ?>
    <div class="cfinfo-funding-type-info">
    	<?php
    	$endDate = JHtml::_('Prism.ui.date', $project->getFundingEnd(), $componentParams->get('date_format_views', JText::_('DATE_FORMAT_LC3')));

    	if ('FIXED' === $project->getFundingType()) {
    	    echo JText::sprintf('MOD_CROWDFUNDINGINFO_FUNDING_TYPE_INFO_FIXED', $goal, $endDate);
    	} else {
    	    echo JText::sprintf('MOD_CROWDFUNDINGINFO_FUNDING_TYPE_INFO_FLEXIBLE', $endDate);
    	}
    	?>
    </div>
	<?php }?>
</div>