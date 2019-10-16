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
    <div class="panel-heading"><?php echo JText::_('COM_CROWDFUNDING_REWARDS');?></div>
    <div class="panel-body">
        <div class="row-fluid">
            <div class="span12">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th class="title">
                            <?php echo JText::_('COM_CROWDFUNDING_REWARD'); ?>
                        </th>
                        <th width="30%">
                            <?php echo JText::_('COM_CROWDFUNDING_TRANSACTION_ID'); ?>
                        </th>
                        <th width="20%" class="hidden-phone">
                            <?php echo JText::_('JSTATUS'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    foreach ($this->rewards as $reward) {
                        $classRow    = (!$reward['reward_state']) ? '' : 'class="success"';
                        $rewardTitle = $this->escape($reward['reward_name']);

                        $rewardOptions = array(
                            'transaction_id' => $reward['transaction_id'],
                            'reward_id' => $reward['reward_id'],
                            'reward_title' => $rewardTitle,
                            'reward_state' => $reward['reward_state'],
                            'project_id' => $reward['project_id'],
                            'class' => 'width-100px'
                        );
                        ?>
                        <tr <?php echo $classRow; ?> id="js-reward-row-<?php echo $reward['transaction_id'];?>">
                            <td><?php echo $i; ?></td>
                            <td class="has-context">
                                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=rewards&pid='.(int)$reward['project_id'].'&filter_search=id:' . (int)$reward['reward_id']); ?>">
                                    <?php echo $rewardTitle; ?>
                                </a>

                                <div class="small">
                                    <?php echo JText::_('COM_CROWDFUNDING_PROJECT'); ?>:
                                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . (int)$reward['project_id']); ?>">
                                        <?php echo $this->escape($reward['project']); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions&filter_search=id:' . (int)$reward['transaction_id']); ?>">
                                    <?php echo $this->escape($reward['txn_id']); ?>
                                </a>
                            </td>
                            <td class="hidden-phone">
                                <?php echo JHtml::_('crowdfundingbackend.reward', $rewardOptions); ?>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>