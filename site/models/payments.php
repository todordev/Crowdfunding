<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;
use Prism\Money\Money;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelPayments extends JModelLegacy
{
    /**
     * @param int $projectId
     * @param Joomla\Registry\Registry $params
     * @param stdClass $wizardSession
     *
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws RuntimeException
     * @throws \Prism\Domain\BindException
     *
     * @return stdClass
     */
    public function prepareItem($projectId, $params, $wizardSession)
    {
        $container        = Prism\Container::getContainer();
        $containerHelper  = new Crowdfunding\Container\Helper\Project($container);
        $gateway          = new Crowdfunding\Project\Gateway\JoomlaGateway(JFactory::getDbo());

        $project          = $containerHelper->getProject($projectId, $gateway);

        if ($project === null || !$project->getId()) {
            throw new UnexpectedValueException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'));
        }

        if ($project->isCompleted()) {
            throw new UnexpectedValueException(JText::_('COM_CROWDFUNDING_ERROR_COMPLETED_PROJECT'));
        }

        $moneyFormatter = JoomlaFacade::getMoneyFormatter();
        $currency       = JoomlaFacade::getCurrency();

        $item = new stdClass;

        $item->id             = $project->getId();
        $item->title          = $project->getTitle();
        $item->slug           = $project->getSlug();
        $item->catslug        = $project->getCatSlug();
        $item->starting_date  = $project->getFundingStart();
        $item->ending_date    = $project->getFundingEnd();
        $item->user_id        = $project->getUserId();
        $item->rewardId       = $wizardSession->rewardId;

        $item->amount         = $wizardSession->amount;
        $item->currencyCode   = $currency->getCode();

        $item->amountFormated = $moneyFormatter->format(new Money($item->amount, $currency));
        $item->amountCurrency = $moneyFormatter->formatCurrency(new Money($item->amount, $currency));

        return $item;
    }
}
