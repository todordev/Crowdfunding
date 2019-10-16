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
<div class="span8">

    <!--  Row 1 -->
    <div class="row-fluid">
        <div class="span6">
            <div class="panel panel-default">
                <div class="panel-heading latest-started">
                    <i class="icon-list"></i>
                    <?php echo JText::_('COM_CROWDFUNDING_LATEST_STARTED'); ?>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="20">#</th>
                            <th><?php echo JText::_('COM_CROWDFUNDING_PROJECT'); ?></th>
                            <th width="100" class="nowrap"><?php echo JText::_('COM_CROWDFUNDING_STARTED_ON'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->latestStarted); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . (int)$this->latestStarted[$i]['id']); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->latestStarted[$i]['title']), 53); ?>
                                    </a>
                                </td>
                                <td style="min-width: 100px;">
                                    <?php echo JHtml::_('date', $this->latestStarted[$i]['funding_start'], $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'))); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="span6">
            <div class="panel panel-default">
                <div class="panel-heading popular">
                    <i class="icon-list"></i>
                    <?php echo JText::_('COM_CROWDFUNDING_POPULAR'); ?>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="20">#</th>
                            <th><?php echo JText::_('COM_CROWDFUNDING_PROJECT'); ?></th>
                            <th width="100" class="nowrap"><?php echo JText::_('COM_CROWDFUNDING_HITS'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->popular); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . (int)$this->popular[$i]['id']); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->popular[$i]['title']), 53); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo (int)$this->popular[$i]['hits']; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Row 1 -->

    <!--  Row 2 -->
    <div class="row-fluid">
        <div class="span6">
            <div class="panel panel-default">
                <div class="panel-heading latest-created">
                    <i class="icon-list"></i>
                    <?php echo JText::_('COM_CROWDFUNDING_LATEST_CREATED'); ?>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="20">#</th>
                            <th><?php echo JText::_('COM_CROWDFUNDING_PROJECT'); ?></th>
                            <th width="100" class="nowrap"><?php echo JText::_('COM_CROWDFUNDING_CREATED_ON'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->latestCreated); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . (int)$this->latestCreated[$i]['id']); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->latestCreated[$i]['title']), 53); ?>
                                    </a>
                                </td>
                                <td style="min-width: 100px;">
                                    <?php echo JHtml::_('date', $this->latestCreated[$i]['created'], $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'))); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="span6">
            <div class="panel panel-default">
                <div class="panel-heading mostfunded">
                    <i class="icon-list"></i>
                    <?php echo JText::_('COM_CROWDFUNDING_MOST_FUNDED'); ?>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="20">#</th>
                            <th><?php echo JText::_('COM_CROWDFUNDING_PROJECT'); ?></th>
                            <th width="100" class="nowrap"><?php echo JText::_('COM_CROWDFUNDING_FUNDS'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        for ($i = 0, $max = count($this->mostFunded); $i < $max; $i++) {
                            $moneyAmount = new Prism\Money\Money($this->mostFunded[$i]['funded'], $this->currency);
                        ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . (int)$this->mostFunded[$i]['id']); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->mostFunded[$i]['title']), 53); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo $this->moneyFormatter->formatCurrency($moneyAmount);?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Row 2 -->
</div>