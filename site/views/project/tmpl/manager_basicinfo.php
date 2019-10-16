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
<div class="panel panel-info">
    <div class="panel-heading">
        <span class="fa fa-info-circle"></span> <?php echo JText::_('COM_CROWDFUNDING_BASIC_INFORMATION'); ?>
    </div>
    <table class="table table-striped">
        <tbody>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_GOAL'); ?></td>
                <td><?php echo $this->goal;?> </td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_RAISED'); ?></td>
                <td><?php echo $this->raised;?> </td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_STARTING_DATE'); ?></td>
                <td><?php echo JHtml::_('Prism.ui.date', $this->item->funding_start, $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3')));?></td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_ENDING_DATE'); ?></td>
                <td><?php echo JHtml::_('Prism.ui.date', $this->item->funding_end, $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3')));?></td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_LAUNCHED'); ?></td>
                <td>
                    <?php echo JHtml::_('crowdfunding.unitState', $this->item->published, ['hint' => true, 'title_true' => JText::_('COM_CROWDFUNDING_LAUNCHED'), 'title_false' => JText::_('COM_CROWDFUNDING_NOTLAUNCHED') ]); ?>
                </td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_APPROVED'); ?></td>
                <td>
                    <?php echo JHtml::_('crowdfunding.unitState', $this->item->approved, ['hint' => true, 'title_true' => JText::_('COM_CROWDFUNDING_APPROVED'), 'title_false' => JText::_('COM_CROWDFUNDING_NOTAPPROVED') ]); ?>
                    <?php echo JHtml::_('crowdfunding.featured', $this->item->featured, ['hint' => true, 'title' => JText::_('COM_CROWDFUNDING_FEATURED')]); ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
