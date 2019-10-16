<?php
/**
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Statistic;

use Crowdfunding\Constants;
use Prism\Database;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for projects statistics.
 *
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 */
abstract class Base extends Database\Collection
{
    protected $allowedDirections = array('ASC', 'DESC');

    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id, a.title, a.short_desc, a.image, a.image_small, a.image_square, a.hits, ' .
                'a.goal, a.funded, a.created, a.user_id, ' .
                'a.funding_start, a.funding_end, a.funding_days, a.funding_type,' .
                'u.name AS user_name, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug'
            )
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->leftJoin($this->db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
            ->leftJoin($this->db->quoteName('#__users', 'u') . ' ON a.user_id = u.id');

        return $query;
    }

    /**
     * Prepare some main filters.
     *
     * @param \JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareFilters(&$query, $options)
    {
        // Filter by state.
        if (array_key_exists('state', $options)) {
            $published = (int)$options['state'];
            if (!$published) {
                $query->where('a.published = 0');
            } else {
                $query->where('a.published = 1');
            }
        }

        // Filter by approval state.
        if (array_key_exists('approved', $options)) {
            $approved = (int)$options['approved'];
            if (!$approved) {
                $query->where('a.approved = 0');
            } else {
                $query->where('a.approved = 1');
            }
        }
    }

    /**
     * Prepare result ordering.
     *
     * @param \JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareOrder(&$query, $options)
    {
        // Filter by state.
        if (array_key_exists('order', $options)) {
            // Prepare direction of ordering.
            $direction = (!array_key_exists('order_dir', $options)) ? 'DESC' : $options['order_dir'];
            if (!in_array($direction, $this->allowedDirections, true)) {
                $direction = 'DESC';
            }

            switch ($options['order']) {
                case Constants::ORDER_BY_CREATED_DATE:
                    $query->order('a.created ' .$direction);
                    break;

                case Constants::ORDER_BY_ORDERING:
                    $query->order('a.ordering ' .$direction);
                    break;

                default: // Order by project title.
                    $query->order('a.title ' .$direction);
                    break;
            }
        }
    }
}
