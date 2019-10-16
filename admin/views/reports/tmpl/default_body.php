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
        <td class="has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=report&layout=edit&id=" . (int)$item->id); ?>"><?php echo $this->escape($item->subject); ?></a>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=report&format=raw&id=" . (int)$item->id); ?>" class="btn btn-mini js-cf-preview-report-btn" data-project-id="<?php echo $item->project_id;?>">
                <i class="icon-eye-open"></i>
            </a>
            <div class="small">
                <?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?> :
                <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$item->project_id); ?>">
                    <span id="js-title<?php echo $item->project_id;?>"><?php echo $this->escape($item->project); ?></span>
                </a>
            </div>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_("crowdfundingbackend.name", $item->user, $item->user_id); ?>
        </td>
        <td class="hidden-phone">
            <?php echo $this->escape($item->email); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_('date', $item->record_date, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php } ?>