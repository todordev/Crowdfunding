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
$itemSpan = ($this->subcategoriesPerRow > 0) ? round(12 / $this->subcategoriesPerRow) : 4;
?>
<div id="cf-categories-grid">
    <div class="row">
        <?php foreach ($this->categories as $item) { ?>
        <div class="col-sm-<?php echo $itemSpan; ?>">
            <div class="thumbnail cf-category">
                <?php if ($this->params->get('subcategory_show_image', Prism\Constants::DISPLAY)) {?>
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getCategoryRoute($item->slug)); ?>">
                    <?php if (isset($item->image_link) and $item->image_link !== '') { ?>
                        <img src="<?php echo $item->image_link; ?>" alt="<?php echo $this->escape($item->title); ?>" />
                    <?php } else { ?>
                        <img src="<?php echo 'media/com_crowdfunding/images/no_image.png'; ?>" alt="<?php echo $this->escape($item->title); ?>" width="200" height="200" />
                    <?php } ?>
                </a>
                <?php } ?>

                <div class="caption height-150px absolute-bottom">
                    <?php if ($this->params->get('subcategory_show_title', Prism\Constants::DISPLAY)) {?>
                    <h3>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getCategoryRoute($item->slug)); ?>">
                            <?php echo JHtmlString::truncate($item->title, $this->params->get('subcategory_title_length'), true, false); ?>
                        </a>
                        <?php
                        if ($this->displayProjectsNumber) {
                            echo '( '. $item->project_number . ' )';
                        } ?>
                    </h3>
                    <?php } ?>
                    <?php if ($this->params->get('subcategory_show_description', Prism\Constants::DISPLAY)) { ?>
                        <p><?php echo JHtmlString::truncate($item->description, $this->params->get('subcategory_description_length'), true, false); ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>