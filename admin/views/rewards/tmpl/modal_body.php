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
<?php foreach ($this->items as $i => $item) {
    $dateValidator = new Prism\Validator\Date($item->delivery);
    $ordering = ($this->listOrder === 'a.ordering');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="has-context">
            <a href="javascript: void(0);" data-id="<?php echo (int)$item->id; ?>" data-title="<?php echo $this->escape($item->title); ?>" class="js-reward-element">
                <?php echo $this->escape($item->title); ?>
            </a>
            <div class="small">
                <?php echo JText::sprintf('COM_CROWDFUNDING_PROJECT_S', $this->escape($item->project_title)); ?>
                <?php echo JText::sprintf('COM_CROWDFUNDING_PROJECT_ID_D', $item->project_id); ?>
            </div>
        </td>
        <td class="center"><?php echo $this->moneyFormatter->formatCurrency(new Prism\Money\Money($item->amount, $this->currency)); ?></td>
        <td class="center hidden-phone"><?php echo $item->number; ?></td>
        <td class="center hidden-phone"><?php echo $item->distributed; ?></td>
        <td class="center hidden-phone"><?php echo $item->number - $item->distributed; ?></td>
        <td class="hidden-phone">
            <?php echo $dateValidator->isValid() ? JHtml::_('date', $item->delivery, JText::_('DATE_FORMAT_LC3')) : '--'; ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>