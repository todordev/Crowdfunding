<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();

class CrowdfundingModelIntro extends JModelItem
{
    protected $item;

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableIntro|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Intro', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load the object state.
        $value = $app->input->getUint('id');
        $this->setState($this->getName() . '.id', $value);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param    integer $id   The id of the object to get.
     *
     * @return    mixed    Object on success, false on failure.
     *
     * @throws \RuntimeException
     */
    public function getItem($id = null)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.title, a.introtext, a.fulltext')
            ->from($db->quoteName('#__content', 'a'))
            ->where('a.id = ' . (int)$id);

        $db->setQuery($query);

        return $db->loadObject();
    }
}
