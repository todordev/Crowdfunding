<?php
/**
 * @package      Crowdfunding
 * @subpackage   Reward
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward;

use Prism\Domain;

/**
 * Reward entity.
 *
 * @package      Crowdfunding
 * @subpackage   Reward
 */
class Reward implements Domain\Entity
{
    use Domain\EntityId, Domain\Populator;

    protected $title;
    protected $description;
    protected $amount;
    protected $delivery;
    protected $shipping;
    protected $image;
    protected $image_thumb;
    protected $image_square;
    protected $published;
    protected $project_id;
    protected $user_id      = 0;
    protected $number       = 0;
    protected $distributed  = 0;

    protected $available;

    /**
     * Return reward title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return reward description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return reward amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Return the ID of the user which provides the reward.
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return a reward image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return the thumbnail of the reward.
     *
     * @return string
     */
    public function getImageThumbnail()
    {
        return $this->image_thumb;
    }

    /**
     * Return the square image of the reward.
     *
     * @return string
     */
    public function getImageSquare()
    {
        return $this->image_square;
    }

    /**
     * Return the date to which must be delivered the reward.
     *
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->delivery;
    }

    /**
     * Return the number of the reward.
     *
     * @return int
     */
    public function getNumber()
    {
        return (int)$this->number;
    }

    /**
     * Return an ID of a project.
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }

    /**
     * Return the number of distributed rewards.
     *
     * @return int
     */
    public function getDistributed()
    {
        return (int)$this->distributed;
    }

    /**
     * Return the value of the flag Shipping.
     * 0 - do not provide shipping.
     * 1 - shipping paid by the project owner.
     * 2 - shipping paid by the reward receiver;
     *
     * @return int
     */
    public function getShipping()
    {
        return (int)$this->shipping;
    }

    /**
     * Return reward state.
     * 0 - unpublished; 1 - published; -1 - trashed
     *
     * @return int
     */
    public function getPublished()
    {
        return (int)$this->published;
    }

    /**
     * Increase the number of distributed rewards.
     *
     * @param int
     */
    public function increaseDistributed($number = 1)
    {
        $distributed = (int)$this->distributed + (int)$number;
        if ($distributed > 0 && $distributed <= $this->number) {
            $this->distributed = $distributed;
            $this->available   = $this->calculateAvailable();
        }
    }

    /**
     * Decrease the number of distributed rewards.
     *
     * @param int
     */
    public function decreaseDistributed($number = 1)
    {
        $distributed = (int)$this->distributed - (int)$number;
        
        if ($distributed >= 0 && $distributed <= $this->number) {
            $this->distributed = $distributed;
            $this->available   = $this->calculateAvailable();
        }
    }

    /**
     * Check if this reward is limited.
     * If there is number of rewards, it is limited.
     *
     * @return bool
     */
    public function isLimited()
    {
        return ($this->number > 0);
    }

    /**
     * Return the number of the available rewards.
     *
     * @return int
     */
    public function getAvailable()
    {
        if ($this->available === null) {
            $this->available = $this->calculateAvailable();
        }

        return (int)$this->available;
    }

    /**
     * Check if there are available rewards.
     *
     * @return bool
     */
    public function hasAvailable()
    {
        if ($this->available === null) {
            $this->available = $this->calculateAvailable();
        }

        return ($this->available > 0);
    }

    /**
     * Calculate number of available rewards.
     *
     * @return int|null
     */
    protected function calculateAvailable()
    {
        if ($this->isLimited()) {
            return $this->number - $this->distributed;
        }

        return 0;
    }
}
