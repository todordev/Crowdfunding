<?php
/**
 * @package      Crowdfunding\Data
 * @subpackage   Transformer\Chart
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Data\Transformer\Chart;

use League\Fractal\TransformerAbstract;
use Prism\Money\Currency;
use Prism\Money\Formatter;
use Prism\Money\Money;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a country.
 *
 * @package      Crowdfunding\Data
 * @subpackage   Transformer\Chart
 */
class DailyFunds extends TransformerAbstract
{
    protected $formatter;
    protected $currency;

    public function __construct(Formatter $formatter, Currency $currency)
    {
        $this->formatter  = $formatter;
        $this->currency   = $currency;
    }

    public function transform(array $data)
    {
        $data['formatted_amount'] = $this->formatter->formatCurrency(new Money($data['amount'], $this->currency));

        return $data;
    }
}
