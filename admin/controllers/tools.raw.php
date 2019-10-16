<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding tools controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Components
 * @since          1.6
 */
class CrowdfundingControllerTools extends JControllerLegacy
{
    /**
     * @param string $name
     * @param string $prefix
     * @param array  $config
     *
     * @return bool|CrowdfundingModelTools
     */
    public function getModel($name = 'Tools', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function getAcyStats()
    {
        // Check for request forgeries.
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $response = new Prism\Response\Json();

        $projectId  = $this->input->get->getInt('acy_pid');
        $listId     = $this->input->get->getInt('acy_lid');
        $model      = $this->getModel();

        $data = array();
        try {
            $model->addSubscribers();

            $data = $model->getAcyStats($projectId, $listId);

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->success();

            echo $response;
            $app->close();
        }

        $response
            ->setData($data)
            ->success();

        echo $response;
        $app->close();
    }

    /**
     * Add funders to Acy Mailing list.
     *
     * @throws Exception
     */
    public function addFunders()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $response = new Prism\Response\Json();

        $projectId  = $this->input->post->getInt('acy_pid');
        $listId     = $this->input->post->getInt('acy_lid');
        $model      = $this->getModel();

        $numberOfAdded = 0;

        try {

            $numberOfAdded = $model->addFundersToAcyList($projectId, $listId);

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        if (!$numberOfAdded) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_CANNOT_BE_ADDED_SUBSCRIBERS'))
                ->failure();
        } else {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
                ->setText(JText::sprintf('COM_CROWDFUNDING_ADDED_SUBSCRIBERS_D', $numberOfAdded))
                ->success();
        }

        echo $response;
        $app->close();
    }
}
