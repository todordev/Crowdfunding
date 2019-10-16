<?php
/**
 * @package      Crowdfunding
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

jimport('Prism.init');
jimport('Crowdfunding.init');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      Crowdfunding
 * @subpackage   Component
 * @since        1.6
 */
class JFormFieldCfUrl extends JFormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfurl';

    /**
     * Method to get the field options.
     *
     * @return  string   The field option objects.
     * @since   1.6
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $size      = $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $rows      = $this->element['rows'] ? ' rows="' . (int)$this->element['rows'] . '"' : '';
        $readonly  = ((string)$this->element['readonly'] === 'true') ? ' readonly="readonly"' : '';
        $disabled  = ((string)$this->element['disabled'] === 'true') ? ' disabled="disabled"' : '';
        $class     = (!empty($this->element['class'])) ? ' class="' . (string)$this->element['class'] . '"' : '';
        $required  = $this->required ? ' required aria-required="true"' : '';

        $defaultValue  = '';
        $this->default = trim($this->default);
        if ($this->default) {
            $defaultValue = JUri::root().$this->default;
        }

        $v1 = md5($this->default);
        $v2 = md5($this->value);
        if (!$this->value or (strcmp($v1, $v2) === 0)) {
            $this->value = $defaultValue;
        }

        return '<textarea name="' . $this->name . '" id="' . $this->id . '"'. $size . $rows . $readonly . $disabled . $class . $required .' >'.$this->value.'</textarea>';
    }
}
