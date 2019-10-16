<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics\Categories;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for categories statistics.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
abstract class Base
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
     * <code>
     * $statistics   = new Crowdfunding\Statistics\Categories\Latest(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db  Database Driver
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id, a.title, a.description, a.params ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug '
            )
            ->from($this->db->quoteName('#__categories', 'a'))
            ->where('a.extension = ' . $this->db->quote('com_crowdfunding'));

        return $query;
    }
}
