<?php
/**
 * @package      Crowdfunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\User;

use Prism;
use Prism\Database;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage locations.
 *
 * @package      Crowdfunding
 * @subpackage   Users
 */
class Users extends Database\Collection
{
    /**
     * Load users data from database.
     *
     * <code>
     * $options = array(
     *      "ids" => array(1,2,3,4,5)
     * );
     *
     * $users   = new Crowdfunding\User\Users(\JFactory::getDbo());
     * $users->load($options);
     *
     * foreach($users as $user) {
     *   echo $user["id"];
     *   echo $user["name"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        // Filter by users IDs.
        $ids = ArrayHelper::getValue($options, 'ids', array(), 'array');
        $ids = ArrayHelper::toInteger($ids);

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.id, a.name, a.email')
                ->from($this->db->quoteName('#__users', 'a'));

            $query->where('a.id IN (' . implode(',', $ids) . ')');

            $this->db->setQuery($query);

            $this->items = (array)$this->db->loadAssocList();
        }
    }

    /**
     * Return user object.
     *
     * <code>
     * $options = array(
     *      "ids" => array(1,2,3,4,5)
     * );
     *
     * $users   = new Crowdfunding\User\Users(\JFactory::getDbo());
     * $users->load($options);
     *
     * $userId = 1;
     * $user = $users->getUser($userId);
     * </code>
     *
     * @param int $id
     *
     * @return null|User
     */
    public function getUser($id)
    {
        $user   = null;

        foreach ($this->items as $item) {
            if ((int)$id === (int)$item['id']) {
                $user = new User($this->db);
                $user->bind($item);
                break;
            }
        }

        return $user;
    }

    /**
     * Return the users as array with objects.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4,5)
     * );
     *
     * $users   = new Crowdfunding\User\Users(\JFactory::getDbo());
     * $users->load($options);
     *
     * $items = $users->getUpdates();
     * </code>
     *
     * @return array
     */
    public function getUsers()
    {
        $results = array();

        $i = 0;
        foreach ($this->items as $item) {
            $user = new User($this->db);
            $user->bind($item);

            $results[$i] = $user;
            $i++;
        }

        return $results;
    }
}
