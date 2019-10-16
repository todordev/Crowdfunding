<?php
/**
 * @package      Crowdfunding
 * @subpackage   Regions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Region;

use Prism\Database;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare countries as options.
 *
 * @package      Crowdfunding
 * @subpackage   Regions
 */
class Regions extends Database\Collection
{
    /**
     * Load countries data from database.
     *
     * <code>
     * $options = array(
     *    'country_code' => 'BG'
     * );
     *
     * $regions = new Crowdfunding\Region\Regions(\JFactory::getDbo());
     * $regions->load($options);
     * </code>
     *
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load(array $options = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.country_code, a.admincode_id')
            ->from($this->db->quoteName('#__crowdf_regions', 'a'));

        // Filter by Region Code
        $regionCode = array_key_exists('country_code', $options) ? $options['country_code'] : '';
        if ($regionCode !== '') {
            $query->where('a.country_code = ' . $this->db->quote($regionCode));
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Create a region object and return it.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $regions   = new Crowdfunding\Region\Regions(\JFactory::getDbo());
     * $regions->load($options);
     *
     * $regionId = 1;
     * $region   = $regions->getRegion($regionId);
     * </code>
     *
     * @param int|string $id Region ID or Region code.
     *
     * @throws \UnexpectedValueException
     *
     * @return null|Region
     */
    public function getRegion($id)
    {
        if (!$id) {
            throw new \UnexpectedValueException('Invalid region ID');
        }
        
        $region = null;

        foreach ($this->items as $item) {
            if (is_numeric($id) and (int)$item['id'] === (int)$id) {
                $region = new Region($this->db);
                $region->bind($item);
                break;
            } elseif (strcmp($id, $item['code']) === 0) {
                $region = new Region($this->db);
                $region->bind($item);
                break;
            }
        }

        return $region;
    }

    /**
     * Return the regions as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $regions   = new Crowdfunding\Region\Regions(\JFactory::getDbo());
     * $regions->load($options);
     *
     * $regions = $regions->getRegions();
     * </code>
     *
     * @return array
     */
    public function getRegions()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $region = new Region($this->db);
            $region->bind($item);

            $results[$i] = $region;
            $i++;
        }

        return $results;
    }
}
