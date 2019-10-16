<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register('CrowdfundingModelProject', CROWDFUNDING_PATH_COMPONENT_SITE . '/models/project.php');

class CrowdfundingModelFunding extends CrowdfundingModelProject
{
    protected $formName = 'funding';

    /**
     * Method to get the data that should be injected in the form.
     *
     * @throws   \Exception
     * @return   mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.funding.data', array());
        if (!$data) {
            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get('id');

            $data = $this->getItem($itemId, $userId);
            
            // Validate end date. If the date is not valid, generate a valid one.
            // Use minimum allowed days to generate end funding date.
            $dateValidator   = new Prism\Validator\Date($data->funding_end);
            if (!$dateValidator->isValid()) {
                // Get minimum days.
                $params  = $this->getState('params');
                $minDays = (int)$params->get('project_days_minimum', 30);

                // Generate end date.
                $today             = new Crowdfunding\Date();
                $fundingEndDate    = $today->calculateEndDate($minDays);

                $data->funding_end = $fundingEndDate->format(Prism\Constants::DATE_FORMAT_SQL_DATE);
            }
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @throws   \InvalidArgumentException
     * @throws   \RuntimeException
     * @throws   \Exception
     *
     * @return   mixed        The record id on success, null on failure.
     * @since    1.6
     */
    public function save($data)
    {
        $id           = Joomla\Utilities\ArrayHelper::getValue($data, 'id');
        $goal         = Joomla\Utilities\ArrayHelper::getValue($data, 'goal');
        $fundingType  = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_type');
        $fundingEnd   = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_end', Prism\Constants::DATE_DEFAULT_SQL_DATE);
        $fundingDays  = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_days', 0);
        $durationType = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_duration_type');

        $keys = array(
            'id' => $id,
            'user_id' => JFactory::getUser()->get('id'),
        );

        // Load a record from the database
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        $row->set('goal', $goal);
        $row->set('funding_type', $fundingType);

        $data = array(
            'duration_type' => $durationType,
            'funding_end'   => $fundingEnd,
            'funding_days'  => $fundingDays,
        );

        $this->prepareTableData($row, $data);

        $row->store();

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, 'funding');

        return $row->get('id');
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param CrowdfundingTableProject $table
     * @param array $data
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @since    1.6
     */
    protected function prepareTableData($table, $data)
    {
        $durationType = Joomla\Utilities\ArrayHelper::getValue($data, 'duration_type');
        $fundingEnd   = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_end');
        $fundingDays  = Joomla\Utilities\ArrayHelper::getValue($data, 'funding_days', 0, 'int');

        switch ($durationType) {
            case 'days':
                $fundingDays = ($fundingDays < 0) ? 0 : (int)$fundingDays;
                $table->set('funding_days', $fundingDays);

                // Calculate end date
                $startingDateValidator   = new Prism\Validator\Date($table->get('funding_start'));
                if ($startingDateValidator->isValid()) {
                    $fundingStartDate   = new Crowdfunding\Date($table->get('funding_start'));
                    $fundingEndDate     = $fundingStartDate->calculateEndDate($table->get('funding_days'));

                    $table->set('funding_end', $fundingEndDate->format(Prism\Constants::DATE_FORMAT_SQL_DATE));
                } else {
                    $table->set('funding_end', Prism\Constants::DATE_DEFAULT_SQL_DATE);
                }
                break;

            case 'date':
                $fundingEnd    = CrowdfundingHelper::convertToSql($fundingEnd);

                $dateValidator = new Prism\Validator\Date($fundingEnd);
                if (!$dateValidator->isValid()) {
                    throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_DATE'));
                }

                $date = new JDate($fundingEnd);

                $table->set('funding_days', 0);
                $table->set('funding_end', $date->toSql());
                break;

            default:
                $table->set('funding_days', 0);
                $table->set('funding_end', Prism\Constants::DATE_DEFAULT_SQL_DATE);
                break;
        }
    }
}
