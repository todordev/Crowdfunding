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
?>
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder === 'a.ordering');

    $numberOfProjects = array_key_exists($item->id, $this->projects) ? $this->projects[$item->id]->number : 0;

    $invested = Joomla\Utilities\ArrayHelper::getValue($this->amounts['invested'], $item->id);
    $received = Joomla\Utilities\ArrayHelper::getValue($this->amounts['received'], $item->id);

    $investedAmount    = 0;
    $investedTxnNumber = 0;
    if (!empty($invested)) {
        $investedAmount    = $invested->amount;
        $investedTxnNumber = $invested->number;
    }

    $receiverAmount    = 0;
    $receivedTxnNumber = 0;
    if (!empty($received)) {
        $receiverAmount    = $received->amount;
        $receivedTxnNumber = $received->number;
    }

    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=user&id=' . $item->id); ?>">
                <?php echo $this->escape($item->name); ?>
            </a>
            <div class="small">
                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=uid:' . $item->id); ?>">
                    <?php echo JText::sprintf('COM_CROWDFUNDING_PROJECTS_N', $numberOfProjects); ?>
                </a>
            </div>
        </td>
        <td class="hidden-phone">
        <?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($investedAmount, $this->currency)); ?>
        <div class="small">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=sid:' . $item->id); ?>">
                <?php echo JText::sprintf('COM_CROWDFUNDING_TRANSACTIONS_N', $investedTxnNumber); ?>
            </a>
        </div>
        </td>
        <td class="hidden-phone">
        <?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($receiverAmount, $this->currency)); ?>
        <div class="small">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=bid:' . $item->id); ?>">
                <?php echo JText::sprintf('COM_CROWDFUNDING_TRANSACTIONS_N', $receivedTxnNumber); ?>
            </a>
        </div>
        </td>
        <td class="center hidden-phone"><?php echo JHtml::_('date', $item->registerDate, JText::_('DATE_FORMAT_LC3')); ?></td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>