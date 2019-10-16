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
?>
<div class="col-md-12">
    <div class="panel panel-brown">
        <div class="panel-heading">
            <h4><span class="fa fa-gift"></span> <?php echo JText::_('COM_CROWDFUNDING_REWARDS'); ?></h4>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th class="title">
                        <?php echo JText::_('COM_CROWDFUNDING_TITLE'); ?>
                    </th>
                    <th width="10%" class="center hidden-phone">
                        <?php echo JText::_('COM_CROWDFUNDING_NUMBER'); ?>
                    </th>
                    <th width="10%" class="center hidden-phone">
                        <?php echo JText::_('COM_CROWDFUNDING_DISTRIBUTED'); ?>
                    </th>
                    <th width="10%" class="center hidden-phone">
                        <?php echo JText::_('COM_CROWDFUNDING_AVAILABLE'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->rewards as $reward) { ?>
                <tr class="">
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=reward&id=' . (int)$reward['id']); ?>">
                            <?php echo $this->escape($reward['title']); ?>
                        </a>
                        <p><?php echo $this->escape($reward['description']); ?></p>
                    </td>
                    <td class="center hidden-phone">
                        <?php echo JHtml::_('crowdfunding.rewardsNumber', $reward['number']); ?>
                    </td>
                    <td class="center hidden-phone">
                        <?php echo $reward['distributed'];?>
                    </td>
                    <td class="center hidden-phone">
                        <?php echo JHtml::_('crowdfunding.rewardsAvailable', $reward['number'], $reward['distributed']); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>