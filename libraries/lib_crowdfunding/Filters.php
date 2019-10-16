<?php
/**
 * @package      Crowdfunding
 * @subpackage   Filters
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing filters and options.
 *
 * @package      Crowdfunding
 * @subpackage   Filters
 */
class Filters
{
    protected $options = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    protected static $instance;

    /**
     * Initialize the object.
     *
     * <code>
     * $filters    = new Crowdfunding\Filters(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     */
    protected function __construct(\JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set database object.
     *
     * <code>
     * $country   = new Crowdfunding\Filters;
     * $country->setDb(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(\JDatabaseDriver $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Create an object.
     *
     * <code>
     * $filters    = Crowdfunding\Filters::getInstance(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     *
     * @return null|self
     */
    public static function getInstance(\JDatabaseDriver $db)
    {
        if (self::$instance === null) {
            self::$instance = new Filters($db);
        }

        return self::$instance;
    }

    /**
     * Prepare payment statuses as an array with options.
     *
     * <code>
     * $filters    = new Crowdfunding\Filters();
     * $options = $filters->getPaymentStatuses();
     * </code>
     *
     * @return array
     */
    public function getPaymentStatuses()
    {
        return array(
            \JHtml::_('select.option', 'completed', \JText::_('COM_CROWDFUNDING_COMPLETED')),
            \JHtml::_('select.option', 'pending', \JText::_('COM_CROWDFUNDING_PENDING')),
            \JHtml::_('select.option', 'canceled', \JText::_('COM_CROWDFUNDING_CANCELED')),
            \JHtml::_('select.option', 'refunded', \JText::_('COM_CROWDFUNDING_REFUNDED')),
            \JHtml::_('select.option', 'failed', \JText::_('COM_CROWDFUNDING_FAILED'))
        );
    }

    /**
     * Prepare an array with reward distribution options.
     *
     * <code>
     * $filters    = new Crowdfunding\Filters();
     * $options = $filters->getRewardDistributionStatuses();
     * </code>
     *
     * @return array
     */
    public function getRewardDistributionStatuses()
    {
        return array(
            \JHtml::_('select.option', 'none', \JText::_('COM_CROWDFUNDING_NOT_SELECTED')),
            \JHtml::_('select.option', '0', \JText::_('COM_CROWDFUNDING_NOT_SENT')),
            \JHtml::_('select.option', '1', \JText::_('COM_CROWDFUNDING_SENT')),
        );
    }

    /**
     * Load project types and prepare them as an array with options.
     *
     * <code>
     * $filters = new Crowdfunding\Filters(\JFactory::getDbo());
     * $options = $filters->getProjectsTypes();
     * </code>
     *
     * @return array
     * @deprecated v2.8
     */
    public function getProjectsTypes()
    {
        if (!array_key_exists('project_types', $this->options)) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.id AS value, a.title AS text')
                ->from($this->db->quoteName('#__crowdf_types', 'a'));

            $this->db->setQuery($query);
            $this->options['project_types'] = (array)$this->db->loadAssocList();
        }

        return $this->options['project_types'];
    }

    /**
     * Load payment services and prepare them as an array with options.
     *
     * <code>
     * $filters = new Crowdfunding\Filters(\JFactory::getDbo());
     * $options = $filters->getPaymentServices();
     * </code>
     *
     * @return array
     */
    public function getPaymentServices()
    {
        if (!array_key_exists('payment_services', $this->options)) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.service_alias AS value, a.service_provider AS text')
                ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
                ->where('a.service_alias != ""')
                ->group('a.service_provider');

            $this->db->setQuery($query);
            $this->options['payment_services'] = (array)$this->db->loadAssocList();
        }

        return $this->options['payment_services'];
    }

    /**
     * Load countries and prepare them as an array with options.
     *
     * <code>
     * $filters = new Crowdfunding\Filters(\JFactory::getDbo());
     * $options = $filters->getCountries();
     * </code>
     *
     * @param string $index This is a column that will be used as a value of an option. Possible values: id, code or code4.
     * @param bool $force Force loading data.
     *
     * @return array
     *
     * @deprecated since 2.8 Use Crowdfunding\Country\Countries->toOptions() instead.
     */
    public function getCountries($index = 'id', $force = false)
    {
        if (!array_key_exists('countries', $this->options) or $force) {
            $query = $this->db->getQuery(true);

            switch ($index) {

                case 'code':
                    $query->select('a.code AS value, a.name AS text');
                    break;

                case 'code4':
                    $query->select('a.code4 AS value, a.name AS text');
                    break;

                default:
                    $query->select('a.id AS value, a.name AS text');
                    break;
            }

            $query->from($this->db->quoteName('#__crowdf_countries', 'a'));

            $this->db->setQuery($query);

            $this->options['countries'] = (array)$this->db->loadAssocList();
        }

        return $this->options['countries'];
    }

    /**
     * Load log types and prepare them as an array with options.
     *
     * <code>
     * $filters = new Crowdfunding\Filters(\JFactory::getDbo());
     * $options = $filters->getLogTypes();
     * </code>
     *
     * @return array
     */
    public function getLogTypes()
    {
        if (!array_key_exists('log_types', $this->options)) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.type AS value, a.type AS text')
                ->from($this->db->quoteName('#__crowdf_logs', 'a'))
                ->group('a.type');

            $this->db->setQuery($query);
            $this->options['log_types'] = (array)$this->db->loadAssocList();
        }

        return $this->options['log_types'];
    }
}
