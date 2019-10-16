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
<div class="cfdetails<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php
    if ($this->params->get('seo_project_title', 1)) {
        $headingTag = $this->params->get('seo_project_title_header', 'h1');
    ?>
    <<?php echo $headingTag; ?>><?php echo $this->item->title; ?></<?php echo $headingTag; ?>>
	<?php } ?>

<?php if ($this->item->event->beforeDisplayContent) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="cf-details-block cf-border-bottom"><?php echo $this->item->event->beforeDisplayContent; ?></div>
        </div>
    </div>
<?php } ?>

<div class="row">
    <div class="col-md-12">
        <div class="cf-details-block">
        <?php
            if (!$this->item->pitch_video) {
                if (!$this->item->pitch_image) {
                    echo JHtml::_('image', 'media/com_crowdfunding/images/no_image_large.png', $this->escape($this->item->title), array('class' => 'img-responsive'));
                } else {
                    echo JHtml::_('image', $this->imageFolder . '/' . $this->item->pitch_image, $this->escape($this->item->title), array('class' => 'img-responsive'));
                }
            } else {
                echo JHtml::_('crowdfunding.video', $this->item->pitch_video, $this->params->get('video_responsive', false));
            } ?>
        </div>

        <?php
        if ($this->item->event->onContentAfterDisplayMedia) { ?>
            <?php echo $this->item->event->onContentAfterDisplayMedia; ?>
        <?php
        } ?>

        <div class="cf-details-block cf-border-top">
        <?php
        switch ($this->screen) {
            case 'updates':
                echo $this->loadTemplate('updates');
                break;
            case 'comments':
                echo $this->loadTemplate('comments');
                break;
            case 'funders':
                echo $this->loadTemplate('funders');
                break;
            default:
                echo $this->loadTemplate('home');
                break;
        } ?>
        </div>
    </div>

</div>
<?php
if (!empty($this->item->event->onContentAfterDisplay)) {
    echo $this->item->event->onContentAfterDisplay;
} ?>
</div>
<div class="clearfix">&nbsp;</div>