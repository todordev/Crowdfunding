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
<div class="cfbacking<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row">
		<div class="col-md-12">
    		<?php
                $layout = new JLayoutFile('payment_wizard');
        	    echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
    <?php
    if ($this->event->onDisplay !== '') {
        echo $this->event->onDisplay;
    }
    ?>
</div>
