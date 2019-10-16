<?php
/**
 * @package      Crowdfunding
 * @subpackage   Locations
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism\Database;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage locations.
 *
 * @package      Crowdfunding
 * @subpackage   Locations
 */
class Locations extends Database\Collection
{
    /**
     * Load locations data by ID from database.
     *
     * <code>
     * $options = array(
     *      "ids" => array(1,2,3,4,5)
     * );
     *
     * $locations   = new Crowdfunding\Locations(\JFactory::getDbo());
     * $locations->load($options);
     *
     * foreach($locations as $location) {
     *   echo $location["id"];
     *   echo $location["name"];
     * }
     *
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.latitude, a.longitude, a.country_code, a.timezone, a.published')
            ->from($this->db->quoteName('#__crowdf_locations', 'a'));

        $ids = (array_key_exists('ids', $options) and is_array($options['ids'])) ? $options['ids'] : array();
        $ids = ArrayHelper::toInteger($ids);
        if (count($ids) > 0) {
            $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Load locations data by string from database.
     *
     * <code>
     * $options = array(
     *   'query' => "Plovdiv",
     *   'mode'  => 1
     * )
     *
     * $locations   = new Crowdfunding\Locations(\JFactory::getDbo());
     * $locations->search($options);
     *
     * foreach($locations as $location) {
     *   echo $location["id"];
     *   echo $location["name"];
     * }
     * </code>
     *
     * @param array $options
     *
     * Example:
     *
     * # Filter modes
     * 0 = "string";
     * 1 = "string%";
     * 2 = "%string";
     * 3 = "%string%";
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function search(array $options = array())
    {
        $qString = ArrayHelper::getValue($options, 'query', '', 'string');
        $mode    = ArrayHelper::getValue($options, 'mode', 0, 'int');
        $start   = ArrayHelper::getValue($options, 'start', 0, 'int');
        $limit   = ArrayHelper::getValue($options, 'limit', 5, 'int');

        $query  = $this->db->getQuery(true);

        switch ($mode) {
            case 1: // Beginning
                $searchFilter = $this->db->escape($qString, true) . '%';
                break;

            case 2: // End
                $searchFilter =  '%'. $this->db->escape($qString, true);
                break;

            case 3: // Both
                $searchFilter =  '%' . $this->db->escape($qString, true) . '%';
                break;

            default: // NONE
                $searchFilter = $this->db->escape($qString, true);
                break;
        }

        $search = $this->db->quote($searchFilter);

        $query
            ->select(
                'a.id, ' .
                $query->concatenate(array('a.name', 'a.country_code'), ', ') . ' AS name'
            )
            ->from($this->db->quoteName('#__crowdf_locations', 'a'))
            ->where($this->db->quoteName('a.name') . ' LIKE ' . $search);

        $this->db->setQuery($query, $start, $limit);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Load locations data by string from database.
     *
     * <code>
     * $string = "Plovdiv";
     *
     * $locations   = new Crowdfunding\Locations(\JFactory::getDbo());
     * $locations->loadByString($string);
     *
     * foreach($locations as $location) {
     *   echo $location["id"];
     *   echo $location["name"];
     * }
     * </code>
     *
     * @param string $string
     * @param int $mode  Filter mode.
     *
     * Example:
     *
     * # Filter modes
     * 0 = "string";
     * 1 = "string%";
     * 2 = "%string";
     * 3 = "%string%";
     *
     * @deprecated v2.5
     */
    public function loadByString($string, $mode = 1)
    {
        $options = array(
            'query' => $string,
            'mode'  => $mode
        );

        $this->search($options);
    }

    /**
     * Create a location object and return it.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $locations   = new Crowdfunding\Location\Locations(\JFactory::getDbo());
     * $locations->load($options);
     *
     * $locationId = 1;
     * $location   = $locations->getLocation($locationId);
     * </code>
     *
     * @param int|string $id Location ID or location name.
     *
     * @throws \UnexpectedValueException
     *
     * @return null|Location
     */
    public function getLocation($id)
    {
        if (!$id) {
            throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_LOCATION_ID'));
        }

        $location = null;

        foreach ($this->items as $item) {
            if (is_numeric($id) and (int)$id === (int)$item['id']) {
                $location = new Location($this->db);
                $location->bind($item);
                break;
            } elseif (strcmp($id, $item['name']) === 0) {
                $location = new Location($this->db);
                $location->bind($item);
                break;
            }
        }

        return $location;
    }

    /**
     * Return the locations as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $locations   = new Crowdfunding\Location\Locations(\JFactory::getDbo());
     * $locations->load($options);
     *
     * $items = $locations->getLocations();
     * </code>
     *
     * @return array
     */
    public function getLocations()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $location = new Location($this->db);
            $location->bind($item);

            $results[$i] = $location;
            $i++;
        }

        return $results;
    }
}
