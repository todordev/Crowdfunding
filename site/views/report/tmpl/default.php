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
<div class='row'>
	<div class='col-md-12'>
		<form action='<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>' method='post' name='reportForm' id='reportForm' autocomplete='off' >

            <div class='form-group'>
                <?php echo $this->form->getLabel('subject'); ?>
                <?php echo $this->form->getInput('subject'); ?>
            </div>
            <div class='form-group'>
                <?php echo $this->form->getLabel('description'); ?>
                <?php echo $this->form->getInput('description'); ?>
            </div>
            <div class='form-group'>
                <?php echo $this->form->getLabel('email'); ?>
                <?php echo $this->form->getInput('email'); ?>
            </div>

			<?php if(!$this->item){ ?>
                <div class='form-group'>
                    <?php echo $this->form->getLabel('project'); ?>
                    <?php echo $this->form->getInput('project'); ?>
                </div>
			<?php } else { ?>
                <div class='form-group'>
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </div>
			<?php } ?>

            <div class='form-group'>
			<?php echo $this->form->getControlGroup('captcha'); ?>
            </div>

			<?php echo $this->form->getInput('id'); ?>
			<?php echo JHtml::_('form.token'); ?>

			<input type='hidden' name='task' value='report.send' />
			<button type='submit' class='btn btn-primary'>
				<?php echo JText::_('COM_CROWDFUNDING_SUBMIT');?>
			</button>

		</form>
	</div>
</div>