<?php
/**
 * @package      Crowdfunding
 * @subpackage   Updates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism\Database;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing an update.
 *
 * @package      Crowdfunding
 * @subpackage   Updates
 */
class Update extends Database\Table
{
    protected $id;
    protected $title;
    protected $description;
    protected $record_date;
    protected $project_id;

    /**
     * Load update data from database.
     *
     * <code>
     * $updateId = 1;
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($updateId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.title, a.description, a.record_date, a.project_id')
            ->from($this->db->quoteName('#__crowdf_updates', 'a'));

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
     * Store the data in database.
     *
     * <code>
     * $data = (
     *     "title"  => "My project...",
     *     "description"  => "My description...",
     *     "project_id" => 1
     * );
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->bind($data);
     * $update->store();
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

    protected function insertObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_updates'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_updates'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id)
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Return update ID.
     *
     * <code>
     * $updateId  = 1;
     *
     * $update    = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($typeId);
     *
     * if (!$update->getId()) {
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
     * Return title of the update.
     *
     * <code>
     * $updateId = 1;
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($updateId);
     *
     * echo $update->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return description of the update.
     *
     * <code>
     * $updateId = 1;
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($updateId);
     *
     * echo $update->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the date when the update has been created.
     *
     * <code>
     * $updateId = 1;
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($updateId);
     *
     * echo $update->getRecordDate();
     * </code>
     *
     * @return string
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $updateId = 1;
     *
     * $update   = new Crowdfunding\Update\Update(\JFactory::getDbo());
     * $update->load($updateId);
     *
     * echo $update->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }
}
