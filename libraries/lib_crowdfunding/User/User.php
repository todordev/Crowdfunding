<?php
/**
 * @package      Crowdfunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\User;

use Joomla\Utilities\ArrayHelper;
use Prism\Database\TableImmutable;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage user profile.
 *
 * @package      Crowdfunding
 * @subpackage   Users
 */
class User extends TableImmutable
{
    protected $id;
    protected $name;
    protected $email;

    /**
     * User rewards.
     *
     * @var array
     */
    protected $rewards;

    /**
     * List with followed campaigns.
     *
     * @var null|array
     */
    protected $followed;

    protected static $instances = array();

    /**
     * Create an object or return existing one.
     *
     * <code>
     * $userId = 1;
     *
     * $currency   = Crowdfunding\User\User::getInstance(\JFactory::getDbo(), $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param int             $id
     *
     * @return null|self
     */
    public static function getInstance(\JDatabaseDriver $db, $id)
    {
        if (!array_key_exists($id, self::$instances)) {
            $item = new User($db);
            $item->load($id);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load user data from database by ID.
     *
     * <code>
     * $userId = 1;
     *
     * $user   = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.name, a.email')
            ->from($this->db->quoteName('#__users', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName($key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * if (!$user->getId()) {
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
     * Set user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->setId($userId)
     * </code>
     *
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * $name = $user->getName();
     * </code>
     *
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * $email = $user->getEmail();
     * </code>
     *
     * @return int
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return list with followed campaigns.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $followedCampaigns = $user->getFollowed()
     * </code>
     *
     * @return array
     */
    public function getFollowed()
    {
        if ($this->followed === null) {
            $query = $this->db->getQuery(true);
            $query
                ->select('a.project_id')
                ->from($this->db->quoteName('#__crowdf_followers', 'a'))
                ->where('a.user_id = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->followed = (array)$this->db->loadColumn();
            $this->followed = ArrayHelper::toInteger($this->followed);
        }

        return $this->followed;
    }

    /**
     * Start following campaign.
     *
     * <code>
     * $userId  = 1;
     * $projectId  = 2;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $user->follow($projectId)
     * </code>
     *
     * @param int $projectId
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function follow($projectId)
    {
        if (!$this->id) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_USER'));
        }

        if (!$projectId) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_PROJECT'));
        }

        $followed = $this->getFollowed();

        if (is_array($this->followed) and !in_array($projectId, $followed, true)) {
            $query = $this->db->getQuery(true);
            $query
                ->insert($this->db->quoteName('#__crowdf_followers'))
                ->set($this->db->quoteName('user_id')    .'='. (int)$this->id)
                ->set($this->db->quoteName('project_id') .'='. (int)$projectId);


            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Stop follow a campaign.
     *
     * <code>
     * $userId  = 1;
     * $projectId  = 2;
     *
     * $user    = new Crowdfunding\User\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $user->unfollow($projectId)
     * </code>
     *
     * @param int $projectId
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function unfollow($projectId)
    {
        if (!$this->id) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_USER'));
        }

        if (!$projectId) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_PROJECT'));
        }

        $followed = $this->getFollowed();

        if (is_array($this->followed) and in_array($projectId, $followed, true)) {
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName('#__crowdf_followers'))
                ->where($this->db->quoteName('user_id')    .'='. (int)$this->id)
                ->where($this->db->quoteName('project_id') .'='. (int)$projectId);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }
}
