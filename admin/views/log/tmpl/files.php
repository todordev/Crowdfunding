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
<div class="row-fluid" id="js-log-files">
    <div class="span3">
        <table class="table table-condensed">
            <thead>
            <tr>
                <th class="center nowrap hidden-phone">#</th>
                <th><?php echo JText::_('COM_CROWDFUNDING_FILENAME'); ?></th>
                <th class="center nowrap hidden-phone"><?php echo JText::_('COM_CROWDFUNDING_ACTION'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php for ($i = 0, $max = count($this->files); $i < $max; $i++) {
                $file = str_replace(JPATH_ROOT, '', $this->files[$i]);
                ?>
                <tr id="js-file-row<?php echo $i; ?>">
                    <td class="center nowrap hidden-phone"><?php echo $i + 1; ?></td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=log&layout=file&format=raw&file=' . rawurlencode($file)); ?>"
                           class="js-log-file" data-row-id="<?php echo $i; ?>">
                            <?php echo basename($this->files[$i]); ?>
                        </a>
                        <img src="../media/com_crowdfunding/images/ajax-loader.gif" style="display: none;" id="js-ajaxload-icon<?php echo $i; ?>"/></td>
                    <td class="center nowrap hidden-phone">
                        <a href="javascript: void(0);" class="btn btn-small hasTooltip" title="<?php echo JText::sprintf('COM_CROWDFUNDING_FILE_IS', $this->escape($this->files[$i])); ?>"><i class="icon-question-sign"></i></a>
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=log.remove'); ?>" data-row-id="js-file-row<?php echo $i; ?>" data-filename="<?php echo $this->escape($file); ?>" class="btn btn-small hasTooltip js-log-file-remove-btn" title="<?php echo JText::_("JACTION_DELETE"); ?>"><i class="icon-trash"></i></a>
                        <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&task=log.download&format=raw&file=' . rawurlencode($file)); ?>" class="btn btn-small hasTooltip" title="<?php echo JText::_("COM_CROWDFUNDING_DOWNLOAD"); ?>"><i class="icon-download"></i></a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="span9">
        <h2 id="js-file-title"></h2>
        <pre id="js-file-preview" style="height: 100%; ovrflow: hidden;"></pre>
    </div>

    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
          id="adminForm">
        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>