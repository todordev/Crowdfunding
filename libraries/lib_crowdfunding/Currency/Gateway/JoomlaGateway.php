<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies\Gateways
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Currency\Gateway;

use Joomla\Utilities\ArrayHelper;
use Prism\Database\Request\Request;
use Prism\Database\Joomla\FetchMethods;
use Prism\Database\JoomlaDatabaseGateway;
use Prism\Database\Joomla\FetchCollectionMethod;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding
 * @subpackage   Currencies\Gateways
 */
class JoomlaGateway extends JoomlaDatabaseGateway implements CurrencyGateway
{
    use FetchMethods, FetchCollectionMethod;

    /**
     * Prepare the query by query builder.
     *
     * @param Request $request
     *
     * @return \JDatabaseQuery
     * @throws \RuntimeException
     */
    protected function getQuery(Request $request = null)
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.title, a.code, a.symbol, a.position')
            ->from($this->db->quoteName('#__crowdf_currencies', 'a'));

        return $query;
    }

    /**
     * Prepare some query filters.
     *
     * @param \JDatabaseQuery $query
     * @param Request         $request
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function filter(\JDatabaseQuery $query, Request $request)
    {
        $conditions = $request->getConditions();

        // Filter by IDs
        if ($conditions->getSpecificCondition('ids')) {
            $condition = $conditions->getSpecificCondition('ids');

            if ($condition !== null && is_array($condition->getValue())) {
                $usersIds = ArrayHelper::toInteger($condition->getValue());
                $usersIds = array_filter(array_unique($usersIds));

                if (count($usersIds) > 0) {
                    $query->where($this->db->quoteName('a.id') . ' IN (' . implode(',', $usersIds) . ')');
                }
            }
        }

        // Filter by codes.
        if ($conditions->getSpecificCondition('codes')) {
            $condition = $conditions->getSpecificCondition('codes');

            $escapedCodes = array_map(function ($value) {
                return $this->db->quote($value);
            }, $condition->getValue());

            $query->where($this->db->quoteName('a.code') .' IN ('. implode(',', $escapedCodes) .')');
            unset($conditions['codes']);
        }

        // Filter by standard conditions.
        parent::filter($query, $request);
    }
}
