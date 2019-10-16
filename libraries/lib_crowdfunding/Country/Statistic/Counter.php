<?php
/**
 * @package      Crowdfunding
 * @subpackage   Filters
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Country\Statistic;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare countries as options.
 *
 * @package      Crowdfunding
 * @subpackage   Filters
 */
class Counter
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;
    
    /**
     * Initialize the object.
     *
     * @param \JDatabaseDriver $db
     */
    public function __construct(\JDatabaseDriver $db = null)
    {
        $this->db     = $db;
    }
    
    /**
     * Return number of regions in a country.
     *
     * <code>
     * $countryCodes = array(
     *     "US", "UK", "BG", "DE"
     * );
     *
     * $countryStatistics = new Crowdfunding\Country\Statistic\Counter(\JFactory::getDbo());
     * $regionsNumber     = $countryStatistics->countRegions($countryCodes);
     * </code>
     *
     * @param array $countryCodes
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function countRegions(array $countryCodes = array())
    {
        $results = array();
        
        if (count($countryCodes) > 0) {
            $quotedCodes = (array)$this->db->quote($countryCodes);

            $query = $this->db->getQuery(true);

            $query
                ->select('a.country_code, COUNT(*) as number')
                ->from($this->db->quoteName('#__crowdf_regions', 'a'))
                ->where('a.country_code IN (' . implode(',', $quotedCodes) . ')')
                ->group('a.country_code');

            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList('country_code', 'number');
        }

        return $results;
    }
}
