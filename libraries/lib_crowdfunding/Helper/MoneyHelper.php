<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Joomla\Registry\Registry;
use Crowdfunding\Currency;
use Prism\Money\Money;
use Prism\Utilities\LocaleHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to inject Money and Currency objects in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6.6 Use Crowdfunding\Container\Helper
 */
trait MoneyHelper
{
    /**
     * Return currency object.
     *
     * <code>
     * $currencyId = 1;
     * $currency   = $this->getCurrency($currencyId);
     * </code>
     *
     * @param int $currencyId
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Currency
     */
    protected function getCurrency($currencyId)
    {
        $currency = new Currency(\JFactory::getDbo());
        $currency->load($currencyId);

        return $currency;
    }

    /**
     * Return money formatter.
     *
     * <code>
     * $this->prepareMoneyFormatter($container, $params);
     * $money = $this->getMoneyFormatter($container, $params);
     * </code>
     *
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return Money
     */
    protected function getMoneyFormatter($params)
    {
        $currencyId = $params->get('project_currency');

        // Get the currency.
        $currency   = $this->getCurrency($currencyId);

        // Prepare decimal pattern.
        $fractionDigits = (int)$params->get('fraction_digits', 2);
        $pattern        = '#,##0';
        if ($fractionDigits > 0) {
            $pattern .= '.' . str_repeat('0', $fractionDigits);
        }

        $formatter = LocaleHelper::getNumberFormatter($pattern);

        $money = new Money($formatter);
        $money->setCurrency($currency);

        return $money;
    }
}
