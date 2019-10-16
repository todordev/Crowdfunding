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
 * @var $item \Crowdfunding\Project\Project
 */

$item   = $displayData->item;

$goal   = $displayData->moneyFormatter->formatCurrency(new Prism\Money\Money($item->getGoal(), $displayData->currency));
$funded = $displayData->moneyFormatter->formatCurrency(new Prism\Money\Money($item->getFunded(), $displayData->currency));
$fundedPercent = Crowdfunding\Utilities\MathHelper::calculatePercent($item->getGoal(), $item->getFunded());

$category = $item->getCategory();
$image    = $item->getImageSmall() ? $displayData->mediaFolder .'/'.$item->getImageSmall() : 'media/com_crowdfunding/images/no_image.png';
?>
<div class="media project-details">
    <div class="media-left media-middle">
        <a href="#">
            <img class="media-object" src="<?php echo $image;?>" width="<?php echo $displayData->imageWidth;?>" height="<?php echo $displayData->imageHeight;?>"/>
        </a>
    </div>
    <div class="media-body">
        <h4 class="media-heading">
            <?php echo JHtml::_('crowdfunding.projectTitle', $item->getTitle(), $item->getCategory()->getState(), $item->getSlug(), $item->getCatSlug()); ?>
        </h4>
        <?php echo htmlspecialchars($item->getShortDesc());?>
    </div>
    <div class="media-tools">
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-ellipsis-h"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <?php if ($item->isPublished()) {?>
                        <a class="label-danger" href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=projects.savestate&id='.(int)$item->getId().'&state=0&'.JSession::getFormToken().'=1'); ?>">
                            <span class="fa fa-ban"></span>
                            <?php echo JText::_('COM_CROWDFUNDING_STOP');?>
                        </a>
                    <?php } else { ?>
                        <a class="label-success" href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=projects.savestate&id='.(int)$item->getId().'&state=1&'.JSession::getFormToken().'=1'); ?>">
                            <spam class="fa fa-check-circle-o"></spam>
                            <?php echo JText::_('COM_CROWDFUNDING_LAUNCH');?>
                        </a>
                    <?php } ?>

                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($item->getId())); ?>">
                        <span class="fa fa-edit"></span>
                        <?php echo JText::_('COM_CROWDFUNDING_EDIT');?>
                    </a>

                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($item->getId(), 'manager')); ?>">
                        <span class="fa fa-wrench"></span>
                        <?php echo JText::_('COM_CROWDFUNDING_MANAGER');?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="media-footer mf-color1">
        <div class="row">
            <div class="col-md-4">
                <?php echo JText::sprintf('COM_CROWDFUNDING_START_', JHtml::_('Prism.ui.date', $item->getFundingStart(), $displayData->dateFormat));?>
            </div>
            <div class="col-md-3">
                <?php echo JText::sprintf('COM_CROWDFUNDING_GOAL_', $goal);?>
            </div>
            <div class="col-md-3">
                <?php echo JText::sprintf('COM_CROWDFUNDING_FUNDED_', $fundedPercent.'%');?>
            </div>
            <div class="col-md-2">
                <?php echo JHtml::_('crowdfunding.unitState', $item->isPublished(), ['hint' => true, 'title_true' => JText::_('COM_CROWDFUNDING_LAUNCHED'), 'title_false' => JText::_('COM_CROWDFUNDING_NOTLAUNCHED') ]); ?>
                <?php echo JHtml::_('crowdfunding.unitState', $item->isApproved(), ['hint' => true, 'title_true' => JText::_('COM_CROWDFUNDING_APPROVED'), 'title_false' => JText::_('COM_CROWDFUNDING_NOTAPPROVED') ]); ?>
            </div>

        </div>
    </div>
    <div class="media-footer mf-color2">
        <div class="row">
            <div class="col-md-4">
                <?php echo JText::sprintf('COM_CROWDFUNDING_DURATION_', JHtml::_('crowdfunding.duration2', $item->getFundingEnd(), $item->getFundingDays(), $displayData->dateFormat)); ?>
            </div>
            <div class="col-md-3">
                <?php echo JText::sprintf('COM_CROWDFUNDING_RAISED_', $funded);?>
            </div>
            <div class="col-md-3">
                <?php echo JText::sprintf('COM_CROWDFUNDING_DAYS_LEFT_', $item->getDaysLeft());?>
            </div>
            <div class="col-md-2">
                <?php echo JText::sprintf('COM_CROWDFUNDING_HITS_', $item->getHits());?>
            </div>
        </div>
    </div>
</div>