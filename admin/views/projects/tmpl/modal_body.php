<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$appSite    = JApplicationCms::getInstance('site');
$routerSite = $appSite->getRouter('site');

$uri        = JUri::getInstance();
$website    = $uri->toString(array('scheme', 'host'));
?>
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder === 'a.ordering');
    $numberOfRewards = (isset($this->rewards[$item->id])) ? $this->rewards[$item->id]['number'] : 0;
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="has-context">
            <a href="javascript: void(0);" data-id="<?php echo $item->id; ?>" data-title="<?php echo $this->escape($item->title); ?>" class="js-project-element">
                <?php echo $this->escape($item->title); ?>
            </a>&nbsp;&nbsp;
            <a href="<?php echo JHtml::_('crowdfundingbackend.siteRoute', $item, $website, $routerSite); ?>" target="_blank">
                <i class="icon-link"></i>
            </a>
            <div class="small">
                <?php echo JText::sprintf('COM_CROWDFUNDING_REWARDS_N', $numberOfRewards); ?><br />
                <?php echo JText::sprintf('COM_CROWDFUNDING_CATEGORY_S', $item->category); ?>
            </div>
        </td>
        <td class="hidden-phone"><?php echo $this->moneyFormatter->formatCurrency(); ?></td>
        <td class="hidden-phone"><?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($item->funded, $this->currencu)); ?></td>
        <td class="hidden-phone"><?php echo JHtml::_('crowdfunding.percent', $item->funded_percents); ?></td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfunding.date', $item->funding_start, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfunding.duration', $item->funding_end, $item->funding_days, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="hidden-phone"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></td>
        <td class="hidden-phone"><?php echo $this->escape($item->type); ?></td>
        <td>
            <?php echo $this->escape($item->username); ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>