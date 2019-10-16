<?php
/**
 * @package      Crowdfunding
 * @subpackage   Countries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Country;

use Prism\Database;
use Joomla\Utilities\ArrayHelper;
use Prism\Utilities\ArrayHelper as PrismArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare countries as options.
 *
 * @package      Crowdfunding
 * @subpackage   Countries
 */
class Countries extends Database\Collection
{
    protected static $instance;

    /**
     * Create an object and return the instance.
     *
     * <code>
     * $countries = Crowdfunding\Country\Countries::getInstance(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param array  $options
     *
     * @return null|self
     *
     * @deprecated v2.6 Use Container
     */
    public static function getInstance(\JDatabaseDriver $db, array $options = array())
    {
        if (self::$instance === null) {
            self::$instance = new Countries($db);
            self::$instance->load($options);
        }

        return self::$instance;
    }

    /**
     * Load countries data from database.
     *
     * <code>
     * $countries = new Crowdfunding\Country\Countries(\JFactory::getDbo());
     * $countries->load();
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        // Get columns
        $defaultColumns = array('a.id', 'a.name', 'a.code', 'a.locale', 'a.latitude', 'a.longitude', 'a.currency', 'a.timezone');
        $columns = (array_key_exists('columns', $options) and is_array($options['columns'])) ? $options['columns'] : $defaultColumns;
        
        // Get IDs.
        $ids = (array_key_exists('ids', $options) and is_array($options['ids'])) ? $options['ids'] : array();
        $ids = ArrayHelper::toInteger($ids);

        $query = $this->db->getQuery(true);

        $query
            ->select(implode(',', $columns))
            ->from($this->db->quoteName('#__crowdf_countries', 'a'));

        // Filter by IDs
        if (count($ids) > 0) {
            $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Create a country object and return it.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $countries   = new Crowdfunding\Country\Countries(\JFactory::getDbo());
     * $countries->load($options);
     *
     * $countryId = 1;
     * $country   = $countries->getCountry($countryId);
     * </code>
     *
     * @param int|string $id Country ID or Country code.
     *
     * @throws \UnexpectedValueException
     *
     * @return null|Country
     */
    public function getCountry($id)
    {
        if (!$id) {
            throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_COUNTRY_ID'));
        }
        
        $country = null;

        foreach ($this->items as $item) {
            if (is_numeric($id) and (int)$item['id'] === (int)$id) {
                $country = new Country($this->db);
                $country->bind($item);
                break;
            } elseif (strcmp($id, $item['code']) === 0) {
                $country = new Country($this->db);
                $country->bind($item);
                break;
            }
        }

        return $country;
    }

    /**
     * Return the countries as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $countries   = new Crowdfunding\Country\Countries(\JFactory::getDbo());
     * $countries->load($options);
     *
     * $countries = $countries->getCountries();
     * </code>
     *
     * @return array
     */
    public function getCountries()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $country = new Country($this->db);
            $country->bind($item);

            $results[$i] = $country;
            $i++;
        }

        return $results;
    }
}
