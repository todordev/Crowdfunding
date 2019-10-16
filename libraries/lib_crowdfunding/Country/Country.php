<?php
/**
 * @package      Crowdfunding
 * @subpackage   Countries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Country;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a country.
 *
 * @package      Crowdfunding
 * @subpackage   Countries
 */
class Country extends Prism\Database\TableImmutable
{
    protected $id;
    protected $name;
    protected $code;
    protected $locale;
    protected $latitude;
    protected $longitude;
    protected $currency;
    protected $timezone;

    /**
     * Load country data from database.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
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
            ->select('a.id, a.name, a.code, a.locale, a.latitude, a.longitude, a.currency, a.code')
            ->from($this->db->quoteName('#__crowdf_countries', 'a'));

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
     * $countryId  = 1;
     *
     * $country    = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($typeId);
     *
     * if (!$country->getId()) {
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
     * Return country code.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $countryCode = $country->getCode();
     * </code>
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return 4 symbols country code (en_GB).
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $countryCode = $country->getCode4();
     * </code>
     *
     * @return string
     * @deprecated since 2.7
     */
    public function getCode4()
    {
        return $this->locale;
    }

    /**
     * Return locale code of the  country.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $countryCode = $country->getLocale();
     * </code>
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Return country name.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $name = $country->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return country latitude.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $latitude = $country->getLatitude();
     * </code>
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Return country longitude.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $longitude = $country->getLongitude();
     * </code>
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Return country currency code (GBP).
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $currency = $country->getCurrency();
     * </code>
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Return country timezone.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new Crowdfunding\Country\Country(\JFactory::getDbo());
     * $country->load($countryId);
     *
     * $timezone = $country->getTimezone();
     * </code>
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
