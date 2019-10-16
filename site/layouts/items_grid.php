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

$items    = $displayData->items;
$params   = $displayData->params;
/** @var Joomla\Registry\Registry $params */

$socialProfiles = $displayData->socialProfiles;
/** @var Prism\Integration\Profiles\ProfilesInterface $socialProfiles */

$moneyFormatter   = $displayData->moneyFormatter;
/** @var Prism\Money\Formatter $moneyFormatter */

$currency   = $displayData->currency;
/** @var Prism\Money\Currency $currency */

$numberInRow        = (int)$params->get('items_row', 3);
$span               = ($numberInRow > 0) ? round(12 / $numberInRow) : 4
?>
<div class="row">
    <?php
    foreach ($items as $item) {
        $titleLength        = $item->params->get('title_length', $params->get('title_length'));
        $descriptionLength  = $item->params->get('description_length', $params->get('description_length'));
        $showAuthor         = (bool)$item->params->get('show_author', $params->get('show_author'));
        $projectStateCSS    = JHtml::_('crowdfunding.styles', $item, $params);

        $raised      = $moneyFormatter->formatCurrency(new Prism\Money\Money($item->funded, $currency));

        // Prepare the value that I am going to display
        $fundedPercents = JHtml::_('crowdfunding.funded', $item->funded_percents);

        // Prepare social profile.
        $profileName = '';
        if ($socialProfiles !== null) {
            $socialProfile = $socialProfiles->getLink($item->user_id);
            $profileName   = JHtml::_('crowdfunding.socialProfileLink', $socialProfile, $item->user_name);
        }
        ?>
    <div class="col-md-<?php echo $span; ?>">
        <div class="thumbnail cf-project <?php echo $projectStateCSS; ?> ">
            <?php if ((bool)$params->get('discover_include_badge_element', 0)) {?><div class="cf-badge"></div><?php } ?>
            <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>" class="cf-thumnails-thumb">
                <?php if (!$item->image) { ?>
                    <img src="<?php echo 'media/com_crowdfunding/images/no_image.png'; ?>"
                         alt="<?php echo $this->escape($item->title); ?>" width="<?php echo $params->get('image_width', 200); ?>"
                         height="<?php echo $params->get('image_height', 200); ?>" />
                <?php } else { ?>
                    <img src="<?php echo $displayData->imageFolder . '/' . $item->image; ?>"
                         alt="<?php echo $this->escape($item->title); ?>" width="<?php echo $params->get('image_width', 200); ?>"
                         height="<?php echo $params->get('image_height', 200); ?>" />
                <?php } ?>
            </a>

            <div class="caption">
                <h3>
                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>">
                        <?php echo JHtmlString::truncate($item->title, $titleLength, true, false); ?>
                    </a>
                </h3>
                <?php if ($showAuthor and $socialProfiles !== null) { ?>
                    <div class="font-xxsmall">
                        <?php echo JText::sprintf('COM_CROWDFUNDING_BY_S', $profileName); ?>
                    </div>
                <?php } ?>

                <?php if ((bool)$item->params->get('show_description', $params->get('show_description'))) { ?>
                    <p><?php echo JHtmlString::truncate($item->short_desc, $descriptionLength, true, false); ?></p>
                <?php } ?>
            </div>
            <div class="cf-caption-info absolute-bottom">
                <?php echo JHtml::_('crowdfunding.progressbar', $fundedPercents, $item->days_left, $item->funding_type, false, $item->funding_start); ?>

                <div class="row">
                    <div class="col-sm-4 hidden-xs">
                        <div class="bolder"><?php echo $item->funded_percents; ?>%</div>
                        <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_FUNDED'); ?></div>
                    </div>
                    <div class="col-sm-4 col-xs-6">
                        <div class="bolder"><?php echo $raised; ?></div>
                        <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_RAISED'); ?></div>
                    </div>
                    <div class="col-sm-4 col-xs-6">
                        <div class="bolder"><?php echo $item->days_left; ?></div>
                        <div class="text-uppercase"><?php echo JText::_('COM_CROWDFUNDING_DAYS_LEFT'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>