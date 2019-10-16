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
<h2><?php echo JText::_('COM_CROWDFUNDING_ADDITIONAL_INFORMATION'); ?></h2>
<table class="table table-condensed">
    <tbody>
    <?php foreach ($displayData['extra_data'] as $key => $value) { ?>
        <?php if (!is_array($value)) { ?>
            <tr>
                <th><?php echo $this->escape($key); ?></th>
                <td><?php echo $this->escape($value); ?></td>
            </tr>
        <?php } else { ?>
            <tr class="cf-response-type">
                <th colspan="2">
                    <?php echo JHtml::_('crowdfundingbackend.trackId', $key); ?>
                </th>
            </tr>

            <?php foreach ($value as $k => $v) { ?>
            <tr>
                <th><?php echo $this->escape($k); ?></th>
                <?php if (!is_array($v)) {?>
                <td><?php
                    if (is_bool($v)) {
                        echo (!$v) ? 'false' : 'true';
                    }  else {
                        echo $this->escape($v);
                    }
                    ?></td>
                <?php } else { ?>
                <td>
                    <pre><?php echo $this->escape(var_export($v, true)) ?></pre>
                </td>
                <?php } ?>
            
            </tr>
                
        <?php } ?>
    <?php } ?>
<?php } ?>
    </tbody>
</table>