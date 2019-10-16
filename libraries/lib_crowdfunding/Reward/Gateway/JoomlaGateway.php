<?php
/**
 * @package      Crowdfunding\Reward
 * @subpackage   Gateway
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward\Gateway;

use Crowdfunding\Reward\Reward;
use Joomla\Utilities\ArrayHelper;
use Prism\Database\Request\Request;
use Prism\Database\Joomla\FetchMethods;
use Prism\Database\JoomlaDatabaseGateway;
use Prism\Database\Joomla\FetchCollectionMethod;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding\Reward
 * @subpackage   Gateway
 */
class JoomlaGateway extends JoomlaDatabaseGateway implements RewardGateway
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
            'a.id', 'a.title', 'a.description', 'a.amount', 'a.number', 'a.distributed', 'a.delivery',
            'a.shipping', 'a.image', 'a.image_thumb', 'a.image_square', 'a.published', 'a.project_id',
            'b.user_id'
        ];

        $fields = $this->prepareFields($request, $defaultFields);

        // If there are no fields, use default ones.
        if (count($fields) === 0) {
            $fields = $defaultFields;
            unset($defaultFields);
        }

        $query
            ->select($fields)
            ->from($this->db->quoteName('#__crowdf_rewards', 'a'))
            ->innerJoin($this->db->quoteName('#__crowdf_projects', 'b') . ' ON a.project_id = b.id');

        return $query;
    }

    public function insertObject(Reward $object)
    {
        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_rewards'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($object->getTitle()))
            ->set($this->db->quoteName('description') . '=' . $this->db->quote($object->getDescription()))
            ->set($this->db->quoteName('amount') . '=' . $this->db->quote($object->getAmount()))
            ->set($this->db->quoteName('number') . '=' . (int)$object->getNumber())
            ->set($this->db->quoteName('distributed') . '=' . (int)$object->getDistributed())
            ->set($this->db->quoteName('delivery') . '=' . $this->db->quote($object->getDeliveryDate()))
            ->set($this->db->quoteName('shipping') . '=' . $this->db->quote($object->getShipping()))
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($object->getImage()))
            ->set($this->db->quoteName('image_thumb') . '=' . $this->db->quote($object->getImageThumbnail()))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($object->getImageSquare()))
            ->set($this->db->quoteName('published') . '=' . (int)$object->getPublished())
            ->set($this->db->quoteName('project_id') . '=' . (int)$object->getProjectId());

        $this->db->setQuery($query);
        $this->db->execute();

        $object->setId($this->db->insertid());
    }

    public function updateObject(Reward $object)
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_rewards'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($object->getTitle()))
            ->set($this->db->quoteName('description') . '=' . $this->db->quote($object->getDescription()))
            ->set($this->db->quoteName('amount') . '=' . $this->db->quote($object->getAmount()))
            ->set($this->db->quoteName('number') . '=' . (int)$object->getNumber())
            ->set($this->db->quoteName('distributed') . '=' . (int)$object->getDistributed())
            ->set($this->db->quoteName('delivery') . '=' . $this->db->quote($object->getDeliveryDate()))
            ->set($this->db->quoteName('shipping') . '=' . $this->db->quote($object->getShipping()))
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($object->getImage()))
            ->set($this->db->quoteName('image_thumb') . '=' . $this->db->quote($object->getImageThumbnail()))
            ->set($this->db->quoteName('image_square') . '=' . $this->db->quote($object->getImageSquare()))
            ->set($this->db->quoteName('published') . '=' . (int)$object->getPublished())
            ->set($this->db->quoteName('project_id') . '=' . (int)$object->getProjectId())
            ->where($this->db->quoteName('id') .'='. (int)$object->getId());

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Prepare some query filters.
     *
     * @param \JDatabaseQuery $query
     * @param Request         $request
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    protected function filter(\JDatabaseQuery $query, Request $request)
    {
        $conditions = $request->getConditions();

        // Filter by IDs
        if ($conditions->getSpecificCondition('ids')) {
            $condition = $conditions->getSpecificCondition('ids');
            if ($condition !== null && is_array($condition->getValue())) {
                $ids = ArrayHelper::toInteger($condition->getValue());
                $ids = array_filter(array_unique($ids));

                if (count($ids) > 0) {
                    $query->where($this->db->quoteName('a.id') . ' IN (' . implode(',', $ids) . ')');
                }
            }
        }

        // Filter by standard conditions.
        parent::filter($query, $request);
    }
}
