<?php
/**
 * @package      Crowdfunding
 * @subpackage   Category
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category;

use Prism\Domain;
use Prism\Constants;
use Joomla\Registry\Registry;

/**
 * This class provides functionality for managing a category.
 *
 * @package      Crowdfunding
 * @subpackage   Category
 */
class Category implements Domain\Entity
{
    use Domain\EntityId, Domain\Populator;

    protected $title;
    protected $alias;
    protected $description;
    protected $published;
    protected $params;

    protected $slug     = '';

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->params   = new Registry();
    }

    /**
     * Return challenge title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return category description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Check if the entity is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return ((int)$this->published === Constants::PUBLISHED);
    }

    /**
     * Return a slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return the value of property Published.
     *
     * @return int
     */
    public function getState()
    {
        return (int)$this->published;
    }

    /**
     * Return category parameters.
     *
     * @return Registry
     */
    public function getParams()
    {
        return $this->params;
    }
}
