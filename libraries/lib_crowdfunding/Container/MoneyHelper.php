<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Container;

use Prism\Money\Parser;
use Joomla\DI\Container;
use Prism\Money\Formatter;
use Joomla\Registry\Registry;
use Prism\Domain\BindException;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Currency\Gateway\JoomlaGateway;
use Crowdfunding\Container\Helper\Money as ContainerHelperMoney;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to inject Money and Currency objects in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6
 */
abstract class MoneyHelper
{
    /**
     * Return currency object from a container.
     * NOTE: Only used in Joomla.
     *
     * <code>
     * $this->prepareCurrency($container, $params);
     * $currency = $this->getCurrency($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws BindException
     *
     * @return Currency
     */
    public static function getCurrency($container, $params)
    {
        $gateway      = new JoomlaGateway(\JFactory::getDbo());
        $moneyHelper  = new ContainerHelperMoney($container);

        return $moneyHelper->getCurrency($params->get('project_currency'), $gateway);
    }

    /**
     * Return money formatter.
     * NOTE: Only used in Joomla.
     *
     * <code>
     * $this->prepareMoneyFormatter($container, $params);
     * $money = $this->getMoneyFormatter($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws BindException
     *
     * @return Formatter
     */
    public static function getMoneyFormatter($container, $params)
    {
        $language         = \JFactory::getLanguage();
        $locale           = $language->getTag();

        $fractionDigits   = (int)$params->get('fraction_digits', 2);

        $moneyService     = new ContainerHelperMoney($container);
        return $moneyService->getFormatter($locale, $fractionDigits);
    }

    /**
     * Return money parser.
     * NOTE: Only used in Joomla.
     *
     * <code>
     * $this->prepareMoneyFormatter($container, $params);
     * $money = $this->getMoneyFormatter($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws BindException
     *
     * @return Parser
     */
    public static function getMoneyParser($container, $params)
    {
        $language         = \JFactory::getLanguage();
        $locale           = $language->getTag();

        $fractionDigits   = (int)$params->get('fraction_digits', 2);

        $moneyService     = new ContainerHelperMoney($container);
        return $moneyService->getParser($locale, $fractionDigits);
    }
}
