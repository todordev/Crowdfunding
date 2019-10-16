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
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th class="title">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap">
        <?php echo JText::_("COM_CROWDFUNDING_INVESTED_AMOUNT"); ?>
    </th>
    <th width="10%" class="nowrap">
        <?php echo JText::_("COM_CROWDFUNDING_RECEIVED_AMOUNT"); ?>
    </th>
    <th width="5%" class="center nowrap">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_REGISTRATION_DATE', 'a.registerDate', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  