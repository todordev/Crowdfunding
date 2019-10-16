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
<?php foreach ($this->items as $i => $item) { ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=log&layout=view&id=' . (int)$item->id); ?>"
               id="js-title<?php echo $item->id; ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td class="center hidden-phone">
            <a class="btn js-preview-log" data-item-id="<?php echo $item->id; ?>"
               href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=log&layout=preview&format=raw&id=' . (int)$item->id); ?>">
                <i class="icon-eye-open"></i>
            </a>
        </td>
        <td class="center hidden-phone">
            <?php echo $this->escape($item->type); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_('date', $item->record_date, JText::_('DATE_FORMAT_LC2')); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php } ?>