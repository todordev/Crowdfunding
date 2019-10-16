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
    <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $this->listDirn, $this->listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th width="1%" style="min-width: 55px" class="nowrap center">
        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
    </th>
    <th class="title">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_GOAL', 'a.goal', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_FUNDED', 'a.funded', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_START_DATE', 'a.funding_start', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_END_DATE', 'a.funding_end', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_CREATED', 'a.created', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap">
        <?php echo JHtml::_('searchtools.sort', 'COM_CROWDFUNDING_OWNER', 'u.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>