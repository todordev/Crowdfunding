<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

class JFormFieldCfPercent extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     *
     * @since  11.1
     */
    protected $type = 'cfpercent';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $size      = $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $readonly  = ((string)$this->element['readonly'] === 'true') ? ' readonly="readonly"' : '';
        $disabled  = ((string)$this->element['disabled'] === 'true') ? ' disabled="disabled"' : '';
        $class     = (!empty($this->element['class'])) ? ' class="' . (string)$this->element['class'] . '"' : '';

        $cssLayout = (!empty($this->element['css_layout'])) ? (string)$this->element['css_layout'] : 'Bootstrap 2';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        $html = array();
        if ($cssLayout === 'Bootstrap 3') {
            $html[] = '<div class="input-group">';

            $html [] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' .
                htmlspecialchars($this->value) . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';

            // Appended
            $html[] = '<div class="input-group-addon">%</div>';
            $html[] = '</div>';
        } else { // Bootstrap 2
            $html[] = '<div class="input-append">';
            
            $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' .
                htmlspecialchars($this->value) . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';

            // Appended
            $html[] = '<span class="add-on">%</span>';
            $html[] = '</div>';
        }

        return implode("\n", $html);
    }
}
