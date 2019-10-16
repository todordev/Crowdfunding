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

class CrowdfundingModelDetails extends JModelItem
{
    protected $item = array();

    /**
     * Model context string.
     *
     * @var    string
     * @since  11.1
     */
    protected $context = 'com_crowdfunding.details';

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableProject|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdfundingTable', $config = array())
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
        /** @var  $app JApplicationSite */

        $id = $app->input->getUint('id');
        $this->setState($this->context . '.id', $id);

        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param   int $id The id of the object to get.
     *
     * @throws \RuntimeException
     * @return mixed    Object on success, false on failure.
     */
    public function getItem($id = 0)
    {
        if ((int)$id === 0) {
            $id = $this->getState($this->context . '.id');
        }
        $storedId = $this->getStoreId($id);

        if (array_key_exists($storedId, $this->item)) {
            return $this->item[$storedId];
        }

        $this->item[$storedId] = null;

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(
                'a.id, a.title, a.short_desc, a.description, a.image, a.location_id, ' .
                'a.funded, a.goal, a.pitch_video, a.pitch_image, a.params, ' .
                'a.funding_start, a.funding_end, a.funding_days, a.funding_type,  ' .
                'a.catid, a.user_id, a.published, a.approved, a.hits, a.access, ' .
                'c.access AS category_access, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug'
            )
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
            ->where('a.id = ' . (int)$id);

        $db->setQuery($query, 0, 1);
        $this->item[$storedId] = $db->loadObject();

        return $this->item[$storedId];
    }

    /**
     * Check for valid owner.
     * If the project is not published and not approved,
     * only the owner will be able to view the project.
     *
     * @param stdClass $item
     * @param integer  $userId
     *
     * @return bool
     */
    public function isAllowed($item, $userId)
    {
        if (!is_object($item) || (!$item->id || !$item->user_id)) {
            return (bool)Prism\Constants::NOT_ALLOWED;
        }

        // Check for the owner of the project.
        // If it is not published and not approved, only the owner will be able to view the project.
        if ((!$item->published || !$item->approved) && ((int)$item->user_id === (int)$userId)) {
            return (bool)Prism\Constants::ALLOWED;
        }

        return (bool)$item->params->get('access-view');
    }

    /**
     * Increase number of hits.
     *
     * @param integer $id
     *
     * @throws \RuntimeException
     */
    public function hit($id)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName('#__crowdf_projects'))
            ->set($db->quoteName('hits') . ' = hits + 1')
            ->where($db->quoteName('id') . '=' . (int)$id);

        $db->setQuery($query);
        $db->execute();
    }
}
