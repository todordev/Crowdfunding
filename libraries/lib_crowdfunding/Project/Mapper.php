<?php
/**
 * @package      Crowdfunding
 * @subpackage   Project
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project;

use Crowdfunding\Category\Category;
use Joomla\Registry\Registry;
use Prism\Domain;
use Prism\Constants;
use Crowdfunding\Date;
use Prism\Domain\Entity;
use Prism\Utilities\MathHelper;
use Prism\Validator\Date as DateValidator;
use Crowdfunding\Project\Gateway\ProjectGateway;

/**
 * This class provides functionality for managing a challenge.
 *
 * @package      Crowdfunding
 * @subpackage   Project
 */
class Mapper extends Domain\Mapper
{
    /**
     * @var ProjectGateway
     */
    protected $gateway;

    /**
     * Initialize the object.
     *
     * @param ProjectGateway $gateway
     */
    public function __construct(ProjectGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Return a gateway object.
     *
     * @return ProjectGateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    protected function createObject()
    {
        return new Project;
    }

    /**
     * @param Entity $object
     * @param array  $data
     *
     * @return Project
     */
    public function populate(Entity $object, array $data)
    {
        $categoryData = array(
            'id' => $data['catid'],
            'slug' => $data['catslug'],
            'published' => $data['catstate'],
            'title' => $data['category']
        );

        /** @var Project $object */
        $object->bind($data);

        // Calculate funded percent
        if (!$object->getGoal()) {
            $object->setFundedPercent(0);
        } else {
            $object->setFundedPercent(MathHelper::calculatePercentage($object->getFunded(), $object->getGoal(), 0));
        }

        // Calculate end date
        if ($object->getFundingDays() > 0) {
            $fundingStartDateValidator = new DateValidator($object->getFundingStart());
            if (!$fundingStartDateValidator->isValid()) {
                $object->setFundingEnd(Constants::DATE_DEFAULT_SQL_DATE);
            } else {
                $fundingStartDate  = new Date($object->getFundingStart());
                $fundingEndDate    = $fundingStartDate->calculateEndDate($object->getFundingDays());
                $object->setFundingEnd($fundingEndDate->format('Y-m-d'));
            }
        }

        // Calculate days left
        $today = new Date();
        $object->setDaysLeft($today->calculateDaysLeft($object->getFundingDays(), $object->getFundingStart(), $object->getFundingEnd()));

        $category = new Category();
        $category->bind($categoryData);

        $object->setCategory($category);

        return $object;
    }

    protected function insertObject(Entity $object)
    {
        $this->gateway->insertObject($object);
    }

    protected function updateObject(Entity $object)
    {
        $this->gateway->updateObject($object);
    }

    protected function deleteObject(Entity $object)
    {
    }
}
