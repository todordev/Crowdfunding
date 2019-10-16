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
<div class="js-reward-image-wrapper mtr-10-10" >
    <div class="thumbnail">
        <?php
        $rewardId   = Joomla\Utilities\ArrayHelper::getValue($this->formItem, 'id');
        $imageThumb = Joomla\Utilities\ArrayHelper::getValue($this->formItem, 'image_thumb');

        if ($imageThumb !== null and JString::strlen($imageThumb) > 0) {
            $rewardImage = $this->rewardsImagesUri.'/'.$imageThumb;
            $displayRemoveButton = '';
        } else {
            $rewardImage = 'media/com_crowdfunding/images/no_image.png';
            $displayRemoveButton = 'style="display: none;';
        }

        echo JHtml::_('crowdfunding.rewardImage', $rewardImage, $rewardId, $this->params->get('rewards_image_thumb_width', 200), $this->params->get('rewards_image_thumb_height', 200));
        ?>
    </div>

    <?php if (!$this->debugMode) {?>
    <input type="file" class="js-reward-image" name="images[<?php echo (int)$this->formItem['id']?>]" value="" />
    <a href="javascript: void(0);" class="btn btn-danger js-btn-remove-reward-image mt-10" <?php echo $displayRemoveButton; ?> data-reward-id="<?php echo $rewardId;?>">
        <span class="fa fa-trash"></span>
        <?php echo JText::_('COM_CROWDFUNDING_REMOVE_IMAGE')?>
    </a>
    <?php } ?>

</div>