<?php
/**
 * @package      Crowdfunding
 * @subpackage   Updates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism;
use Prism\Database;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage updates.
 *
 * @package      Crowdfunding
 * @subpackage   Updates
 */
class Updates extends Database\Collection
{
    protected $allowedStates = array(Prism\Constants::SENT, Prism\Constants::NOT_SENT);

    /**
     * Load data about updates from database by project ID.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1, // It can also be an array with IDs.
     *     "period" => 7, // Period in days
     *     "limit" => 10 // Limit the results
     * );
     *
     * $updates   = new Crowdfunding\Updates(\JFactory::getDbo());
     * $updates->load($options);
     *
     * foreach($updates as $item) {
     *      echo $item->title;
     *      echo $item->record_date;
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.title, a.description, a.record_date, a.project_id')
            ->from($this->db->quoteName('#__crowdf_updates', 'a'));

        // Filter by IDs.
        $ids = ArrayHelper::getValue($options, 'ids', array(), 'array');
        if (count($ids) > 0) {
            $query->where('a.ids IN (' . implode(',', $ids) . ')');
        }

        // Filter by project ID.
        $projectId = ArrayHelper::getValue($options, 'project_id', 0, 'int');
        if ($projectId > 0) {
            $query->where('a.project_id = ' . (int)$projectId);
        }

        // Filter by period.
        $period = ArrayHelper::getValue($options, 'period', 0, 'int');
        if ($period > 0) {
            $query->where('a.record_date >= DATE_SUB(NOW(), INTERVAL '.$period.' DAY)');
        }

        // Set limit.
        $limit = ArrayHelper::getValue($options, 'limit', 0, 'int');
        if ($limit > 0) {
            $this->db->setQuery($query, 0, $limit);
        } else {
            $this->db->setQuery($query);
        }

        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Change the state of update records.
     *
     * <code>
     * $ids = array(1, 2, 3, 4, 5);
     *
     * $updates = new Crowdfunding\Updates(\JFactory::getDbo());
     * $updates->changeState($ids);
     * </code>
     *
     * @param int $state 1 = Sent; 0 = Not sent;
     * @param null|array $ids
     */
    public function changeState($state, array $ids = array())
    {
        if (count($ids) === 0 and count($this->items) > 0) {
            $ids = $this->getKeys();
        }

        if (!in_array($state, $this->allowedStates, true)) {
            $state = 0;
        }

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->update($this->db->quoteName('#__crowdf_updates'))
                ->set($this->db->quoteName('state') .'='. (int)$state)
                ->where($this->db->quoteName('id') .' IN ('.implode(',', $ids).')');

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Create a update object and return it.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1
     * );
     *
     * $updates   = new Crowdfunding\Updates\Updates(\JFactory::getDbo());
     * $updates->load($options);
     *
     * $updateId = 1;
     * $update = $updates->getUpdate($updateId);
     * </code>
     *
     * @param int $id Update ID.
     *
     * @throws \UnexpectedValueException
     *
     * @return null|Update
     */
    public function getUpdate($id)
    {
        if (!$id) {
            throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_UPDATE_ID'));
        }

        $update = null;

        foreach ($this->items as $item) {
            if ((int)$id === (int)$item['id']) {
                $update = new Update($this->db);
                $update->bind($item);
                break;
            }
        }

        return $update;
    }

    /**
     * Return the updates as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $updates   = new Crowdfunding\Update\Updates(\JFactory::getDbo());
     * $updates->load($options);
     *
     * $updates = $updates->getUpdates();
     * </code>
     *
     * @return array
     */
    public function getUpdates()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $update = new Update($this->db);
            $update->bind($item);
            
            $results[$i] = $update;
            $i++;
        }

        return $results;
    }
}
