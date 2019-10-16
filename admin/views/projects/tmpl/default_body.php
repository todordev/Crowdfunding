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

    $disableClassName = '';
    $disabledLabel    = '';
    if (!$this->saveOrder) {
        $disabledLabel    = JText::_('JORDERINGDISABLED');
        $disableClassName = 'inactive tip-top';
    }

    $numberOfRewards = array_key_exists($item->id, $this->rewards) ? $this->rewards[$item->id]['number'] : 0;

    $moneyGoal   = new Prism\Money\Money($item->goal, $this->currency);
    $moneyFunded = new Prism\Money\Money($item->funded, $this->currency);
    ?>
    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid ?>">
        <td class="order nowrap center hidden-phone">
    		<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
    			<i class="icon-menu"></i>
    		</span>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <div  class="btn-group">
            <?php echo JHtml::_('crowdfundingbackend.published', $i, $item->published, 'projects.'); ?>
            <?php echo JHtml::_('crowdfundingbackend.featured', $i, $item->featured); ?>
            <?php echo JHtml::_('crowdfundingbackend.approved', $i, $item->approved, 'projects.'); ?>
            </div>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=project&layout=edit&id=' . $item->id); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>&nbsp;&nbsp;
            <a href="<?php echo JHtml::_('crowdfundingbackend.siteRoute', $item, $website, $routerSite); ?>" target="_blank">
                <i class="icon-link"></i>
            </a>
            <div class="small">
                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=rewards&pid=' . $item->id); ?>">
                    <?php echo JText::sprintf('COM_CROWDFUNDING_REWARDS_N', $numberOfRewards); ?>
                </a>
                <?php echo JHtml::_('crowdfundingbackend.category', $item->category); ?>
                <?php echo JHtml::_('crowdfundingbackend.type', $item->type); ?>
            </div>
        </td>
        <td class="hidden-phone"><?php echo $this->moneyFormatter->formatCurrency($moneyGoal); ?></td>
        <td class="hidden-phone">
            <span title="<?php echo JHtml::_('crowdfunding.percent', $item->funded_percents); ?>" class="cursor-help">
            <?php echo $this->moneyFormatter->formatCurrency($moneyFunded); ?>
            </span>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfunding.date', $item->funding_start, $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'))); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfunding.duration', $item->funding_end, $item->funding_days, $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'))); ?>
        </td>
        <td class="hidden-phone"><?php echo JHtml::_('date', $item->created, $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'))); ?></td>
        <td class="hidden-phone">
            <?php echo $this->escape($item->access_level); ?></td>
        <td>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=users&filter_search=id:' . $item->user_id); ?>">
                <?php echo $this->escape($item->username); ?>
            </a>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>