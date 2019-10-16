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
class ProjectFunds extends TransformerAbstract
{
    protected $formatter;
    protected $currency;

    public function __construct(Formatter $formatter, Currency $currency)
    {
        $this->formatter  = $formatter;
        $this->currency   = $currency;
    }

    public function transform(array $projectData)
    {
        $data = array(
            'goal'      => ['label' => '', 'amount' => 0],
            'funded'    => ['label' => '', 'amount' => 0]
        );

        if (!$projectData) {
            return $data;
        }

        // Goal
        $data['goal']['label']  = \JText::sprintf('COM_CROWDFUNDING_GOAL_S', $this->formatter->formatCurrency(new Money($projectData['goal'], $this->currency)));
        $data['goal']['amount'] = (float)$projectData['goal'];

        // Funded
        $data['funded']['label']   = \JText::sprintf('COM_CROWDFUNDING_FUNDED_S', $fundedFormatted = $this->formatter->formatCurrency(new Money($projectData['funded'], $this->currency)));
        $data['funded']['amount']  = (float)$projectData['funded'];

        if ((float)$projectData['remaining'] < (float)$projectData['goal']) {
            $data['remaining']['label']  = \JText::sprintf('COM_CROWDFUNDING_REMAINING_S', $this->formatter->formatCurrency(new Money($projectData['remaining'], $this->currency)));
            $data['remaining']['amount'] = (float)$projectData['remaining'];
        }

        return $data;
    }
}
