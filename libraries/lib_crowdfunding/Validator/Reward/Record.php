<?php
/**
 * @package      Crowdfunding\Rewards
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Validator\Reward;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality to check if reward exists in database.
 *
 * @package      Crowdfunding\Rewards
 * @subpackage   Validators
 */
class Record implements ValidatorInterface
{
    protected $rewardId;
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
     * $rewardId = 1;
     * $options = array(
     *     'project_id' => 1,
     *     'state' => Prism\Constants::PUBLISHED
     * );
     *
     * $record = new Crowdfunding\Validator\Reward\Record(\JFactory::getDbo(), $rewardId, $options);
     * </code>
     *
     * @param \JDatabaseDriver $db        Database object.
     * @param int             $rewardId Reward ID.
     * @param array           $options
     */
    public function __construct(\JDatabaseDriver $db, $rewardId, array $options = array())
    {
        $this->db        = $db;
        $this->rewardId  = (int)$rewardId;
        $this->options   = $options;
    }

    /**
     * Validate reward record.
     *
     * <code>
     * $rewardId = 1;
     *
     * $record = new Crowdfunding\Validator\Reward\Record(\JFactory::getDbo(), $rewardId);
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
            ->from($this->db->quoteName('#__crowdf_rewards', 'a'))
            ->where('a.id = ' . (int)$this->rewardId);

        // Filter by project.
        $projectId = array_key_exists('project_id', $this->options) ? (int)$this->options['project_id'] : 0;
        if ($projectId > 0) {
            $query->where('a.project_id = '. (int)$projectId);
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
