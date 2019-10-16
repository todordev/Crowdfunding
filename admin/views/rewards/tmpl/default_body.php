<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>

<?php foreach ($this->items as $i => $item) {
    $dateValidator = new Prism\Validator\Date($item->delivery);

    $ordering = ($this->listOrder === 'a.ordering');

    $disableClassName = '';
    $disabledLabel    = '';
    if (!$this->saveOrder) {
        $disabledLabel    = JText::_('JORDERINGDISABLED');
        $disableClassName = 'inactive tip-top';
    }
    ?>
    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->project_id ?>">
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
            <?php echo JHtml::_('jgrid.published', $item->published, $i, 'rewards.'); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=reward&layout=edit&id=' . (int)$item->id); ?>"><?php echo $this->escape($item->title); ?></a>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=reward&id=' . $item->id); ?>" class="btn btn-mini hasTooltip" title="<?php echo JText::_('COM_CROWDFUNDING_ADDITIONAL_INFORMATION'); ?>">
                <i class="icon icon-eye"></i>
            </a>
        </td>
        <td class="center"><?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($item->amount, $this->currency)); ?></td>
        <td class="center hidden-phone"><?php echo $item->number; ?></td>
        <td class="center hidden-phone"><?php echo $item->distributed; ?></td>
        <td class="center hidden-phone"><?php echo $item->number - $item->distributed; ?></td>
        <td class="hidden-phone">
            <?php echo $dateValidator->isValid() ? JHtml::_('date', $item->delivery, JText::_('DATE_FORMAT_LC3')) : '--'; ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>