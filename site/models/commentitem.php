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

class CrowdfundingModelCommentItem extends JModelItem
{
    protected $item = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableComment|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Comment', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var  $app JApplicationSite */

        // Load the object state.
        $value = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $value);

        $value = $app->input->getInt('project_id');
        $this->setState('project_id', $value);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param    integer $id The id of the object to get.
     *
     * @return    stdClass|null    Object on success, false on failure.
     * @throws \Exception
     */
    public function getItem($id = 0)
    {
        if ((int)$id === 0) {
            $id = (int)$this->getState($this->getName() . '.id');
        }

        if ($id === 0) {
            return null;
        }

        $storedId = $this->getStoreId($id);

        if (!array_key_exists($storedId, $this->item)) {
            $this->item[$storedId] = null;

            // Get a level row instance.
            $table = $this->getTable();
            $table->load($id);

            // Attempt to load the row.
            if ($table->get('id')) {
                $properties = $table->getProperties();
                $this->item[$storedId] = Joomla\Utilities\ArrayHelper::toObject($properties);

                unset($table);
            }
        }

        return $this->item[$storedId];
    }

    public function remove($itemId, $userId = 0)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_comments'))
            ->where($db->quoteName('id') .' = ' . (int)$itemId);

        if ((int)$userId > 0) {
            $query->where($db->quoteName('user_id') . '=' . (int)$userId);
        }

        $db->setQuery($query);
        $db->execute();
    }
}
