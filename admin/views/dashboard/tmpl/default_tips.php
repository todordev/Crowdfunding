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

$tip = new Crowdfunding\Tip\Tip();
?>
<div class="panel panel-default">
    <div class="panel-heading bgcolor-yellow-lighter">
        <i class="icon-question"></i>
        <?php echo JText::_('COM_CROWDFUNDING_TIPS_AND_TRICKS'); ?>
    </div>
    <div class="panel-body">
        <?php echo JText::_($tip->getTip()); ?>
    </div>
</div>