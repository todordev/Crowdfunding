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
<?php if (!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <?php else : ?>
<div id="j-main-container">
    <?php endif; ?>
    <div class="row-fluid">
        <div class="span6">
            <?php echo $this->loadTemplate('requirements'); ?>
        </div>
        <?php if (JComponentHelper::isInstalled('com_acymailing')) { ?>
        <div class="span6">
            <?php echo $this->loadTemplate('acymailing'); ?>
        </div>
        <?php } ?>
    </div>
</div>
</div>