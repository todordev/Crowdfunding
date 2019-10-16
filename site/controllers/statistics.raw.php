<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use League\Fractal;
use Joomla\Registry\Registry;
use Crowdfunding\Data\Serializer;
use Crowdfunding\Data\Transformer;
use Crowdfunding\Container\MoneyHelper;

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding statistics controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Components
 * @since          1.6
 */
class CrowdfundingControllerStatistics extends JControllerLegacy
{
    public function getDailyFunds()
    {
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */
        
        // Create response object
        $response = new Prism\Response\Json();

        $itemId = $app->input->getUint('id');

        // Check for errors.
        if (!$itemId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();
        }

        $data = array();

        try {
            $params = \JComponentHelper::getParams('com_crowdfunding');
            /** @var  $params Registry */

            $container      = Prism\Container::getContainer();
            $currency       = MoneyHelper::getCurrency($container, $params);
            $moneyFormatter = MoneyHelper::getMoneyFormatter($container, $params);

            $project = new Crowdfunding\Statistics\Project(JFactory::getDbo(), $itemId);
            $data    = $project->getFullPeriodAmounts();

            $manager = new Fractal\Manager();
            $manager->setSerializer(new Serializer\Chart\DailyFunds());

            // Run all transformers
            $resource = new Fractal\Resource\Collection($data, new Transformer\Chart\DailyFunds($moneyFormatter, $currency));
            $data     = $manager->createData($resource)->toArray();

        } catch (Exception $e) {
            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setData($data)
            ->success();

        echo $response;
        $app->close();
    }

    public function getProjectFunds()
    {
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Create response object
        $response = new Prism\Response\Json();

        $itemId = $app->input->getUint('id');

        // Check for errors.
        if (!$itemId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();
        }

        $data = array();

        try {
            $params = \JComponentHelper::getParams('com_crowdfunding');
            /** @var  $params Registry */

            $container      = Prism\Container::getContainer();
            $currency       = MoneyHelper::getCurrency($container, $params);
            $moneyFormatter = MoneyHelper::getMoneyFormatter($container, $params);

            $project = new Crowdfunding\Statistics\Project(JFactory::getDbo(), $itemId);
            $data    = $project->getFundedAmount();

            $manager = new Fractal\Manager();
            $manager->setSerializer(new Serializer\Chart\ProjectFunds());

            // Run all transformers
            $resource = new Fractal\Resource\Item($data, new Transformer\Chart\ProjectFunds($moneyFormatter, $currency));
            $data = $manager->createData($resource)->toArray();

        } catch (Exception $e) {
            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setData($data)
            ->success();

        echo $response;
        $app->close();
    }
}
