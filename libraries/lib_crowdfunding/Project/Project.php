<?php
/**
 * @package      Crowdfunding
 * @subpackage   Project
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project;

use Prism\Domain;
use Prism\Constants;
use Joomla\Registry\Registry;
use Prism\Utilities\MathHelper;
use Crowdfunding\Category\Category;

/**
 * This class provides functionality for managing a project.
 *
 * @package      Crowdfunding
 * @subpackage   Project
 */
class Project implements Domain\Entity
{
    use Domain\EntityId, Domain\Populator;

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
    protected $params;
    protected $catid = 0;
    protected $type_id = 0;
    protected $user_id = 0;

    protected $rewards;
    protected $type;

    protected $fundedPercent = 0;
    protected $daysLeft = 0;
    protected $slug = '';
    protected $catslug = '';

    protected $category;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->category = new Category();
        $this->params   = new Registry();
    }

    /**
     * Add a new amount to current funded one.
     * Calculate funded percent.
     *
     * @param float $amount
     */
    public function addFunds($amount)
    {
        $this->funded += $amount;

        // Calculate new percentage
        $this->setFundedPercent(MathHelper::calculatePercentage($this->funded, $this->goal, 0));
    }

    /**
     * Remove amount from current funded one.
     * Calculate funded percent.
     *
     * @param float $amount
     */
    public function removeFunds($amount)
    {
        $this->funded -= $amount;

        // Calculate new percentage
        $this->setFundedPercent(MathHelper::calculatePercentage($this->funded, $this->goal, 0));
    }

    /**
     * Return the percent of funded amount.
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
     * Return category ID.
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
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return project title.
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
     * @return float
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * Return the amount that has been funded.
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
     * @return string
     */
    public function getFundingType()
    {
        return $this->funding_type;
    }

    /**
     * Return the date when the campaign has started.
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
     * @return string
     */
    public function getFundingEnd()
    {
        return $this->funding_end;
    }

    /**
     * Return original image of campaign.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return pitch video.
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
     * @return string
     */
    public function getShortDesc()
    {
        return $this->short_desc;
    }

    /**
     * Check if the project is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return ((int)$this->published === Constants::PUBLISHED);
    }

    /**
     * Check if the project has been approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return ((int)$this->approved === Constants::APPROVED);
    }

    /**
     * Return the days that left to the end of campaign.
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
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return project category slug.
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
     * @return bool
     */
    public function isCompleted()
    {
        $today      = strtotime('today');
        $fundingEnd = strtotime($this->funding_end);

        return ($today > $fundingEnd);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getImageSquare()
    {
        return $this->image_square;
    }

    /**
     * @return string
     */
    public function getImageSmall()
    {
        return $this->image_small;
    }

    /**
     * @return string
     */
    public function getPitchImage()
    {
        return $this->pitch_image;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return (int)$this->location_id;
    }

    /**
     * @return int
     */
    public function getFundingDays()
    {
        return (int)$this->funding_days;
    }

    /**
     * @return int
     */
    public function getHits()
    {
        return (int)$this->hits;
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getFeatured()
    {
        return (int)$this->featured;
    }

    /**
     * @return int
     */
    public function getPublished()
    {
        return (int)$this->published;
    }

    /**
     * @return int
     */
    public function getApproved()
    {
        return (int)$this->approved;
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return (int)$this->ordering;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return (int)$this->type_id;
    }

    /**
     * @param string $date
     */
    public function setFundingEnd($date)
    {
        $this->funding_end = $date;
    }

    /**
     * @param int $days
     */
    public function setDaysLeft($days)
    {
        $this->daysLeft = (int)$days;
    }

    /**
     * @return Registry
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param Registry $params
     */
    public function setParams(Registry $params)
    {
        $this->params = $params;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
