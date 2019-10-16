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
?>
<div class="cfmdetails<?php echo $moduleclassSfx; ?>">
    <div class="thumbnail cf-project">
        <img src="<?php echo $imageFolder . '/' . $project->getImage(); ?>" alt="<?php echo htmlspecialchars($project->getTitle(), ENT_QUOTES, 'UTF-8'); ?>" width="<?php echo $imageWidth; ?>" height="<?php echo $imageHeight; ?>" />
        <div class="caption">
            <h3>
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())); ?>">
                    <?php echo htmlspecialchars(JHtmlString::truncate($project->getTitle(), $params->get('title_length', $componentParams->get('title_length')), true, false), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h3>
            <span class="font-xxsmall">
            <?php
            if ($params->get('show_author', $componentParams->get('show_author', Prism\Constants::DO_NOT_DISPLAY))) {
                echo JText::_('MOD_CROWDFUNDINGDETAILS_BY');
                if (!empty($socialProfileLink)) {?>
                <a href="<?php echo $socialProfileLink; ?>"><?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php } else { ?>
                <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
                <?php } ?>
            <?php } ?>
            </span>

            <?php if ($params->get('show_description', $componentParams->get('show_description', Prism\Constants::DO_NOT_DISPLAY))) {?>
            <p><?php echo htmlspecialchars(JHtmlString::truncate($project->getShortDesc(), $params->get('description_length', $componentParams->get('description_length')), true, false), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>
        </div>

        <div class="cf-caption-info absolute-bottom">
            <?php echo JHtml::_('crowdfunding.progressbar', $fundedPercents, $project->getDaysLeft(), $project->getFundingType(), false, $project->getFundingStart()); ?>
            <div class="row">
                <div class="col-sm-4 hidden-xs">
                    <div class="bolder"><?php echo $project->getFundedPercent(); ?>%</div>
                    <div class="text-uppercase"><?php echo JText::_('MOD_CROWDFUNDINGDETAILS_FUNDED'); ?></div>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <div class="bolder"><?php echo $raised; ?></strong></div>
                    <div class="text-uppercase"><?php echo JText::_('MOD_CROWDFUNDINGDETAILS_RAISED'); ?></div>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <div class="bolder"><?php echo $project->getDaysLeft(); ?></strong></div>
                    <div class="text-uppercase"><?php echo JText::_('MOD_CROWDFUNDINGDETAILS_DAYS_LEFT'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>