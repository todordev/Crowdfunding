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
$itemSpan = ($this->numberInRow > 0) ? round(12 / $this->numberInRow) : 4;
?>
<?php if (count($this->items) > 0) { ?>
<div id="cf-categories-grid">
    <div class="row">
        <?php foreach ($this->items as $item) { ?>
        <div class="col-md-<?php echo $itemSpan; ?>">
            <div class="thumbnail cf-category">
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getCategoryRoute($item->slug)); ?>">
                    <?php if (isset($item->image_link) and $item->image_link !== '') { ?>
                        <img src="<?php echo $item->image_link; ?>" alt="<?php echo $this->escape($item->title); ?>" />
                    <?php } else { ?>
                        <img src="<?php echo 'media/com_crowdfunding/images/no_image.png'; ?>"
                             alt="<?php echo $this->escape($item->title); ?>" width="200"
                             height="200" />
                    <?php } ?>
                </a>

                <div class="caption height-150px absolute-bottom">
                    <h3>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getCategoryRoute($item->slug)); ?>">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                        <?php
                        if ($this->displayProjectsNumber) {
                            echo '( '.$item->project_number. ' )';
                        } ?>
                    </h3>
                    <?php if ((bool)$this->params->get('categories_show_description', true)) { ?>
                        <p><?php echo JHtmlString::truncate($item->description, $this->descriptionLength, true, false); ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>