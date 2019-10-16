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

JFormHelper::loadFieldClass('radio');

class JFormFieldCfFundingType extends JFormFieldRadio
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'cffundingtype';

    /**
     * Method to get the field input markup for a spacer.
     * The spacer does not have accept input.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        // Get component parameters
        $componentParams    = JComponentHelper::getParams('com_crowdfunding');
        $allowedFundingType = $componentParams->get('project_funding_type');

        // Initialize variables.
        $html = array();

        // Build the radio field output.
        switch ($allowedFundingType) {
            case 'FIXED':
                $this->prepareFixed($html);
                break;

            case 'FLEXIBLE':
                $this->prepareFlexible($html);
                break;

            default:
                $this->prepareFixed($html);
                $this->prepareFlexible($html);

                break;
        }

        return implode($html);
    }

    private function prepareFixed(&$html)
    {
        // Initialize some option attributes.
        $checked = ($this->value === 'FIXED') ? ' checked="checked"' : '';

        $html[] = '<div class="radio">';
        $html[] = '<label for="' . $this->id . '_fixed">';
        $html[] = '<input type="radio" id="' . $this->id . '_fixed" name="' . $this->name . '"' . ' value="FIXED"' . $checked . '/>';
        $html[] = JText::_('COM_CROWDFUNDING_FIELD_FUNDING_TYPE_FIXED');
        $html[] = '</label>';

        $html[] = '<span class="help-block">'.JText::_('COM_CROWDFUNDING_FIELD_FUNDING_TYPE_HELP_FIXED').'</span>';
        $html[] = '</div>';
    }

    private function prepareFlexible(&$html)
    {
        // Initialize some option attributes.
        $checked = ($this->value === 'FLEXIBLE') ? ' checked="checked"' : '';

        $html[] = '<div class="radio">';
        $html[] = '<label for="' . $this->id . '_flexible">';
        $html[] = '<input type="radio" id="' . $this->id . '_flexible" name="' . $this->name . '"' . ' value="FLEXIBLE"' . $checked . '/>';
        $html[] = JText::_('COM_CROWDFUNDING_FIELD_FUNDING_TYPE_FLEXIBLE');
        $html[] = '</label>';

        $html[] = '<span class="help-block">'.JText::_('COM_CROWDFUNDING_FIELD_FUNDING_TYPE_HELP_FLEXIBLE').'</span>';
        $html[] = '</div>';
    }
}
