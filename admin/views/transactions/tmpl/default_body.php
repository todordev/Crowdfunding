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

/**
 * @var stdClass $item
 */
?>
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder === 'a.ordering');

    $rewardOptions = array(
        'transaction_id' => $item->id,
        'reward_id' => $item->reward_id,
        'reward_title' => $item->reward,
        'reward_state' => $item->reward_state,
        'project_id' => $item->project_id,
        'class' => 'width-100px'
    );

    $params = new Joomla\Registry\Registry();
    $params->loadString($item->params);
    ?>
    <tr class="row<?php echo $i % 2; ?> <?php echo JHtml::_('crowdfundingbackend.transactionColor', $item->txn_status); ?>" id="txn-row-<?php echo $item->id; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transaction&layout=edit&id=' . $item->id); ?>">
                <?php echo $this->escape($item->txn_id); ?>
            </a>
            <?php echo JHtml::_('crowdfundingbackend.capturedPeriod', $params); ?>

            <div class="small hidden-phone">
                <?php echo JText::sprintf('COM_CROWDFUNDING_PROJECT_S_S', JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=pid:' . $item->project_id), $this->escape($item->project)); ?>
            </div>
            <?php if (!empty($item->parent_txn_id)) { ?>
                <div class="small hidden-phone">
                    <?php echo JText::sprintf('COM_CROWDFUNDING_PARENT_TRANSACTION_ID_S', $this->escape($item->parent_txn_id)); ?>
                </div>
            <?php } ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfundingbackend.name', $item->sender, $item->investor_id); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfundingbackend.name', $item->beneficiary, $item->receiver_id); ?>
        </td>
        <td><?php echo JHtml::_('crowdfundingbackend.transactionAmount', $item, $this->moneyFormatter, $this->currencies); ?></td>
        <td class="hidden-phone"><?php echo $item->txn_date; ?></td>
        <td class="hidden-phone"><?php echo $item->service_provider; ?></td>
        <td class="hidden-phone">
            <?php echo JHtml::_('select.genericlist', $this->paymentStatuses, 'txn_status', array('class' => 'js-txn-status', 'data-id' => $item->id), 'value', 'text', $item->txn_status); ?>
            <?php echo JHtml::_('crowdfundingbackend.reason', $item->status_reason); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('crowdfundingbackend.reward', $rewardOptions); ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>