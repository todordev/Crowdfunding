<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="cf-reward<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>

    <div class="row">
        <div class="col-md-8">
            <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=reward&id='.$this->reward->getId()); ?>" method="post" name="adminForm" id="adminForm">

                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_RECIPIENT', 'c.name', $this->listDirn, $this->listOrder); ?>
                            </th>
                            <th class="nowrap hidden-phone">
                                <?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_TRANSACTION_ID', 'a.txn_id', $this->listDirn, $this->listOrder); ?>
                            </th>
                            <th class="nowrap text-center">
                                <?php echo JHtml::_('crowdfunding.sort',  'JSTATUS', 'a.reward_state', $this->listDirn, $this->listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($this->items as $item) {
                            $classRow = (!$item->reward_state) ? '' : 'class="success"';
                            $socialProfileLink = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->receiver_id);?>
                        <tr <?php echo $classRow; ?>>
                            <td>
                                <?php echo JHtml::_('crowdfunding.socialProfileLink', $socialProfileLink, $item->name); ?>
                            </td>
                            <td class="hidden-phone">
                                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getTransactionsRoute().'&filter_search=id:'.$item->transaction_id); ?>">
                                    <?php echo $item->txn_id; ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <?php
                                $rewardOptions = array(
                                    'can_edit' => ((int)$this->userId === (int)$item->receiver_id),
                                    'reward_id' => $item->reward_id,
                                    'reward_title' => $item->reward_name,
                                    'transaction_id' => $item->transaction_id,
                                    'reward_state' => $item->reward_state
                                );

                                echo JHtml::_('crowdfunding.reward', $rewardOptions); ?>
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>

                <input type="hidden" name="task" value="" />
                <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
                <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
                <input type="hidden" name="<?php echo JSession::getFormToken();?>" value="1" id="js-form-token"/>
            </form>
        </div>

        <div class="col-md-4">
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <td colspan="2">
                        <h4><?php echo $this->escape($this->reward->getTitle()); ?></h4>
                        <p><?php echo $this->escape($this->reward->getDescription()); ?></p>

                        <?php if ($this->reward->getImage()) { ?>
                            <img src="../<?php echo $this->imagesFolder . '/'.$this->reward->getImageThumbnail(); ?>" />
                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo JText::_('COM_CROWDFUNDING_NUMBER_OF_REWARDS'); ?>
                    </td>
                    <td>
                        <?php echo JHtml::_('crowdfunding.rewardsNumber', $this->reward->getNumber()); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_('COM_CROWDFUNDING_DISTRIBUTED'); ?>
                    </td>
                    <td>
                        <?php echo $this->reward->getDistributed(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_('COM_CROWDFUNDING_AVAILABLE'); ?>
                    </td>
                    <td>
                        <?php echo JHtml::_('crowdfunding.rewardsAvailable', $this->reward->getNumber(), $this->reward->getDistributed()); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_('COM_CROWDFUNDING_DELIVERY_UNTIL_DATE'); ?>
                    </td>
                    <td>
                        <?php echo $this->deliveryDate; ?>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
<a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($this->reward->getProjectId(), 'manager')); ?>" class="btn btn-default">
    <span class="fa fa-chevron-left"></span>
    <?php echo JText::_('COM_CROWDFUNDING_BACK_PROJECT_MANAGER'); ?>
</a>
<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
        <?php } ?>
        <?php echo $this->pagination->getPagesLinks(); ?> </div>
<?php } ?>