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
<?php if ($this->commentsEnabled) { ?>

<?php if ($this->userId > 0) { ?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="commentsForm" id="crowdf-comments-form" class="form-validate" autocomplete="off">

    <div class="form-group">
    <?php echo $this->form->getLabel('comment'); ?>
    <?php echo $this->form->getInput('comment'); ?>
    </div>

    <div class="form-group">
    <?php echo $this->form->getInput('id'); ?>
    <?php echo $this->form->getInput('project_id'); ?>
    </div>
        
    <input type="hidden" name="task" value="comment.save" />
    <?php echo JHtml::_('form.token'); ?>
    
    <div class="clearfix"></div>
    <button type="submit" class="btn btn-primary"><?php echo JText::_('COM_CROWDFUNDING_SEND')?></button>
    <button type="submit" class="btn btn-default" id="js-cfcomments-btn-reset"><?php echo JText::_('COM_CROWDFUNDING_RESET')?></button>
    
</form>
<div class="hr mtb-15-0"></div>
<?php } ?>
<?php if (count($this->items) > 0) {
    
    foreach($this->items as $item ) {

        // Do not display items that are not published, it the person is not their owner.
        if (!$item->published and ( $item->user_id != $this->userId) ) {
            continue;
        }
        
        $socialProfile  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->user_id);
        $socialAvatar   = (!$this->socialProfiles) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($item->user_id, $this->avatarsSize);
?>
    <div class="row-fluid cf-comment-item" id="comment<?php echo $item->id;?>">
        
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
            			<?php if(!$item->published AND ( $item->user_id == $this->userId) ) {?>
                    		<p class="message"><?php echo JText::_('COM_CROWDFUNDING_COMMENT_NOT_APPROVED');?></p>
                        <?php }?>
            		</div>
                	<?php if((int)$this->userId === (int)$item->user_id) {?>
                	<div class="pull-right">
                		<a href="javascript: void(0);" class="btn btn-mini btn-default js-cfcomments-btn-edit" data-id="<?php echo $item->id;?>"><?php echo JText::_('COM_CROWDFUNDING_EDIT');?></a>
                		<a href="javascript: void(0);" class="btn btn-mini btn-danger js-cfcomments-btn-remove" data-id="<?php echo $item->id;?>"><?php echo JText::_('COM_CROWDFUNDING_DELETE');?></a>
                	</div>
                	<?php }?>
                	
                	<div class="clearfix"></div>
            	</div>
            	<p><?php echo nl2br($item->comment);?></p>
        	</div>
    	</div>
    	
    </div>
    
<?php }?>
    
<input type="hidden" value="<?php echo JText::_('COM_CROWDFUNDING_QUESTION_REMOVE_COMMENT');?>" id="cf-hidden-question" />
<?php }?>

<?php } ?>

<?php
if(!empty($this->onCommentAfterDisplay)) {
    echo $this->onCommentAfterDisplay;
}
?>