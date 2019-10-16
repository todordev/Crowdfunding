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
<div class="panel panel-default">
    <div class="panel-heading"><?php echo JText::_('COM_CROWDFUNDING_BASIC_INFORMATION');?></div>
    <div class="panel-body">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th><?php echo JText::_('COM_CROWDFUNDING_PROJECTS');?></th>
                <td>
                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=uid:' . $this->item->id); ?>">
                        <?php echo (int)$this->projects; ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_CROWDFUNDING_INVESTED_AMOUNT');?></th>
                <td>
                    <?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($this->investedAmount, $this->currency)); ?>
                    <div class="small">
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=sid:' . $this->item->id); ?>">
                            <?php echo JText::sprintf('COM_CROWDFUNDING_TRANSACTIONS_N', $this->investedTransactions); ?>
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_CROWDFUNDING_RECEIVED_AMOUNT');?></th>
                <td>
                    <?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($this->receivedAmount, $this->currency)); ?>
                    <div class="small">
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=bid:' . $this->item->id); ?>">
                            <?php echo JText::sprintf('COM_CROWDFUNDING_TRANSACTIONS_N', $this->receivedTransactions); ?>
                        </a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>