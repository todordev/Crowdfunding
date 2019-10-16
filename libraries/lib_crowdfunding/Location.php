<?php
/**
 * @package      Crowdfunding
 * @subpackage   Locations
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a location.
 *
 * @package      Crowdfunding
 * @subpackage   Locations
 */
class Location extends Prism\Database\TableImmutable
{
    protected $id;
    protected $name;
    protected $latitude;
    protected $longitude;
    protected $country_code;
    protected $timezone;
    protected $published;

    /**
     * Load location data from database.
     *
     * <code>
     * $keys = array(
     *     "id" => 1,
     *     "country_code" => "UK"
     * );
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.latitude, a.longitude, a.country_code, a.timezone, a.published')
            ->from($this->db->quoteName('#__crowdf_locations', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Return location ID.
     *
     * <code>
     * $locationId  = 1;
     *
     * $location    = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * if (!$location->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Return a country code of the location.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * $locationCode = $location->getCountryCode();
     * </code>
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Return location name.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * $name = $location->getName();
     * </code>
     *
     * @param bool $includeCountryCode Include or not the country code to the name.
     *
     * @return string
     */
    public function getName($includeCountryCode = false)
    {
        return (!$includeCountryCode) ? $this->name : $this->name . ', ' . $this->country_code;
    }

    /**
     * Return location latitude.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * $latitude = $location->getLatitude();
     * </code>
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Return location longitude.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * $longitude = $location->getLongitude();
     * </code>
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Check if the location is published.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * if (!$location->isPublished()) {
     * ....
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return (bool)((int)$this->published === Prism\Constants::PUBLISHED);
    }

    /**
     * Return location timezone.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new Crowdfunding\Location(\JFactory::getDbo());
     * $location->load($locationId);
     *
     * $timezone = $location->getTimezone();
     * </code>
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
