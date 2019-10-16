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
use Crowdfunding\Constants;
use Prism\Utilities\StringHelper;
use Prism\Utilities\LocaleHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare and inject NumberFormatter object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
trait NumberHelper
{
    /**
     * Prepare number formatter.
     *
     * <code>
     * $fractionDigits = 2;
     *
     * $this->prepareNumberFormatter($container, $fractionDigits);
     * </code>
     *
     * @param Container $container
     * @param int $fractionDigits
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return \NumberFormatter
     */
    protected function prepareNumberFormatter($container, $fractionDigits = 2)
    {
        $fractionDigits = (int)abs($fractionDigits);
        $numberHash     = StringHelper::generateMd5Hash(Constants::CONTAINER_FORMATTER_NUMBER, $fractionDigits);

        if (!$container->exists($numberHash)) {
            // Prepare decimal pattern.
            $pattern         = '#0';
            if ($fractionDigits > 0) {
                $pattern .= '.' .str_repeat('0', $fractionDigits);
            }

            $formatter = LocaleHelper::getNumberFormatter($pattern);

            $container->set($numberHash, $formatter);
        }
    }

    /**
     * Return number formatter.
     *
     * <code>
     * $fractionDigits = 2;
     *
     * $this->prepareNumberFormatter($container, $fractionDigits);
     * $numberFormatter = $this->getNumberFormatter($container, $fractionDigits);
     * </code>
     *
     * @param Container $container
     * @param int $fractionDigits
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter($container, $fractionDigits = 2)
    {
        $fractionDigits = (int)abs($fractionDigits);
        $numberHash     = StringHelper::generateMd5Hash(Constants::CONTAINER_FORMATTER_NUMBER, $fractionDigits);

        if (!$container->exists($numberHash)) {
            $this->prepareNumberFormatter($container, $fractionDigits);
            $numberFormatter = $container->get($numberHash);
        } else {
            $numberFormatter = $container->get($numberHash);
        }

        return $numberFormatter;
    }
}
