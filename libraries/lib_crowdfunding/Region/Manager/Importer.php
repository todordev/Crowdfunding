<?php
/**
 * @package      Crowdfunding\Regions
 * @subpackage   Manager\Importers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Region\Manager;

use Prism\Utilities\FileHelper;
use Prism\Utilities\DatabaseHelper;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for parsing email template.
 *
 * @package      Crowdfunding\Regions
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

            $countryCodeOptions = ArrayHelper::getValue($options, 'country_code', '', 'cmd');

            $truncate = ArrayHelper::getValue($options, 'truncate', false, 'bool');
            if ($truncate) {
                $this->db->truncateTable('#__crowdf_regions');
            }

            $items   = array();
            $columns = array(
                'id', 'name',
                'country_code', 'admincode_id'
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

                $adminCodeId = StringHelper::trim($item[0]);

                // Filter by country.
                list($countryCode, $adminId) = explode('.', $adminCodeId);
                if ($countryCodeOptions and strcmp($countryCodeOptions, $countryCode) !== 0) {
                    continue;
                }

                $items[] =
                    (int)$item[3] . ',' . $this->db->quote($name) . ',' .
                    $this->db->quote($countryCode) . ',' . $this->db->quote($adminCodeId);

                $i++;

                // Import 500 records.
                if ($i === 500) {
                    $i = 0;

                    $query = $this->db->getQuery(true);
                    $query
                        ->insert($this->db->quoteName('#__crowdf_regions'))
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
                    ->insert($this->db->quoteName('#__crowdf_regions'))
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
        ALTER TABLE '.$this->db->quoteName('#__crowdf_regions').'
        DROP INDEX IF EXISTS '.$this->db->quoteName('idx_cfregions_acid').',
        DROP INDEX IF EXISTS '.$this->db->quoteName('idx_cfregions_cc').',
        DROP INDEX IF EXISTS '.$this->db->quoteName('idx_cfregions_name')
        ;

        $this->db->setQuery($sql);
        $this->db->execute();
    }

    protected function addIndexes()
    {
        $sql = '
        ALTER TABLE '.$this->db->quoteName('#__crowdf_regions').'
        ADD UNIQUE INDEX IF NOT EXISTS '.$this->db->quoteName('idx_cfregions_acid').' ('.$this->db->quoteName('admincode_id').'),
        ADD INDEX IF NOT EXISTS '.$this->db->quoteName('idx_cfregions_cc').' ('.$this->db->quoteName('country_code').'),
        ADD INDEX IF NOT EXISTS '.$this->db->quoteName('idx_cfregions_name').' ('.$this->db->quoteName('name').')
        ';

        $this->db->setQuery($sql);
        $this->db->execute();
    }
}
