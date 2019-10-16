<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Container;

use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Constants;
use Prism\Money\MoneyFormatter;
use Prism\Utilities\StringHelper;
use Prism\Utilities\LocaleHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to inject Money and Currency objects in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6.6 Use Crowdfunding\Money\Helper
 */
trait MoneyHelperDeprecated
{
    /**
     * Prepare and return currency object.
     *
     * <code>
     * $this->prepareCurrency($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    protected function prepareCurrency($container, $params)
    {
        $currencyId   = $params->get('project_currency');
        $currencyHash = StringHelper::generateMd5Hash(Constants::CONTAINER_CURRENCY, 'deprecated_'.$currencyId);

        // Get the currency from the container.
        if (!$container->exists($currencyHash)) {
            $currency = new Currency(\JFactory::getDbo());
            $currency->load($currencyId);

            $container->set($currencyHash, $currency);
        }
    }

    /**
     * Return currency object.
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
     *
     * @return Currency
     */
    protected function getCurrency($container, $params)
    {
        $currencyId   = $params->get('project_currency');
        $currencyHash = StringHelper::generateMd5Hash(Constants::CONTAINER_CURRENCY, 'deprecated_'.$currencyId);

        // Get the currency from the container.
        if (!$container->exists($currencyHash)) {
            $this->prepareCurrency($container, $params);
            $currency = $container->get($currencyHash);
        } else {
            $currency = $container->get($currencyHash);
        }

        return $currency;
    }

    /**
     * Prepare money formatter.
     *
     * <code>
     * $this->prepareMoneyFormatter($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    protected function prepareMoneyFormatter($container, $params)
    {
        $currencyId = $params->get('project_currency');
        $moneyHash  = StringHelper::generateMd5Hash(Constants::CONTAINER_FORMATTER_MONEY, $currencyId);

        if (!$container->exists($moneyHash)) {
            // Get the currency from the container.
            $currency = $this->getCurrency($container, $params);

            // Prepare decimal pattern.
            $fractionDigits = (int)$params->get('fraction_digits', 2);
            $pattern        = '#,##0';
            if ($fractionDigits > 0) {
                $pattern .= '.' . str_repeat('0', $fractionDigits);
            }

            $formatter = LocaleHelper::getNumberFormatter($pattern);

            $money = new MoneyFormatter($formatter);
            $money->setCurrency($currency);

            $container->set($moneyHash, $money);
        }
    }

    /**
     * Return money formatter.
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
     *
     * @return MoneyFormatter
     */
    protected function getMoneyFormatter($container, $params)
    {
        $currencyId = $params->get('project_currency');
        $moneyHash  = StringHelper::generateMd5Hash(Constants::CONTAINER_FORMATTER_MONEY, $currencyId);

        if (!$container->exists($moneyHash)) {
            $this->prepareMoneyFormatter($container, $params);
            $money = $container->get($moneyHash);
        } else {
            $money = $container->get($moneyHash);
        }

        return $money;
    }
}
