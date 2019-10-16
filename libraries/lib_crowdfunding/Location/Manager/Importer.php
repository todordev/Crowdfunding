<?php
/**
 * @package      Crowdfunding\Locations
 * @subpackage   Manager\Importers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Location\Manager;

use Prism\Utilities\FileHelper;
use Prism\Utilities\DatabaseHelper;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for parsing email template.
 *
 * @package      Crowdfunding\Locations
 * @subpackage   Manager\Importers
 */
class Importer
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
        $this->db = $db;
    }

    public function import($file, array $options)
    {
        if (is_file($file)) {
            $isMariaDb = DatabaseHelper::isMariaDB($this->db);

            if ($isMariaDb) {
                $this->dropIndexes();
            }

            $maxPopulation      = ArrayHelper::getValue($options, 'minimum_population', 0, 'int');
            $countryCodeOptions = ArrayHelper::getValue($options, 'country_code', '', 'cmd');

            $truncate = ArrayHelper::getValue($options, 'truncate', false, 'bool');
            if ($truncate) {
                $this->db->truncateTable('#__crowdf_locations');
            }

            $items   = array();
            $columns = array(
                'id', 'name',
                'latitude', 'longitude',
                'admin1_code', 'admin1code_id',
                'country_code', 'timezone'
            );

            $i = 0;
            foreach (FileHelper::getLine($file) as $key => $geodata) {
                $item = mb_split("\t", $geodata);

                // Check for missing ascii characters name
                $name = StringHelper::trim($item[2]);
                if (!$name) {
                    // If missing ascii characters name, use utf-8 characters name
                    $name = StringHelper::trim($item[1]);
                }

                // If missing name, skip the record
                if (!$name) {
                    continue;
                }

                // Filter by population.
                $population = (int)$item[14];
                if ($maxPopulation > $population) {
                    continue;
                }

                // Filter by country.
                $countryCode = StringHelper::trim($item[8]);
                if ($countryCodeOptions and strcmp($countryCodeOptions, $countryCode) !== 0) {
                    continue;
                }

                $admin1Code = StringHelper::trim($item[10]);

                $admin1CodeId = $admin1Code ? implode('.', [$countryCode, $admin1Code]) : '';

                $items[] =
                    (int)$item[0] . ',' . $this->db->quote($name) . ',' .
                    $this->db->quote(StringHelper::trim($item[4])) . ',' . $this->db->quote(StringHelper::trim($item[5])) . ',' .
                    $this->db->quote($admin1Code) . ',' . $this->db->quote($admin1CodeId) . ',' .
                    $this->db->quote($countryCode) . ',' . $this->db->quote(StringHelper::trim($item[17]));

                $i++;

                // Import 500 records.
                if ($i === 500) {
                    $i = 0;

                    $query = $this->db->getQuery(true);
                    $query
                        ->insert($this->db->quoteName('#__crowdf_locations'))
                        ->columns($this->db->quoteName($columns))
                        ->values($items);

                    $this->db->setQuery($query);
                    $this->db->execute();

                    $items   = array();
                }
            }

            // Import latest ones.
            if (count($items) > 0) {
                $query = $this->db->getQuery(true);

                $query
                    ->insert($this->db->quoteName('#__crowdf_locations'))
                    ->columns($this->db->quoteName($columns))
                    ->values($items);

                $this->db->setQuery($query);
                $this->db->execute();
            }

            unset($content, $items);

            if ($isMariaDb) {
                $this->addIndexes();
            }
        }
    }

    protected function dropIndexes()
    {
        $sql = '
        ALTER TABLE '.$this->db->quoteName('#__crowdf_locations').'
        DROP INDEX IF EXISTS '.$this->db->quoteName('idx_cflocations_name').',
        DROP INDEX IF EXISTS '.$this->db->quoteName('idx_cflocations_cca1cid').'
        ';

        $this->db->setQuery($sql);
        $this->db->execute();
    }

    protected function addIndexes()
    {
        $sql = '
        ALTER TABLE '.$this->db->quoteName('#__crowdf_locations').'
        ADD INDEX IF NOT EXISTS '.$this->db->quoteName('idx_cflocations_name').' ('.$this->db->quoteName('name').'),
        ADD INDEX IF NOT EXISTS '.$this->db->quoteName('idx_cflocations_cca1cid').' ('.$this->db->quoteName('country_code').','.$this->db->quoteName('admin1code_id').')
        ';

        $this->db->setQuery($sql);
        $this->db->execute();
    }
}
