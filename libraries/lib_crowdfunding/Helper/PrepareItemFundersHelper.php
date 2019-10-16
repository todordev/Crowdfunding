<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Prism\Helper\HelperInterface;
use Prism\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare the items.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
final class PrepareItemFundersHelper implements HelperInterface
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * @param \JDatabaseDriver $db
     */
    public function __construct(\JDatabaseDriver $db = null)
    {
        $this->db     = $db;
    }

    /**
     * Count project funders.
     *
     * @param array $data
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function handle(&$data, array $options = array())
    {
        $funders = array();
        $ids     = ArrayHelper::getIds($data, 'id');

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->select('a.project_id, COUNT(*) AS funders')
                ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
                ->where('a.project_id  IN (' . implode(',', $ids) . ')')
                ->where('(a.txn_status = ' . $this->db->quote('completed') . ' OR a.txn_status = ' . $this->db->quote('pending') . ')')
                ->group($this->db->quoteName('project_id'));

            $this->db->setQuery($query);

            $funders = (array)$this->db->loadObjectList('project_id');
        }

        foreach ($data as $item) {
            $item->funders = array_key_exists($item->id, $funders) ? $funders[$item->id]->funders : 0;
        }

        unset($funders);
    }
}
