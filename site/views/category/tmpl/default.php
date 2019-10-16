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
<div class="cfcategory<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>

    <?php if ($this->params->get('category_show_description', Prism\Constants::DO_NOT_DISPLAY)) {
        echo $this->item->description;
    } ?>

    <?php if (!$this->items) { ?>
        <p class="alert alert-warning"><?php echo JText::_('COM_CROWDFUNDING_NO_ITEMS_MATCHING_QUERY'); ?></p>
    <?php } ?>

    <?php if ($this->items) {
        $layout      = new JLayoutFile($this->params->get('grid_layout', 'items_grid'));
        echo $layout->render($this->layoutData);
    } ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
        <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
        <?php } ?>
        <?php echo $this->pagination->getPagesLinks(); ?> </div>
    <?php } ?>

    <?php
    if (!empty($this->displaySubcategories) and !empty($this->categories)) { ?>
        <h4><?php echo JText::_('COM_CROWDFUNDING_SUBCATEGORIES'); ?></h4>
        <?php echo $this->loadTemplate('categories');
    } ?>
</div>
<div class="clearfix">&nbsp;</div>