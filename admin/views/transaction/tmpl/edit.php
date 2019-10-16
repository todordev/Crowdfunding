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
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

            <?php echo $this->form->renderField('txn_amount'); ?>
            <?php echo $this->form->renderField('txn_currency'); ?>
            <?php echo $this->form->renderField('service_provider'); ?>
            <?php echo $this->form->renderField('service_alias'); ?>
            <?php echo $this->form->renderField('txn_date'); ?>
            <?php echo $this->form->renderField('txn_status'); ?>
            <?php echo $this->form->renderField('txn_id'); ?>
            <?php echo $this->form->renderField('parent_txn_id'); ?>
            <?php echo $this->form->renderField('investor_id'); ?>
            <?php echo $this->form->renderField('receiver_id'); ?>
            <?php echo $this->form->renderField('project_id'); ?>
            <?php echo $this->form->renderField('reward_id'); ?>
            <?php echo $this->form->renderField('id'); ?>

            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

    <div class="span6">
        <?php
        if (!empty($this->extraData)) {
            $layout = new JLayoutFile('transaction_info', $this->layoutsBasePath);
            echo $layout->render(['extra_data' => $this->extraData]);
        }
        ?>
    </div>
</div>