<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Prism\Money\Money;
use Joomla\Registry\Registry;
use Crowdfunding\Currency\Currencies;
use Prism\Money\Formatter\IntlDecimalFormatter;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding Html Helper
 *
 * @package        ITPrism Components
 * @subpackage     Crowdfunding
 * @since          1.6
 */
abstract class JHtmlCrowdfundingBackend
{
    public static function approved($i, $value, $prefix, $checkbox = 'cb')
    {
        JHtml::_('bootstrap.tooltip');

        if (!$value) { // Disapproved
            $task  = $prefix . 'approve';
            $title = 'COM_CROWDFUNDING_APPROVE_ITEM';
            $class = 'ban-circle';
        } else {
            $task  = $prefix . 'disapprove';
            $title = 'COM_CROWDFUNDING_DISAPPROVE_ITEM';
            $class = 'ok';
        }

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $task . '\')" title="' . addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')) . '">';
        $html[] = '<i class="icon-' . $class . '"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }

    /**
     * Returns a published state on a grid
     *
     * @param   integer      $value    The state value.
     * @param   integer      $i        The row index
     * @param   string|array $prefix   An optional task prefix or an array of options
     * @param   boolean      $enabled  An optional setting for access control on the action.
     * @param   string       $checkbox An optional prefix for checkboxes.
     *
     * @return  string  The Html code
     *
     * @see     JHtmlJGrid::state
     * @since   11.1
     */
    public static function published($i, $value, $prefix = '', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix)) {
            $options  = $prefix;
            $enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            1  => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0  => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2  => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash')
        );

        return JHtmlJGrid::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }

    public static function reward($options)
    {
        $html = array();

        if (!$options['reward_id']) {
            $html[] = '--';
        } else {
            $html[] = '<select name="reward_state" class="js-reward-state '.$options['class'].'" id="reward_state_'.$options['reward_id'].'" data-id="'.$options['transaction_id'].'">';
            if (!$options['reward_state']) {
                $html[] = '<option value="0" selected>' . JText::_('COM_CROWDFUNDING_NOT_SENT') . '</option>';
                $html[] = '<option value="1">'.JText::_('COM_CROWDFUNDING_SENT').'</option>';
            } else {
                $html[] = '<option value="0">' . JText::_('COM_CROWDFUNDING_NOT_SENT') . '</option>';
                $html[] = '<option value="1" selected>'.JText::_('COM_CROWDFUNDING_SENT').'</option>';
            }

            $html[] = '</select>';
            $html[] = '<a href="'.JRoute::_('index.php?option=com_crowdfunding&view=rewards&pid=' . (int)$options['project_id']) . '&amp;filter_search=' . rawurlencode('id:' . $options['reward_id']).'" class="btn btn-mini" title="'.htmlspecialchars($options['reward_title'], ENT_QUOTES, 'UTF-8'). '"">';
            $html[] = '<i class="icon-link"></i>';
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    /**
     * @param   int  $i
     * @param   int  $value The state value
     * @param   bool $canChange
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function featured($i, $value = 0, $canChange = true)
    {
        $value = (int)$value;
        JHtml::_('bootstrap.tooltip');

        // Array of image, task, title, action
        $states = array(
            0 => array('unfeatured', 'projects.featured', 'COM_CROWDFUNDING_UNFEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_FEATURE'),
            1 => array('featured', 'projects.unfeatured', 'COM_CROWDFUNDING_FEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_UNFEATURE'),
        );

        $state = Joomla\Utilities\ArrayHelper::getValue($states, $value, $states[1]);
        $icon  = $state[0];
        if ($canChange) {
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value === 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"><i class="icon-' . $icon . '"></i></a>';
        } else {
            $html = '<a class="btn btn-micro hasTooltip disabled' . ($value === 1 ? ' active' : '') . '" title="' . JText::_($state[2]) . '"><i class="icon-' . $icon . '"></i></a>';
        }

        return $html;
    }

    public static function reason($value)
    {
        if (!$value) {
            return '';
        }

        JHtml::_('bootstrap.tooltip');

        $title = JText::sprintf('COM_CROWDFUNDING_STATUS_REASON', htmlspecialchars($value));

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="' . addslashes($title) . '">';
        $html[] = '<i class="icon-question"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }

    /**
     * @param null|Prism\Integration\Profile\ProfileInterface $socialProfile
     * @param int $userId
     *
     * @return string
     */
    public static function profileIcon($socialProfile, $userId)
    {
        $html = array();

        if ($socialProfile !== null) {
            $link = str_replace('/administrator', '', $socialProfile->getLink());
            $link = $str= ltrim($link, '/');

            $html[] = '<a href="'. JUri::root() .$link .'" class="btn btn-mini" target="_blank">';
            $html[] = '<i class="icon icon-user"></i>';
            $html[] = '</a>';
        } else {
            $html[] = '<a href="index.php?option=com_crowdfunding&view=users&filter_search=id:' . (int)$userId.'" class="btn">';
            $html[] = '<i class="icon icon-user"></i>';
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    /**
     * @param null|Prism\Integration\Profile\ProfileInterface $socialProfile
     * @param string $name
     * @param int $userId
     *
     * @return string
     */
    public static function profileLink($socialProfile, $name, $userId)
    {
        $html = array();

        if ($socialProfile !== null) {
            $link = str_replace('/administrator', '', $socialProfile->getLink());
            $link = $str= ltrim($link, '/');

            $html[] = '<a href="'. JUri::root() .$link .'" target="_blank">';
            $html[] = htmlentities($name, ENT_QUOTES, 'UTF-8');
            $html[] = '</a>';
        } else {
            $html[] = '<a href="index.php?option=com_crowdfunding&view=users&filter_search=id:' . (int)$userId.'">';
            $html[] = htmlentities($name, ENT_QUOTES, 'UTF-8');
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    /**
     * Generates tracking information about a transaction data.
     *
     * @param mixed $trackId
     *
     * @return string
     */
    public static function trackId($trackId)
    {
        if (!$trackId) {
            $output = JText::sprintf('COM_CROWDFUNDING_DATE_AND_TIME', '---');
        } else {
            if (!is_numeric($trackId)) {
                $output = JText::sprintf('COM_CROWDFUNDING_TRACK_ID', htmlentities($trackId, ENT_QUOTES, 'UTF-8'));
            } else {
                $validator = new Prism\Validator\Date($trackId);

                if (!$validator->isValid()) {
                    $output = JText::sprintf('COM_CROWDFUNDING_DATE_AND_TIME', '---');
                } else {
                    $date = new JDate($trackId);
                    $output = JText::sprintf('COM_CROWDFUNDING_DATE_AND_TIME', $date->format(DATE_RFC822));
                }
            }
        }

        return $output;
    }

    /**
     * Generates information about transaction amount.
     *
     * @param stdClass $item
     * @param IntlDecimalFormatter $moneyFormatter
     * @param Currencies $currencies
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    public static function transactionAmount($item, $moneyFormatter, $currencies)
    {
        $item->txn_amount = (float)$item->txn_amount;
        $item->fee        = (float)$item->fee;

        // Find a currency object by its code.
        $currency = null;
        foreach ($currencies as $currency_) {
            if (strcmp($item->txn_currency, $currency_->getCode()) === 0) {
                $currency = $currency_;
                break;
            }
        }

        if ($currency !== null) {
            $output = $moneyFormatter->formatCurrency(new Money($item->txn_amount, $currency));
        } else {
            $output = $item->txn_amount;
        }

        if ($item->fee > 0.00) {
            $fee = ($currency !== null) ? $moneyFormatter->formatCurrency(new Money($item->fee, $currency)) : $item->fee;

            // Prepare project owner amount.
            $projectOwnerAmount = round($item->txn_amount - $item->fee, 2);
            $projectOwnerAmount = ($currency !== null) ? $moneyFormatter->formatCurrency(new Money($projectOwnerAmount, $currency)) : $projectOwnerAmount;

            $title = JText::sprintf('COM_CROWDFUNDING_TRANSACTION_AMOUNT_FEE', $projectOwnerAmount, $fee);

            $output .= '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="' . addslashes($title) . '">';
            $output .= '<i class="icon-question"></i>';
            $output .= '</a>';
        }

        return $output;
    }

    public static function name($name, $userId = 0)
    {
        $output = array();

        if (!empty($name)) {
            if (!empty($userId)) {
                $output[] = '<a href="' . JRoute::_('index.php?option=com_crowdfunding&view=users&filter_search=id:' . (int)$userId) . '">';
                $output[] = htmlspecialchars($name, ENT_QUOTES);
                $output[] = '</a>';
            } else {
                $output[] = htmlspecialchars($name, ENT_QUOTES);
            }
        } else {
            $output[] = JText::_('COM_CROWDFUNDING_ANONYMOUS');
        }

        return implode("\n", $output);
    }

    public static function capturedPeriod(Registry $params)
    {
        $output = array();
        $capturedPeriod = $params->get('capture_period');

        if ($capturedPeriod !== null && (isset($capturedPeriod->start) && isset($capturedPeriod->end))) {
            $output[] = '<span class="icon icon-help hasPopover cursor-pointer" data-title="'.JText::_('COM_CROWDFUNDING_CAPTURING_PERIOD').'" data-content="'.JText::sprintf('COM_CROWDFUNDING_CAPTURING_PERIOD_S', $capturedPeriod->start, $capturedPeriod->end).'">';
            $output[] = '</span>';
        }

        return implode("\n", $output);
    }

    public static function type($title)
    {
        $title  = (string)$title;
        $output = array();

        if ($title !== '') {
            $output[] = '<div>';
            $output[] = JText::sprintf('COM_CROWDFUNDING_TYPE_S', htmlspecialchars($title));
            $output[] = '</div>';
        }

        return implode("\n", $output);
    }

    public static function category($title)
    {
        $title  = (string)$title;
        $output = array();

        if ($title !== '') {
            $output[] = '<div>';
            $output[] = JText::sprintf('COM_CROWDFUNDING_CATEGORY_S', htmlspecialchars($title));
            $output[] = '</div>';
        }

        return implode("\n", $output);
    }

    /**
     * Route URI to front-end.
     *
     * @param stdClass  $item
     * @param string  $website
     * @param JRouter $routerSite
     *
     * @return string
     */
    public static function siteRoute($item, $website, $routerSite)
    {
        $routedUri = $routerSite->build(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));
        if ($routedUri instanceof JUri) {
            $routedUri = $routedUri->toString();
        }

        if (false !== strpos($routedUri, '/administrator')) {
            $routedUri = str_replace('/administrator', '', $routedUri);
        }

        return $website.$routedUri;
    }

    /**
     * Return CSS class based on transaction status.
     *
     * @param string  $status
     *
     * @return string
     */
    public static function transactionColor($status)
    {
        switch ($status) {
            case 'completed':
                return 'success';
                break;

            case 'failed':
                return 'error';
                break;

            case 'canceled':
                return 'warning2';
                break;

            case 'refunded':
                return 'warning';
                break;

            case 'pending':
            default:
                return '';
                break;
        }
    }
}
