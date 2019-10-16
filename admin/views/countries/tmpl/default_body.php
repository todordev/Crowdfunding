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
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=country&layout=edit&id=' . (int)$item->id); ?>">
                <?php echo $this->escape($item->name); ?>
            </a>
            <div class="small">
                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=regions&code=' . $this->escape($item->code)); ?>">
                <?php
                $numberOfRegions = array_key_exists($item->code, $this->regionsNumber) ? (int)$this->regionsNumber[$item->code] : 0;
                echo JText::sprintf('COM_CROWDFUNDING_REGIONS_D', $numberOfRegions);
                ?>
                </a>
            </div>
        </td>
        <td class="center"><?php echo $this->escape($item->code); ?></td>
        <td class="hidden-phone"><?php echo $this->escape($item->locale); ?></td>
        <td class="hidden-phone"><?php echo $this->escape($item->latitude); ?></td>
        <td class="hidden-phone"><?php echo $this->escape($item->longitude); ?></td>
        <td class="hidden-phone"><?php echo $this->escape($item->timezone); ?></td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>