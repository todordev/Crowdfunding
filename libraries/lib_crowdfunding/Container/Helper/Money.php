<?php
/**
 * @package      Crowdfunding
 * @subpackage   Money
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Container\Helper;

use Joomla\DI\Container;
use Prism\Domain\BindException;
use Prism\Utilities\StringHelper;
use Prism\Money\Parser\IntlDecimalParser;
use Prism\Money\Formatter\IntlDecimalFormatter;

use Crowdfunding\Currency\Mapper;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Currency\Repository;
use Crowdfunding\Currency\Gateway\CurrencyGateway;

/**
 * This class provides a methods used in the process of preparing Money objects.
 *
 * @package      Crowdfunding
 * @subpackage   Money
 */
class Money
{
    const CONTAINER_CURRENCY = 'com_crowdfunding.currency';
    const CONTAINER_FORMATTER_MONEY = 'com_crowdfunding.money.formatter';
    const CONTAINER_PARSER_MONEY = 'com_crowdfunding.money.parser';

    /**
     * @var Container
     */
    private $container;

    /**
     * Helper constructor.
     *
     * @param Container       $container DI container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Return a currency object. It tries to get it from the container.
     * If the object does not exist in the container, it will create an object and will add it to the container.
     *
     * <code>
     * $currencyId  = 1;
     * $moneyHelper = new Money($container);
     * $currency    = $this->getCurrency($currencyId, new JoomlaGateway(JFactory::getDbo()));
     * </code>
     *
     * @param int $currencyId
     * @param CurrencyGateway $gateway Database gateway that will be used to fetch data by the repository.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws BindException
     *
     * @return Currency
     */
    public function getCurrency($currencyId, CurrencyGateway $gateway)
    {
        $currencyHash  = StringHelper::generateMd5Hash(self::CONTAINER_CURRENCY, $currencyId);

        if (!$this->container->exists($currencyHash)) {
            if (!$currencyId) {
                return new Currency();
            }

            $repository = new Repository(new Mapper($gateway));
            $currency   = $repository->fetchById($currencyId);

            $this->container->set($currencyHash, $currency);
        } else {
            $currency = $this->container->get($currencyHash);
        }

        return $currency;
    }

    /**
     * Return money formatter object. It tries to get it from the container.
     * If the object does not exist in the container, it will create an object and will add it to the container.
     *
     * <code>
     * $moneyHelper     = new Helper($container);
     * $moneyFormatter  = $moneyHelper->getFormatter('bg-BG', 2);
     * </code>
     *
     * @param string $locale
     * @param int $digits
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return IntlDecimalFormatter
     */
    public function getFormatter($locale, $digits = 2)
    {
        $formatterHash  = StringHelper::generateMd5Hash(self::CONTAINER_FORMATTER_MONEY, $locale);

        if (!$this->container->exists($formatterHash)) {
            $numberFormatter = $this->prepareIntlFormatter($locale, $digits);

            $formatter  = new IntlDecimalFormatter($numberFormatter);

            $this->container->set($formatterHash, $formatter);
        } else {
            $formatter = $this->container->get($formatterHash);
        }

        return $formatter;
    }

    /**
     * Return money parser object. It tries to get it from the container.
     * If the object does not exist in the container, it will create an object and will add it to the container.
     *
     * <code>
     * $locale  =   'bg-BG';
     * $digits  =   2;
     *
     * $moneyHelper  = new Helper($container);
     * $moneyParser  = $moneyHelper->getParser($locale, $digits);
     * </code>
     *
     * @param string $locale
     * @param int $digits
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return IntlDecimalParser
     */
    public function getParser($locale, $digits = 2)
    {
        $parserHash  = StringHelper::generateMd5Hash(self::CONTAINER_PARSER_MONEY, $locale);

        if (!$this->container->exists($parserHash)) {
            $numberFormatter = $this->prepareIntlFormatter($locale, $digits);

            $parser  = new IntlDecimalParser($numberFormatter);

            $this->container->set($parserHash, $parser);
        } else {
            $parser = $this->container->get($parserHash);
        }

        return $parser;
    }

    /**
     * @param string $locale
     * @param int $digits
     *
     * @return \NumberFormatter
     */
    protected function prepareIntlFormatter($locale, $digits = 2)
    {
        // Prepare decimal pattern.
        $fractionDigits = (int)$digits;
        $pattern        = '#,##0';
        if ($fractionDigits > 0) {
            $pattern .= '.' . str_repeat('0', $fractionDigits);
        }

        $numberFormatter  = new \NumberFormatter($locale, \NumberFormatter::PATTERN_DECIMAL, $pattern);

        return $numberFormatter;
    }
}
