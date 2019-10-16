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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" id="js-cftools-acyfunders">
    <div class="panel panel-default">
        <div class="panel-heading">
            AcyMailing
            <img src="../media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-cfacy-ajax-loader" style="display: none;" />
        </div>
        <div class="panel-body">

            <?php echo JHtml::_('select.genericlist', $this->projects, 'acy_pid', null, 'id', 'title'); ?>
            <div>&nbsp;</div>
            <?php echo JHtml::_('select.genericlist', $this->lists, 'acy_lid', null, 'id', 'name'); ?>
            <div>&nbsp;</div>
            <table class="table table-striped">
                <tbody>
                <tr>
                    <td><?php echo JText::_('COM_CROWDFUNDING_TOTAL'); ?></td>
                    <td><span id="js-acymailing-total-value">0</span></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('COM_CROWDFUNDING_FOR_IMPORTING'); ?></td>

                    <td><span id="js-acymailing-forimporting-value">0</span></td>
                </tr>
                </tbody>
            </table>

            <div>&nbsp;</div>
            <button type="button" class="btn btn-large btn-block btn-primary" id="js-cftools-acy-addfunders">
                <?php echo JText::_('COM_CROWDFUNDING_ACYMAILING_IMPORT_FUNDERS_LIST'); ?>
            </button>
            <div>&nbsp;</div>
            <div class="alert alert-info" role="alert">
                <i class="icon-info"></i>
                <?php echo JText::_('COM_CROWDFUNDING_ACYMAILING_IMPORT_FUNDERS_INFORMATION'); ?>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="format" value="raw"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
