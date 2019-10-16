<?php
/**
 * @package      Crowdfunding\Project
 * @subpackage   Gateway
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Gateway;

use Crowdfunding\Project\Project;
use Prism\Database\Request\Request;
use Prism\Database\Joomla\FetchMethods;
use Prism\Database\JoomlaDatabaseGateway;
use Prism\Database\Joomla\FetchCollectionMethod;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding\Project
 * @subpackage   Gateway
 */
class JoomlaGateway extends JoomlaDatabaseGateway implements ProjectGateway
{
    use FetchMethods, FetchCollectionMethod;

    /**
     * Prepare the query by query builder.
     *
     * @param Request $request
     *
     * @return \JDatabaseQuery
     *
     * @throws \RuntimeException
     */
    protected function getQuery(Request $request = null)
    {
        $query = $this->db->getQuery(true);

        $defaultFields  = [
            'a.id', 'a.title', 'a.alias', 'a.short_desc', 'a.description', 'a.image', 'a.image_square', 'a.image_small',
            'a.location_id', 'a.goal', 'a.funded', 'a.funding_type', 'a.funding_start', 'a.funding_end', 'a.funding_days',
            'a.pitch_video', 'a.pitch_image', 'a.hits', 'a.created', 'a.featured', 'a.published', 'a.approved',
            'a.ordering', 'a.catid', 'a.type_id', 'a.user_id', 'a.params'
        ];

        $aliasFields = [
            'slug'     => $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug',
            'catslug'  => $query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug',
            'catstate' => 'b.published AS catstate',
            'category' => 'b.title AS category'
        ];

        $fields = $this->prepareFields($request, $defaultFields, $aliasFields);

        // If there are no fields, use default ones.
        if (count($fields) === 0) {
            $fields = $defaultFields;
            unset($defaultFields);
        }

        $query
            ->select($fields)
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->leftJoin($this->db->quoteName('#__categories', 'b') . ' ON a.catid = b.id');

        return $query;
    }

    public function insertObject(Project $object)
    {
        $created       = (!$object->getCreated()) ? 'NULL' : $this->db->quote($object->getCreated());
        $description   = (!$object->getDescription()) ? 'NULL' : $this->db->quote($object->getDescription());

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($object->getTitle()))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($object->getAlias()))
            ->set($this->db->quoteName('short_desc') . '=' . $this->db->quote($object->getShortDesc()))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($object->getImage()))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($object->getSquareImage()))
            ->set($this->db->quoteName('image_small') . '=' . $this->db->quote($object->getSmallImage()))
            ->set($this->db->quoteName('location_id') . '=' . $this->db->quote($object->getLocationId()))
            ->set($this->db->quoteName('goal') . '=' . $this->db->quote($object->getGoal()))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($object->getFunded()))
            ->set($this->db->quoteName('funding_type') . '=' . $this->db->quote($object->getFundingType()))
            ->set($this->db->quoteName('funding_start') . '=' . $this->db->quote($object->getFundingStart()))
            ->set($this->db->quoteName('funding_end') . '=' . $this->db->quote($object->getFundingEnd()))
            ->set($this->db->quoteName('funding_days') . '=' . $this->db->quote($object->getFundingDays()))
            ->set($this->db->quoteName('pitch_video') . '=' . $this->db->quote($object->getPitchVideo()))
            ->set($this->db->quoteName('pitch_image') . '=' . $this->db->quote($object->getPitchImage()))
            ->set($this->db->quoteName('hits') . '=' . (int)$object->getHits())
            ->set($this->db->quoteName('created') . '=' . $created)
            ->set($this->db->quoteName('featured') . '=' . $this->db->quote($object->getFeatured()))
            ->set($this->db->quoteName('published') . '=' . $this->db->quote($object->getPublished()))
            ->set($this->db->quoteName('approved') . '=' . $this->db->quote($object->getApproved()))
            ->set($this->db->quoteName('ordering') . '=' . $this->db->quote($object->getOrdering()))
            ->set($this->db->quoteName('catid') . '=' . (int)$object->getCategoryId())
            ->set($this->db->quoteName('type_id') . '=' . (int)$object->getTypeId())
            ->set($this->db->quoteName('user_id') . '=' . (int)$object->getUserId());

        $this->db->setQuery($query);
        $this->db->execute();

        $object->setId($this->db->insertid());
    }

    public function updateObject(Project $object)
    {
        $created       = (!$object->getCreated()) ? 'NULL' : $this->db->quote($object->getCreated());
        $description   = (!$object->getDescription()) ? 'NULL' : $this->db->quote($object->getDescription());

        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($object->getTitle()))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($object->getAlias()))
            ->set($this->db->quoteName('short_desc') . '=' . $this->db->quote($object->getShortDesc()))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($object->getImage()))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($object->getSquareImage()))
            ->set($this->db->quoteName('image_small') . '=' . $this->db->quote($object->getSmallImage()))
            ->set($this->db->quoteName('location_id') . '=' . $this->db->quote($object->getLocationId()))
            ->set($this->db->quoteName('goal') . '=' . $this->db->quote($object->getGoal()))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($object->getFunded()))
            ->set($this->db->quoteName('funding_type') . '=' . $this->db->quote($object->getFundingType()))
            ->set($this->db->quoteName('funding_start') . '=' . $this->db->quote($object->getFundingStart()))
            ->set($this->db->quoteName('funding_end') . '=' . $this->db->quote($object->getFundingEnd()))
            ->set($this->db->quoteName('funding_days') . '=' . $this->db->quote($object->getFundingDays()))
            ->set($this->db->quoteName('pitch_video') . '=' . $this->db->quote($object->getPitchVideo()))
            ->set($this->db->quoteName('pitch_image') . '=' . $this->db->quote($object->getPitchImage()))
            ->set($this->db->quoteName('hits') . '=' . (int)$object->getHits())
            ->set($this->db->quoteName('created') . '=' . $created)
            ->set($this->db->quoteName('featured') . '=' . $this->db->quote($object->getFeatured()))
            ->set($this->db->quoteName('published') . '=' . $this->db->quote($object->getPublished()))
            ->set($this->db->quoteName('approved') . '=' . $this->db->quote($object->getApproved()))
            ->set($this->db->quoteName('ordering') . '=' . $this->db->quote($object->getOrdering()))
            ->set($this->db->quoteName('catid') . '=' . (int)$object->getCategoryId())
            ->set($this->db->quoteName('type_id') . '=' . (int)$object->getTypeId())
            ->set($this->db->quoteName('user_id') . '=' . (int)$object->getUserId())
            ->where($this->db->quoteName('id') . '=' . (int)$object->getId());

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
