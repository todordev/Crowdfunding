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
<?php echo $this->form->renderField('pitch_video'); ?>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('pitch_image'); ?></div>
    <div class="controls">
        <div class="fileupload fileupload-new" data-provides="fileupload">
        <span class="btn btn-file">
            <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_('COM_CROWDFUNDING_SELECT_FILE'); ?></span>
            <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_('COM_CROWDFUNDING_CHANGE'); ?></span>
            <?php echo $this->form->getInput('pitch_image'); ?>
        </span>
            <span class="fileupload-preview"></span>
            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
        </div>
    </div>
</div>
<?php echo $this->form->renderField('description'); ?>

