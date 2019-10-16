<?php
/**
 * @package      Crowdfunding
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Joomla\Utilities\ArrayHelper;
use Prism;
use Prism\Database;
use Prism\Validator;
use Prism\Utilities\MathHelper;
use Crowdfunding\Date;
use Crowdfunding\Rewards;
use Crowdfunding\Type;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing a project.
 *
 * @package      Crowdfunding
 * @subpackage   Projects
 */
class Project extends Database\Table
{
    protected $id;
    protected $title;
    protected $alias;
    protected $short_desc;
    protected $description;
    protected $image;
    protected $image_square;
    protected $image_small;
    protected $location_id;
    protected $goal;
    protected $funded;
    protected $funding_type;
    protected $funding_start;
    protected $funding_end;
    protected $funding_days;
    protected $pitch_video;
    protected $pitch_image;
    protected $hits;
    protected $created;
    protected $featured;
    protected $published;
    protected $approved;
    protected $ordering;
    protected $catid = 0;
    protected $type_id = 0;
    protected $user_id = 0;

    protected $rewards;
    protected $type;

    protected $fundedPercent = 0;
    protected $daysLeft = 0;
    protected $slug = '';
    protected $catslug = '';
    protected $backers;

    protected static $instances = array();

    /**
     * Create an object.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = Crowdfunding\Project::getInstance(\JFactory::getDbo(), $projectId);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param int $id
     *
     * @return null|self
     *
     * @deprecated v2.4 Use container.
     */
    public static function getInstance(\JDatabaseDriver $db, $id)
    {
        if (!array_key_exists($id, self::$instances)) {
            $item  = new Project($db);
            $item->load($id);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load project data from database.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     * </code>
     *
     * @param array|int $keys Array with keys or project ID.
     * @param array $options
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        if (!$keys) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_KEYS'));
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                'a.id, a.title, a.alias, a.short_desc, a.description, a.image, a.image_square, a.image_small, ' .
                'a.location_id, a.goal, a.funded, a.funding_type, a.funding_start, a.funding_end, a.funding_days, ' .
                'a.pitch_video, a.pitch_image, a.hits, a.created, a.featured, a.published, a.approved, ' .
                'a.ordering, a.catid, a.type_id, a.user_id, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug'
            )
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->leftJoin($this->db->quoteName('#__categories', 'b') . ' ON a.catid = b.id');

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        // Filter by state.
        $filter = ArrayHelper::getValue($options, 'state');
        if ($filter !== null and is_numeric($filter)) {
            $query->where('a.published = ' . (int)$filter);
        }

        // Filter by approved state.
        $filter = ArrayHelper::getValue($options, 'approved');
        if ($filter !== null and is_numeric($filter)) {
            $query->where('a.approved = ' . (int)$filter);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);

        // Calculate funded percent
        if (!$this->goal) {
            $this->fundedPercent = 0;
        } else {
            $this->fundedPercent = MathHelper::calculatePercentage($this->funded, $this->goal, 0);
        }

        // Calculate end date
        if ($this->funding_days > 0) {
            $fundingStartDateValidator = new Prism\Validator\Date($this->funding_start);
            if (!$fundingStartDateValidator->isValid()) {
                $this->funding_end = Prism\Constants::DATE_DEFAULT_SQL_DATE;
            } else {
                $fundingStartDate  = new Date($this->funding_start);
                $fundingEndDate    = $fundingStartDate->calculateEndDate($this->funding_days);
                $this->funding_end = $fundingEndDate->format('Y-m-d');
            }
        }

        // Calculate days left
        $today = new Date();
        $this->daysLeft = $today->calculateDaysLeft($this->funding_days, $this->funding_start, $this->funding_end);
    }

    /**
     * Store the data in database.
     *
     * <code>
     * $data = (
     *  "title"  => "My project...",
     *  "user_id" => 1
     * );
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->bind($data);
     * $project->store();
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
        $created       = (!$this->created) ? 'NULL' : $this->db->quote($this->created);
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('short_desc') . '=' . $this->db->quote($this->short_desc))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($this->image_square))
            ->set($this->db->quoteName('image_small') . '=' . $this->db->quote($this->image_small))
            ->set($this->db->quoteName('location_id') . '=' . $this->db->quote($this->location_id))
            ->set($this->db->quoteName('goal') . '=' . $this->db->quote($this->goal))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($this->funded))
            ->set($this->db->quoteName('funding_type') . '=' . $this->db->quote($this->funding_type))
            ->set($this->db->quoteName('funding_start') . '=' . $this->db->quote($this->funding_start))
            ->set($this->db->quoteName('funding_end') . '=' . $this->db->quote($this->funding_end))
            ->set($this->db->quoteName('funding_days') . '=' . $this->db->quote($this->funding_days))
            ->set($this->db->quoteName('pitch_video') . '=' . $this->db->quote($this->pitch_video))
            ->set($this->db->quoteName('pitch_image') . '=' . $this->db->quote($this->pitch_image))
            ->set($this->db->quoteName('hits') . '=' . (int)$this->hits)
            ->set($this->db->quoteName('created') . '=' . $created)
            ->set($this->db->quoteName('featured') . '=' . $this->db->quote($this->featured))
            ->set($this->db->quoteName('published') . '=' . $this->db->quote($this->published))
            ->set($this->db->quoteName('approved') . '=' . $this->db->quote($this->approved))
            ->set($this->db->quoteName('ordering') . '=' . $this->db->quote($this->ordering))
            ->set($this->db->quoteName('catid') . '=' . (int)$this->catid)
            ->set($this->db->quoteName('type_id') . '=' . (int)$this->type_id)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('short_desc') . '=' . $this->db->quote($this->short_desc))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($this->image_square))
            ->set($this->db->quoteName('image_small') . '=' . $this->db->quote($this->image_small))
            ->set($this->db->quoteName('location_id') . '=' . $this->db->quote($this->location_id))
            ->set($this->db->quoteName('goal') . '=' . $this->db->quote($this->goal))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($this->funded))
            ->set($this->db->quoteName('funding_type') . '=' . $this->db->quote($this->funding_type))
            ->set($this->db->quoteName('funding_start') . '=' . $this->db->quote($this->funding_start))
            ->set($this->db->quoteName('funding_end') . '=' . $this->db->quote($this->funding_end))
            ->set($this->db->quoteName('funding_days') . '=' . $this->db->quote($this->funding_days))
            ->set($this->db->quoteName('pitch_video') . '=' . $this->db->quote($this->pitch_video))
            ->set($this->db->quoteName('pitch_image') . '=' . $this->db->quote($this->pitch_image))
            ->set($this->db->quoteName('hits') . '=' . (int)$this->hits)
            ->set($this->db->quoteName('created') . '=' . $this->db->quote($this->created))
            ->set($this->db->quoteName('featured') . '=' . $this->db->quote($this->featured))
            ->set($this->db->quoteName('published') . '=' . $this->db->quote($this->published))
            ->set($this->db->quoteName('approved') . '=' . $this->db->quote($this->approved))
            ->set($this->db->quoteName('ordering') . '=' . $this->db->quote($this->ordering))
            ->set($this->db->quoteName('catid') . '=' . (int)$this->catid)
            ->set($this->db->quoteName('type_id') . '=' . (int)$this->type_id)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id)
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Add a new amount to current funded one.
     * Calculate funded percent.
     *
     * <code>
     * $projectId = 1;
     * $funds = 50;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->addFunds($funds);
     * $project->store();
     * </code>
     *
     * @param float $amount
     */
    public function addFunds($amount)
    {
        $this->funded += $amount;

        // Calculate new percentage
        $this->setFundedPercent((string)MathHelper::calculatePercentage($this->funded, $this->goal, 0));
    }

    /**
     * Remove amount from current funded one.
     * Calculate funded percent.
     *
     * <code>
     * $projectId = 1;
     * $funds     = 50;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->removeFunds($funds);
     * $project->store();
     * </code>
     *
     * @param float $amount
     */
    public function removeFunds($amount)
    {
        $this->funded -= $amount;

        // Calculate new percentage
        $this->setFundedPercent((string)MathHelper::calculatePercentage($this->funded, $this->goal, 0));
    }

    /**
     * Load funded amount of the project from database.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->setId($projectId);
     * $project->loadFunds();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function loadFunds()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.funded')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query, 0, 1);
        $this->funded = $this->db->loadResult();
    }

    /**
     * Store project funds in database.
     *
     * <code>
     * $projectId = 1;
     * $finds = 50;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     * $project->addFunds($finds);
     * $project->storeFunds();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function storeFunds()
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($this->funded))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Return all project rewards.
     *
     * <code>
     * $rewardsOptions = array(
     *  "project_id" => 1
     *  "state" => Prism\Constants::PUBLISHED
     * );
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($rewardsOptions);
     *
     * $rewards = $project->getRewards($rewardsOptions);
     * </code>
     *
     * @param array $options
     *
     * @return Rewards
     */
    public function getRewards(array $options = array())
    {
        if ($this->rewards === null) {
            $options['project_id'] = (int)$this->id;
            $this->rewards = Rewards::getInstance($this->db, $options);
        }

        return $this->rewards;
    }

    /**
     * Return the percent of funded amount.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $percentage = $project->getFundedPercent();
     * </code>
     *
     * @return float
     */
    public function getFundedPercent()
    {
        return $this->fundedPercent;
    }

    /**
     * Set the percent of funded amount.
     *
     * @param float $percent
     */
    public function setFundedPercent($percent)
    {
        $this->fundedPercent = $percent;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->getId()) {
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
     * Set project ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->setId($projectId);
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
     * Return category ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $categoryId = $project->getCategoryId();
     * </code>
     *
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->catid;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $userId = $project->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return project title.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $title = $project->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return project goal.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $goal = $project->getGoal();
     * </code>
     *
     * @return float
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * Return the amount that has been funded.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedAmount = $project->getFunded();
     * </code>
     *
     * @return float
     */
    public function getFunded()
    {
        return $this->funded;
    }

    /**
     * Return the funding type of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedType = $project->getFundingType();
     * </code>
     *
     * @return string
     */
    public function getFundingType()
    {
        return $this->funding_type;
    }

    /**
     * Return the date when the campaign has started.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedStartDate = $project->getFundingStart();
     * </code>
     *
     * @return string
     */
    public function getFundingStart()
    {
        return $this->funding_start;
    }

    /**
     * Return the date of the end of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedEndDate = $project->getFundingEnd();
     * </code>
     *
     * @return string
     */
    public function getFundingEnd()
    {
        return $this->funding_end;
    }

    /**
     * Return original image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getImage();
     * </code>
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return square image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getSquareImage();
     * </code>
     *
     * @return string
     */
    public function getSquareImage()
    {
        return $this->image_square;
    }

    /**
     * Return small image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getSmallImage();
     * </code>
     *
     * @return string
     */
    public function getSmallImage()
    {
        return $this->image_small;
    }

    /**
     * Return pitch image.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $pitchImage = $project->getPitchImage();
     * </code>
     *
     * @return string
     */
    public function getPitchImage()
    {
        return $this->pitch_image;
    }

    /**
     * Return pitch video.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $pitchVideo = $project->getPitchVideo();
     * </code>
     *
     * @return string
     */
    public function getPitchVideo()
    {
        return $this->pitch_video;
    }

    /**
     * Return short description of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $desc = $project->getShortDesc();
     * </code>
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->short_desc;
    }

    /**
     * Check if the project is published.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return (bool)((int)$this->published === Prism\Constants::PUBLISHED);
    }

    /**
     * Check if the project has been approved.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->isApproved()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isApproved()
    {
        return (bool)((int)$this->approved === Prism\Constants::APPROVED);
    }

    /**
     * Return project type.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $type = $project->getType();
     * </code>
     *
     * @return Type
     */
    public function getType()
    {
        if ($this->type === null and $this->type_id > 0) {
            $this->type = new Type(\JFactory::getDbo());
            $this->type->load($this->type_id);

            if (!$this->type->getId()) {
                $this->type = null;
            }
        }

        return $this->type;
    }

    /**
     * Return the days that left to the end of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $days = $project->getDaysLeft();
     * </code>
     *
     * @return int
     */
    public function getDaysLeft()
    {
        return $this->daysLeft;
    }

    /**
     * Return project slug.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $slug = $project->getSlug();
     * </code>
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return project category slug.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $catslug = $project->getCatSlug();
     * </code>
     *
     * @return string
     */
    public function getCatSlug()
    {
        return $this->catslug;
    }

    /**
     * Check if the project is completed.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new Crowdfunding\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->isCompleted()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isCompleted()
    {
        $today      = strtotime('today');
        $fundingEnd = strtotime($this->funding_end);

        return (bool)($today > $fundingEnd);
    }

    /**
     * Count and return project backers.
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function getBackers()
    {
        if ($this->backers === null and (int)$this->id > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
                ->where('a.project_id  = ' . (int)$this->id)
                ->where('(a.txn_status = ' . $this->db->quote('completed') . ' OR a.txn_status = ' . $this->db->quote('pending') . ')');

            $this->db->setQuery($query);

            $this->backers = (int)$this->db->loadResult();
        }

        return (int)$this->backers;
    }

    /**
     * Remove the record of the pitch image from database.
     *
     * <code>
     * $projectId  = 1;
     *
     * $project   = new Crowdfunding\Project(JFactory::getDbo());
     * $project->load($placeId);
     *
     * $project->removePitchImage();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function removePitchImage()
    {
        $this->pitch_image = '';

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('pitch_image') . '= ""')
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Store the pitch image in database.
     *
     * <code>
     * $projectId  = 1;
     * $image = 'picture_pitch.jpg';
     *
     * $project   = new Crowdfunding\Project(JFactory::getDbo());
     * $project->load($placeId);
     *
     * $project->updatePitchImage($image);
     * </code>
     *
     * @param string $image
     *
     * @throws \RuntimeException
     */
    public function storePitchImage($image)
    {
        if (is_string($image)) {
            $this->pitch_image = $image;

            $query = $this->db->getQuery(true);

            $query
                ->update($this->db->quoteName('#__crowdf_projects'))
                ->set($this->db->quoteName('pitch_image') . '=' . $this->db->quote($this->pitch_image))
                ->where($this->db->quoteName('id') . '=' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Store the image in database.
     *
     * <code>
     * $projectId  = 1;
     * $images = array(
     *    'image'           => 'image.jpg',
     *    'image_small'     => 'image_small.jpg',
     *    'image_square'    => 'image_square.jpg',
     * );
     *
     * $project   = new Crowdfunding\Project(JFactory::getDbo());
     * $project->load($placeId);
     *
     * $project->storeImage($images);
     * </code>
     *
     * @param array $images
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function storeImage(array $images)
    {
        if (count($images) > 0) {
            $this->image        = ArrayHelper::getValue($images, 'image', '', 'string');
            $this->image_small  = ArrayHelper::getValue($images, 'image_small', '', 'string');
            $this->image_square = ArrayHelper::getValue($images, 'image_square', '', 'string');

            $query = $this->db->getQuery(true);

            $query
                ->update($this->db->quoteName('#__crowdf_projects'))
                ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
                ->set($this->db->quoteName('image_small') . '=' . $this->db->quote($this->image_small))
                ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($this->image_square))
                ->where($this->db->quoteName('id') . '=' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Remove the records about image from database.
     *
     * <code>
     * $projectId  = 1;
     * $image      = 'picture_pitch.jpg';
     *
     * $project   = new Crowdfunding\Project(JFactory::getDbo());
     * $project->load($placeId);
     *
     * $project->removeImage();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function removeImage()
    {
        $this->image = '';
        $this->image_small = '';
        $this->image_square = '';

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('image') . '= ""')
            ->set($this->db->quoteName('image_small') . '= ""')
            ->set($this->db->quoteName('image_square') . '= ""')
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
