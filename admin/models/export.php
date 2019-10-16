<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelExport extends JModelLegacy
{
    public function getCurrencies()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select('a.id, a.title, a.code, a.symbol, a.position')
            ->from($db->quoteName('#__crowdf_currencies', 'a'));


        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $this->prepareXML($results, 'currencies', 'currency');
    }

    public function getLocations()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select(
                'a.id, a.name, a.latitude, a.longitude, a.country_code, ' .
                'a.timezone, a.state_code, a.published'
            )
            ->from($db->quoteName('#__crowdf_locations', 'a'));


        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $this->prepareXML($results, 'locations', 'location');
    }

    public function getStates()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select('a.id, a.name, a.state_code')
            ->from($db->quoteName('#__crowdf_locations', 'a'));

        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $this->prepareXML($results, 'states', 'state');
    }

    public function getCountries()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select('a.id, a.name, a.code, a.locale, a.latitude, a.longitude, a.timezone')
            ->from($db->quoteName('#__crowdf_countries', 'a'));

        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $this->prepareXML($results, 'countries', 'country');
    }

    protected function prepareXML($results, $root, $child)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><' . $root . '/>');
        $xml->addAttribute('generator', 'com_crowdfunding');

        if (!empty($root) and !empty($child)) {
            foreach ($results as $data) {
                $item = $xml->addChild($child);

                foreach ($data as $key => $value) {
                    $item->addChild($key, $value);
                }
            }
        }

        $dom               = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
