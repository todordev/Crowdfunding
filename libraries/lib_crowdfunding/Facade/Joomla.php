<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Facade;

use Crowdfunding\Project\Project;
use Prism\Container;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Currency\Gateway\JoomlaGateway;
use Crowdfunding\Container\Helper\Money as MoneyHelper;
use Crowdfunding\Container\Helper\Project as ProjectContainer;

/**
 * Joomla facade object.
 */
abstract class Joomla
{
    /**
     * Create currency object or fetch it from DI container.
     *
     * @throws \RuntimeException
     * @throws \Prism\Domain\BindException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return Currency
     */
    public static function getCurrency()
    {
        $params     =  \JComponentHelper::getParams('com_crowdfunding');
        $container  =  Container::getContainer();

        $helper  = new MoneyHelper($container);
        $gateway = new JoomlaGateway(\JFactory::getDbo());

        return $helper->getCurrency($params->get('project_currency'), $gateway);
    }

    /**
     * Create money formatter or fetch the object from DI container.
     *
     * @param int $digits
     *
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return \Prism\Money\Formatter\IntlDecimalFormatter
     */
    public static function getMoneyFormatter($digits = 2)
    {
        $container = Container::getContainer();
        $locale    = \JFactory::getLanguage()->getTag();

        $helper    = new MoneyHelper($container);

        return $helper->getFormatter($locale, $digits);
    }

    /**
     * Create money parser or fetch the object from DI container.
     *
     * @param int $digits
     *
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return \Prism\Money\Parser\IntlDecimalParser
     */
    public static function getMoneyParser($digits = 2)
    {
        $container = Container::getContainer();
        $locale    = \JFactory::getLanguage()->getTag();

        $helper    = new MoneyHelper($container);

        return $helper->getParser($locale, $digits);
    }

    /**
     * Fetch project object from container.
     *
     * @param int $projectId
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return Project
     */
    public static function getProject($projectId)
    {
        $container        = Container::getContainer();
        $projectContainer = new ProjectContainer($container);
        $gateway          = new \Crowdfunding\Project\Gateway\JoomlaGateway(\JFactory::getDbo());

        return $projectContainer->getProject($projectId, $gateway);
    }
}
