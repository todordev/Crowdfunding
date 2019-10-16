<?php
/**
 * @package      Crowdfunding
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage categories.
 *
 * @package      Crowdfunding
 * @subpackage   Categories
 *
 * @deprecated v2.6
 */
class Categories extends \JCategories
{
    /**
     * The property that contains categories.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    public function __construct($options = array())
    {
        $options['table']     = '#__crowdf_projects';
        $options['extension'] = 'com_crowdfunding';
        parent::__construct($options);
    }

    /**
     * Set database object.
     *
     * <code>
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(\JDatabaseDriver $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Count and return the number of subcategories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $number = $categories->getChildNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getChildNumber(array $ids, array $options = array())
    {
        $ids = ArrayHelper::toInteger($ids);

        $result = array();

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.parent_id, COUNT(*) as number')
                ->from($this->db->quoteName('#__categories', 'a'))
                ->group('a.parent_id')
                ->where('a.parent_id IN (' . implode(',', $ids) . ')');

            // Filter by state.
            $state = ArrayHelper::getValue($options, 'state');
            if ($state !== null) {
                $query->where('a.published = ' . (int)$state);
            } else {
                $query->where('a.published IN (0,1)');
            }

            $this->db->setQuery($query);
            $result = (array)$this->db->loadAssocList('parent_id');
        }

        return $result;
    }

    /**
     * Count and return the number of projects in categories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $number = $categories->getProjectsNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getProjectsNumber(array $ids = array(), array $options = array())
    {
        $ids = ArrayHelper::toInteger($ids);

        // Get the ids from the current items.
        if (!$ids and count($this->data) > 0) {
            foreach ($this->data as $category) {
                $ids[] = $category['id'];
            }
        }

        $results = array();

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.catid, COUNT(*) as number')
                ->from($this->db->quoteName('#__crowdf_projects', 'a'))
                ->group('a.catid')
                ->where('a.catid IN (' . implode(',', $ids) . ')');

            // Filter by state.
            $state = ArrayHelper::getValue($options, 'state');
            if ($state !== null) {
                $query->where('a.published = ' . (int)$state);
            } else {
                $query->where('a.published IN (0,1)');
            }

            // Filter by approve state.
            $approved = ArrayHelper::getValue($options, 'approved');
            if ($approved !== null) {
                $query->where('a.approved = ' . (int)$approved);
            } else {
                $query->where('a.approved IN (0,1)');
            }

            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList('catid');
        }

        return $results;
    }

    /**
     * Load categories.
     *
     * <code>
     * $parentId = 2;
     *
     * $options = array(
     *    "offset" => 0,
     *    "limit" => 10,
     *    "order_by" => "a.name",
     *    "order_dir" => "DESC",
     * );
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $categories->load($parentId);
     * </code>
     *
     * @param null|int $parentId Parent ID or "root".
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load($parentId = null, array $options = array())
    {
        $offset    = array_key_exists('offset', $options) ? $options['offset'] : 0;
        $limit     = array_key_exists('limit', $options) ? $options['limit'] : 0;
        $orderBy   = array_key_exists('order_by', $options) ? $options['order_by'] : 'a.title';
        $orderDir  = array_key_exists('order_dir', $options) ? $options['order_dir'] : 'ASC';
        $published = array_key_exists('state', $options) ? $options['state'] : null;

        $orderDir  = strtoupper($orderDir);

        if (!in_array($orderDir, array('ASC', 'DESC'), true)) {
            $orderDir = 'ASC';
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                'a.id, a.title, a.alias, a.description, a.params, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug'
            )
            ->from($this->db->quoteName('#__categories', 'a'))
            ->where('a.extension = '. $this->db->quote($this->_extension));

        if ($parentId !== null) {
            $query->where('a.parent_id = '. (int)$parentId);
        }

        // Filter by state.
        if ($published === null) {
            $query->where('a.published IN (0,1)');
        } else {
            $query->where('a.published = '. (int)$published);
        }
        
        $query->order($this->db->escape($this->db->quoteName($orderBy) . ' ' . $orderDir));

        $this->db->setQuery($query, (int)$offset, (int)$limit);

        $this->data = (array)$this->db->loadAssocList('id');
    }

    /**
     * Return the elements as an array.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $categories = $categories->toArray();
     * </code>
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->data;
    }
}
