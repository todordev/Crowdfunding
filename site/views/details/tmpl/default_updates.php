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
<?php if($this->isOwner) {?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="updatesForm" id="crowdf-updates-form" class="form-validate" autocomplete="off">

    <div class="form-group">
        <?php echo $this->form->getLabel('title'); ?>
        <?php echo $this->form->getInput('title'); ?>
    </div>

    <div class="form-group">
        <?php echo $this->form->getLabel('description'); ?>
        <?php echo $this->form->getInput('description'); ?>
    </div>
        
    <?php echo $this->form->getInput('id'); ?>
    <?php echo $this->form->getInput('project_id'); ?>

    <input type="hidden" name="task" value="update.save" />
    <?php echo JHtml::_('form.token'); ?>

    <div class="clearfix"></div>
    <button type="submit" class="btn btn-primary">
        <span class="fa fa-floppy-o"></span>
        <?php echo JText::_('JSAVE'); ?>
    </button>
    <button type="submit" class="btn btn-default" id="js-cfupdates-btn-reset">
        <span class="fa fa-refresh"></span>
        <?php echo JText::_('COM_CROWDFUNDING_RESET'); ?>
    </button>
</form>
<div class="hr mtb-15-0"></div>
<?php }?>
<?php if (count($this->items) > 0) {
    $socialProfile  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($this->item->user_id);
    $socialAvatar   = (!$this->socialProfiles) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($this->item->user_id, $this->avatarsSize);
?>
<?php foreach($this->items as $item ) { ?>
    <div class="row-fluid cf-update-item" id="update<?php echo $item->id;?>">
    
        <div class="media">
            <div class="media-left">
                <a href="<?php echo (!$socialProfile) ? 'javascript: void(0);' : $socialProfile;?>">
                    <img class="media-object" src="<?php echo $socialAvatar;?>" />
                </a>
            </div>
            
            <div class="media-body">
            	<div class="cf-info-bar"> 
            		<div class="pull-left">
            		  <?php echo JHtml::_('crowdfunding.postedby', $item->author, $item->record_date, $socialProfile)?>
            		</div>
                	<?php if($this->userId == $item->user_id ) {?>
                	<div class="pull-right">
                		<a href="javascript: void(0);" class="btn btn-mini btn-default js-cfupdates-btn-edit" data-id="<?php echo $item->id;?>">
                            <span class="fa fa-pencil-square-o"></span>
                            <?php echo JText::_('COM_CROWDFUNDING_EDIT');?>
                        </a>
                		<a href="javascript: void(0);" class="btn btn-mini btn-danger js-cfupdates-btn-remove" data-id="<?php echo $item->id;?>">
                            <span class="fa fa-trash"></span>
                            <?php echo JText::_('COM_CROWDFUNDING_DELETE');?>
                        </a>
                	</div>
                	<?php }?>
                	<div class="clearfix"></div>
            	</div>
            	<h3><?php echo $this->escape($item->title); ?></h3>
            	<p><?php echo nl2br($this->escape($item->description));?></p>
        	</div>
    	</div>
    	
    </div>
    <?php }?>
    
<input type="hidden" value="<?php echo JText::_('COM_CROWDFUNDING_QUESTION_REMOVE_RECORD');?>" id="cf-hidden-question" />
<?php }?>