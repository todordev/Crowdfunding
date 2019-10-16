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
    <div class="panel-heading"><?php echo JText::_('COM_CROWDFUNDING_INVESTOR_INFORMATION');?></div>
    <div class="panel-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th><?php echo JText::_('COM_CROWDFUNDING_PASSPORT_ID');?></th>
                    <td><?php echo $this->item->passport_id; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>