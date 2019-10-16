<?php
/**
 * @package      Crowdfunding
 * @subpackage   Regions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Region;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a region.
 *
 * @package      Crowdfunding
 * @subpackage   Regions
 */
class Region extends Prism\Database\TableImmutable
{
    protected $id;
    protected $name;
    protected $country_code;
    protected $admincode_id;

    /**
     * Load region data from database.
     *
     * <code>
     * $regionId = 1;
     *
     * $region   = new Crowdfunding\Region\Region(\JFactory::getDbo());
     * $region->load($regionId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.country_code, a.admincode_id')
            ->from($this->db->quoteName('#__crowdf_regions', 'a'));

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
     * Return country ID.
     *
     * <code>
     * $regionId  = 1;
     *
     * $region    = new Crowdfunding\Region\Region(\JFactory::getDbo());
     * $region->load($typeId);
     *
     * if (!$region->getId()) {
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
     * Return region code.
     *
     * <code>
     * $regionId = 1;
     *
     * $region   = new Crowdfunding\Region\Region(\JFactory::getDbo());
     * $region->load($regionId);
     *
     * $regionCode = $region->getCountryCode();
     * </code>
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Return region name.
     *
     * <code>
     * $regionId = 1;
     *
     * $region   = new Crowdfunding\Region\Region(\JFactory::getDbo());
     * $region->load($regionId);
     *
     * $name = $region->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Return administrative code ID.
     *
     * <code>
     * $regionId = 1;
     *
     * $region   = new Crowdfunding\Region\Region(\JFactory::getDbo());
     * $region->load($regionId);
     *
     * echo $region->getAdminCodeId();
     * </code>
     *
     * @return string
     */
    public function getAdminCodeId()
    {
        return $this->admincode_id;
    }
}
