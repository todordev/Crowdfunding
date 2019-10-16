<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage intentions.
 *
 * @package      Crowdfunding
 * @subpackage   Intentions
 */
class Intention extends Prism\Database\Table
{
    protected $id;
    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;

    /**
     * This is a unique string where is stored a unique key from a payment gateway.
     * That can be transaction ID, token,...
     *
     * @var mixed
     */
    protected $unique_key;

    /**
     * Load intention data from database.
     *
     * <code>
     * $keys = array(
     *  "user_id" => 1
     * );
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($keys);
     * </code>
     *
     * @param int|array $keys Intention keys.
     * @param array $options
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.user_id, a.project_id, a.reward_id, a.record_date')
            ->from($this->db->quoteName('#__crowdf_intentions', 'a'));

        if (!is_array($keys)) {
            $query->where('a.id = ' . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) . '=' . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Store data to database.
     *
     * <code>
     * $data = array(
     *  "user_id" => 1,
     *  "gateway" => "PayPal"
     * );
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->bind($data);
     * $intention->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_intentions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($this->user_id))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($this->project_id))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($this->reward_id))
            ->where($this->db->quoteName('id') .'='. (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $recordDate   = (!$this->record_date) ? 'NULL' : $this->db->quote($this->record_date);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__crowdf_intentions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($this->user_id))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($this->project_id))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName('record_date') . '=' . $recordDate);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Remove intention record from database.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     * $intention->delete();
     * </code>
     */
    public function delete()
    {
        $query = $this->db->getQuery(true);

        $query
            ->delete($this->db->quoteName('#__crowdf_intentions'))
            ->where($this->db->quoteName('id') .'='. (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->reset();
    }

    /**
     * Return intention ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * if (!$intention->getId()) {
     * ...
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
     * Set the ID of intention.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     *
     * $intention->setId($intentionId);
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
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $userId = $intention->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $projectId = $intention->getProjectIdUserId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $rewardId = $intention->getRewardId();
     * </code>
     *
     * @return int
     */
    public function getRewardId()
    {
        return (int)$this->reward_id;
    }

    /**
     * Return the date of the record.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new Crowdfunding\Intention(\JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $date = $intention->getRecordDate();
     * </code>
     *
     * @return string
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }
}
