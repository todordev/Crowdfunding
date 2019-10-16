<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

jimport('Prism.init');
jimport('Crowdfunding.init');

class JFormFieldCfProjects extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfprojects';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function getOptions()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id AS value, a.title AS text')
            ->from($db->quoteName('#__crowdf_projects', 'a'));

        // Get the options.
        $db->setQuery($query);

        $options = $db->loadObjectList();

        array_unshift($options, JHtml::_('select.option', '0', '- ' . JText::_('COM_CROWDFUNDING_SELECT_PROJECT') . ' -', 'value', 'text'));

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
