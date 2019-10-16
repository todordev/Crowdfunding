<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

/**
 * This plugin validates data.
 * It works only on front-end.
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgContentCrowdfundingValidator extends JPlugin
{
    protected $allowedContexts = array();
    protected $autoloadLanguage = true;

    public function __construct($subject, array $config)
    {
        parent::__construct($subject, $config);
        
        $this->allowedContexts = array('com_crowdfunding.basic', 'com_crowdfunding.funding', 'com_crowdfunding.story');
    }

    /**
     * This method validates project data that comes from users,
     * during the process of creating campaign.
     *
     * @param string $context
     * @param array $data
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onContentValidate($context, &$data, &$params)
    {
        if (!in_array($context, $this->allowedContexts, true)) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        $result = array(
            'success' => true
        );

        switch ($context) {
            case 'com_crowdfunding.basic':
                $result = $this->validateStepBasic($data, $params);
                break;

            case 'com_crowdfunding.funding':
                $result = $this->validateStepFunding($data, $params);
                break;

            case 'com_crowdfunding.story':
                $result = $this->validateStepStory($data, $params);
                break;
        }

        return $result;
    }

    /**
     * This method validates project data that comes from users,
     * during the process of creating campaign.
     * The system executes this method when the data be saved.
     *
     * @param string $context
     * @param object $item
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onContentValidateAfterSave($context, &$item, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (!in_array($context, $this->allowedContexts, true)) {
            return null;
        }

        $result = array(
            'success' => false,
            'message' => ''
        );

        // Validate pitch image and video URL.
        if ($this->params->get('validate_story_image_video', 1) and (!$item->pitch_image and !$item->pitch_video)) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO');
            return $result;
        }

        // Validation completed successfully.
        $result = array('success' => true);

        return $result;
    }

    protected function validateStepBasic(&$data, &$params)
    {
        return array(
            'success' => true
        );
    }

    /**
     * This method validates project data
     * when someone decides to change a project state ( to publish or approve ).
     * It works only on front-end.
     *
     * @param string $context
     * @param stdClass $item
     * @param Joomla\Registry\Registry $params
     * @param int $state
     *
     * @return null|array
     */
    public function onContentValidateChangeState($context, $item, $params, $state)
    {
        if (strcmp('com_crowdfunding.projects.changestate', $context) !== 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        $result = array(
            'success' => false,
            'message' => ''
        );

        // If the project is approved, do not allow unpublishing.
        if ($this->params->get('validate_state_approved', Prism\Constants::YES) and ((int)$item->published === Prism\Constants::PUBLISHED and (int)$item->approved === Prism\Constants::APPROVED)) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_APPROVED_UNPUBLISH');
            return $result;
        }

        // It is not necessary to continue with validations if it is a process of unpublishing.
        // It is important to do following validation when someone publish his project.
        if ((int)$state === Prism\Constants::UNPUBLISHED) {
            $result = array('success' => true);
            return $result;
        }

        if (!$item->goal) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL');
            return $result;
        }

        if (!$item->funding_type) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_FUNDING_TYPE');
            return $result;
        }

        // Validate funding duration.
        $fundingEnd = new Prism\Validator\Date($item->funding_end);
        if (!$fundingEnd->isValid() and !$item->funding_days) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_FUNDING_DURATION');
            return $result;
        }

        // Validate the period.
        $minDays = (int)$params->get('project_days_minimum', 15);
        $maxDays = (int)$params->get('project_days_maximum');

        // If there is ending date, validate the period.
        $fundingEndDate = new Prism\Validator\Date($item->funding_end);
        if ($fundingEndDate->isValid()) {
            $validatorPeriod = new Crowdfunding\Validator\Project\Period($item->funding_start, $item->funding_end, $minDays, $maxDays);
            if (!$validatorPeriod->isValid()) {
                if ($maxDays > 0) {
                    $result['message'] = JText::sprintf('COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS', $minDays, $maxDays);
                } else {
                    $result['message'] = JText::sprintf('COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS', $minDays);
                }

                return $result;
            }
        }

        // Validate pitch image and video.
        if ($this->params->get('validate_story_image_video', Prism\Constants::YES) and (!$item->pitch_image and !$item->pitch_video)) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO');
            return $result;
        }

        if (!Joomla\String\StringHelper::trim(strip_tags($item->description))) {
            $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DESCRIPTION');
            return $result;
        }

        // Validation completed successfully.
        $result = array('success' => true);

        return $result;
    }

    protected function validateStepStory(&$data, &$params)
    {
        return array(
            'success' => true
        );
    }

    /**
     * Validate user data that comes from step "Funding".
     *
     * @param array $data
     * @param Joomla\Registry\Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function validateStepFunding($data, $params)
    {
        $result = array(
            'success' => false,
            'message' => ''
        );

        // Get item and check it for active state ( published and approved ).
        $itemId = ArrayHelper::getValue($data, 'id');
        $userId = JFactory::getUser()->get('id');

        $item   = null;
        if ($itemId > 0 and $userId > 0) {
            $item = $this->getItem($itemId, $userId);
        }
        
        // Validate minimum and maximum amount.
        if ($this->params->get('validate_amount', 1)) {
            $goal      = ArrayHelper::getValue($data, 'goal', 0, 'float');

            // Verify minimum amount
            $minAmount = (float)$params->get('project_amount_minimum');
            if ($goal < $minAmount) {
                $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL');
                return $result;
            }

            // Verify maximum amount
            $maxAmount = (float)$params->get('project_amount_maximum');
            if ($maxAmount > 0 and ($goal > $maxAmount)) {
                $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL');
                return $result;
            }
        }

        // Check amount for changes.
        $result = $this->checkChangedAmount($data, $item);
        if ($result !== null) {
            return $result;
        }

        // Check duration for changes.
        $result = $this->checkChangedDuration($data, $item);
        if ($result !== null) {
            return $result;
        }

        // Validate funding duration - days or date.
        if ($this->params->get('validate_funding_duration', Prism\Constants::YES)) {
            $minDays = (int)$params->get('project_days_minimum', 15);
            $maxDays = (int)$params->get('project_days_maximum', 0);

            $fundingType = ArrayHelper::getValue($data, 'funding_duration_type');

            // Validate funding type 'days'
            if (strcmp('days', $fundingType) === 0) {
                $days = ArrayHelper::getValue($data, 'funding_days', 0, 'integer');
                if ($days < $minDays) {
                    $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DAYS');
                    return $result;
                }

                if ($maxDays > 0 and ($days > $maxDays)) {
                    $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DAYS');
                    return $result;
                }

            } else { // Validate funding type 'date'

                $fundingEndDate = ArrayHelper::getValue($data, 'funding_end');

                $dateValidator = new Prism\Validator\Date($fundingEndDate);
                if (!$dateValidator->isValid()) {
                    $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DATE');
                    return $result;
                }

                $dateValidator = new Prism\Validator\Date($item->funding_start);
                if ($dateValidator->isValid()) {
                    $starDate = new DateTime($item->funding_start);
                    $endDate  = new DateTime($item->funding_end);
                    if ($endDate <= $starDate) {
                        $result['message'] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DATE');
                        return $result;
                    }
                }
            }
        }

        // Validate funding duration when the projects is published and approved.
        if ($this->params->get('validate_funding_duration_approved', Prism\Constants::YES) and $item !== null) {
            // Validate date if user want to edit date, while the project is published.
            if ($item->published === Prism\Constants::PUBLISHED and $item->approved === Prism\Constants::APPROVED) {
                $minDays = (int)$params->get('project_days_minimum', 15);
                $maxDays = (int)$params->get('project_days_maximum', 0);

                $fundingType = ArrayHelper::getValue($data, 'funding_duration_type');

                // Generate funding end date from days.
                if (strcmp('days', $fundingType) === 0) {
                    // Get funding days.
                    $days = ArrayHelper::getValue($data, 'funding_days', 0, 'integer');

                    $fundingStartDate = new Crowdfunding\Date($item->funding_start);
                    $endDate          = $fundingStartDate->calculateEndDate($days);
                    $fundingEndDate   = $endDate->format('Y-m-d');

                } else { // Get funding end date from request
                    $fundingEndDate = ArrayHelper::getValue($data, 'funding_end');
                }

                // Validate the period.
                $dateValidator = new Crowdfunding\Validator\Project\Period($item->funding_start, $fundingEndDate, $minDays, $maxDays);
                if (!$dateValidator->isValid()) {
                    $result['message'] = ($maxDays > 0) ?
                        JText::sprintf('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS', $minDays, $maxDays) :
                        JText::sprintf('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_ENDING_DATE_MIN_DAYS', $minDays);

                    return $result;
                }
            }
        }

        // Validations completed successfully.
        $result = array(
            'success' => true
        );

        return $result;
    }

    /**
     * @param array $data
     * @param stdClass $item
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function checkChangedAmount($data, $item)
    {
        $result = null;

        switch ($this->params->get('changing_amount', Prism\Constants::NOT_ALLOWED)) {
            case 1: // Allowed
                break;
            
            case 2: // Authorization by the administrator.
                /** @todo */
                break;
            
            default: // Not allowed
                if ($item->published === Prism\Constants::PUBLISHED and $item->approved === Prism\Constants::APPROVED) {
                    $goal   = ArrayHelper::getValue($data, 'goal', 0, 'float');
                    if ($goal !== $item->goal) {
                        $result = array(
                            'success' => false,
                            'message' => JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_GOAL_CHANGE_NOT_ALLWOED')
                        );
                    }
                } elseif ($item->approved === Prism\Constants::APPROVED and $item->published !== Prism\Constants::PUBLISHED) {
                    $goal   = ArrayHelper::getValue($data, 'goal', 0, 'float');
                    if ($goal !== $item->goal) {
                        $result = array(
                            'success' => false,
                            'message' => JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_GOAL_CHANGE_CAMPAIGN_APPROVE')
                        );

                        $this->changeStateNotApproved($item->id, $item->user_id);
                    }
                }

                break;
        }
        
        return $result;
    }

    /**
     * @param array $data
     * @param stdClass $item
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return array
     */
    protected function checkChangedDuration($data, $item)
    {
        $result = null;

        switch ($this->params->get('changing_duration', Prism\Constants::NOT_ALLOWED)) {
            case 1: // Allowed
                break;

            case 2: // Authorization by the administrator.
                /** @todo */
                break;

            default: // Not allowed
                if ($item->published === Prism\Constants::PUBLISHED and $item->approved === Prism\Constants::APPROVED) {
                    $isChanged = $this->isChangedDuration($data, $item);
                    if ($isChanged !== null and $isChanged === true) {
                        $result = array(
                            'success' => false,
                            'message' => JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_DURATION_CHANGE_NOT_ALLWOED')
                        );
                    }

                } elseif ($item->approved === Prism\Constants::APPROVED and $item->published !== Prism\Constants::PUBLISHED) {
                    $isChanged = $this->isChangedDuration($data, $item);
                    if ($isChanged !== null and $isChanged === true) {
                        $app = JFactory::getApplication();
                        $app->enqueueMessage(JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_DURATION_CHANGE_CAMPAIGN_APPROVE'), 'warning');

                        $this->changeStateNotApproved($item->id, $item->user_id);
                    }
                }

                break;
        }

        return $result;
    }

    /**
     * Check duration of the project.
     *
     * @param array $data
     * @param stdClass $item
     *
     * @throws \InvalidArgumentException
     * @return bool|null
     */
    protected function isChangedDuration($data, $item)
    {
        $result = false;

        $fundingType = ArrayHelper::getValue($data, 'funding_duration_type');

        // Check duration days for changes.
        if (strcmp('days', $fundingType) === 0) {
            $days = ArrayHelper::getValue($data, 'funding_days', 0, 'int');
            if ($days !== $item->funding_days) {
                return true;
            }

        } else { // Check funding end date for changes.

            $fundingEndDate = ArrayHelper::getValue($data, 'funding_end');
            $dateValidator  = new Prism\Validator\Date($fundingEndDate);
            if (!$dateValidator->isValid()) {
                return null; // Return null because it will not be possible to compare dates. Let next process validates the date.
            }

            $newDate = new DateTime($fundingEndDate);
            $oldDate = new DateTime($item->funding_end);
            if ($newDate != $oldDate) {
                return true;
            }
        }

        return $result;
    }
    /**
     * Load project data from database.
     *
     * @param int $itemId
     * @param int $userId
     *
     * @throws \RuntimeException
     * @return stdClass|null
     */
    protected function getItem($itemId, $userId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id, a.user_id, a.goal, a.published, a.approved, a.funding_start, a.funding_end, a.funding_days')
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' .(int)$itemId)
            ->where('a.user_id = ' .(int)$userId);

        $db->setQuery($query);

        $result = $db->loadObject();

        if (is_object($result)) {
            $result->published      = (int)$result->published;
            $result->approved       = (int)$result->approved;
            $result->funding_days   = (int)$result->funding_days;
            $result->goal           = (float)$result->goal;
        }

        return $result;
    }

    /**
     * Change the state of project to Not Approved.
     *
     * @param int $itemId
     * @param int $userId
     *
     * @throws \RuntimeException
     * @return stdClass|null
     */
    protected function changeStateNotApproved($itemId, $userId)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName('#__crowdf_projects'))
            ->set($db->quoteName('approved') .'='. (int)Prism\Constants::NOT_APPROVED)
            ->where($db->quoteName('id') .'='. (int)$itemId)
            ->where($db->quoteName('user_id') .'='. (int)$userId);

        $db->setQuery($query);
        $db->execute();
    }
}
