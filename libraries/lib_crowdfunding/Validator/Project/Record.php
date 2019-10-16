<?php
/**
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Validator\Project;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality to check if project exists in database.
 *
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 */
class Record implements ValidatorInterface
{
    protected $projectId;
    protected $options = array();

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
     * $projectId = 1;
     *
     * $options = array(
     *     'user_id' => 1,
     *     'state'   => Prism\Constants::PUBLISHED
     * );
     *
     * $record = new Crowdfunding\Validator\Project\Record(\JFactory::getDbo(), $projectId, $options);
     * </code>
     *
     * @param \JDatabaseDriver $db        Database object.
     * @param int             $projectId Project ID.
     * @param array           $options
     */
    public function __construct(\JDatabaseDriver $db, $projectId, array $options = array())
    {
        $this->db        = $db;
        $this->projectId = (int)$projectId;
        $this->options   = $options;
    }

    /**
     * Validate project record.
     *
     * <code>
     * $projectId = 1;
     *
     * $record = new Crowdfunding\Validator\Project\Record(\JFactory::getDbo(), $projectId);
     * if(!$record->isValid()) {
     * //......
     * }
     * </code>
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function isValid()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$this->projectId);

        // Filter by user.
        $userId = array_key_exists('user_id', $this->options) ? (int)$this->options['user_id'] : 0;
        if ($userId > 0) {
            $query->where('a.user_id = '. (int)$userId);
        }

        // Filter by state.
        $state = array_key_exists('state', $this->options) ? $this->options['state'] : null;
        if ($state !== null) {
            $query->where('a.published = '. (int)$state);
        }

        $this->db->setQuery($query, 0, 1);

        return (bool)$this->db->loadResult();
    }
}
