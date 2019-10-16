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
        <span class="fa fa-bar-chart"></span> <?php echo JText::_('COM_CROWDFUNDING_BASIC_STATISTICS'); ?>
    </div>
    <table class="table table-striped">
        <tbody>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_HITS'); ?></td>
                <td><?php echo $this->item->hits;?></td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_UPDATES'); ?></td>
                <td><?php echo $this->statistics['updates'];?></td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_COMMENTS'); ?></td>
                <td><?php echo $this->statistics['comments'];?></td>
            </tr>
            <tr>
                <td><?php echo JText::_('COM_CROWDFUNDING_FUNDERS'); ?></td>
                <td><?php echo $this->statistics['funders'];?></td>
            </tr>
        </tbody>
    </table>
</div>

