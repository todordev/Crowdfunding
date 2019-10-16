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
<div class="modal hide fade" id="js-preview-report-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_("COM_CROWDFUNDING_REPORTED"); ?> : <span id="js-modal-title"></span></h3>
    </div>
    <div class="modal-body" id="js-modal-data">
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" id="js-modal-close-btn">Close</a>
    </div>
</div>