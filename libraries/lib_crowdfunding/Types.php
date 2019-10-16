<?php
/**
 * @package      Crowdfunding
 * @subpackage   Types
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism\Database;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing types.
 *
 * @package      Crowdfunding
 * @subpackage   Types
 */
class Types extends Database\Collection
{
    protected static $instance;

    /**
     * Initialize and create an object.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = Crowdfunding\Types::getInstance(\JFactory::getDbo(), $options);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param array $options
     *
     * @return self
     */
    public static function getInstance(\JDatabaseDriver $db, array $options = array())
    {
        if (self::$instance === null) {
            self::$instance = new Types($db);
            self::$instance->load($options);
        }

        return self::$instance;
    }

    /**
     * Load types data from database.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = new Crowdfunding\Types();
     * $types->setDb(\JFactory::getDbo());
     * $types->load($options);
     *
     * foreach ($types as $type) {
     *      echo $type["title"];
     *      echo $type["description"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load(array $options = array())
    {
        $orderColumn    = $this->getOptionOrderColumn($options);
        $orderDirection = $this->getOptionOrderDirection($options);

        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.title, a.description, a.params')
            ->from($this->db->quoteName('#__crowdf_types', 'a'));

        // Order by column
        if ($orderColumn !== '') {
            $orderString = $orderColumn;

            // Order direction
            if ($orderDirection !== '') {
                $orderString .= $orderDirection;
            }

            $orderString = $this->db->escape($orderString);
            $query->order($orderString);
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Create a type object and return it.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $types   = new Crowdfunding\Type\Types(\JFactory::getDbo());
     * $types->load($options);
     *
     * $typeId = 1;
     * $type   = $types->getType($typeId);
     * </code>
     *
     * @param int $id Type ID.
     *
     * @throws \UnexpectedValueException
     *
     * @return null|Type
     */
    public function getType($id)
    {
        if (!$id) {
            throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_TYPE_ID'));
        }

        $type = null;

        foreach ($this->items as $item) {
            if ((int)$id === (int)$item['id']) {
                $type = new Type($this->db);
                $type->bind($item);
                break;
            }
        }

        return $type;
    }

    /**
     * Return the types as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $types   = new Crowdfunding\Type\Types(\JFactory::getDbo());
     * $types->load($options);
     *
     * $types = $types->getTypes();
     * </code>
     *
     * @return array
     */
    public function getTypes()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $type = new Type($this->db);
            $type->bind($item);
            
            $results[$i] = $type;
            $i++;
        }

        return $results;
    }
}
