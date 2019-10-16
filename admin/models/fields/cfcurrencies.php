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

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      Crowdfunding
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldCfCurrencies extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfcurrencies';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     * @throws \RuntimeException
     */
    protected function getOptions()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id AS value, ' . $query->concatenate(array('a.code', 'a.title'), ' - ') . ' AS text')
            ->from($db->quoteName('#__crowdf_currencies', 'a'))
            ->order('a.code ASC');

        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
