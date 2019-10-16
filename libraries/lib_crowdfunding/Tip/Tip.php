<?php
/**
 * @package      Crowdfunding
 * @subpackage   Tips
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Tip;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods used for managing a tip.
 *
 * @package      Crowdfunding
 * @subpackage   Tips
 */
class Tip
{
    /**
     * All tips and tricks
     *
     * @var mixed
     */
    protected $tips = array();

    /**
     * Initialize the object.
     *
     * <code>
     * $tip   = new Crowdfunding\Tip();
     * </code>
     */
    public function __construct()
    {
        $this->tips = array(
            'COM_CROWDFUNDING_TIP_RESOURCES', 'COM_CROWDFUNDING_TIP_CATEGORIES', 'COM_CROWDFUNDING_TIP_TEMPLATE',
            'COM_CROWDFUNDING_TIP_EMAIL_TEMPLATES', 'COM_CROWDFUNDING_TIP_CONFIRMATION_EMAILS', 'COM_CROWDFUNDING_TIP_PLG_SYSTEM_CROWDFUNDING',
            'COM_CROWDFUNDING_TIP_SPECIFIC_MODULES', 'COM_CROWDFUNDING_TIP_SETTINGS', 'COM_CROWDFUNDING_TIP_INTRO_ARTICLE',
            'COM_CROWDFUNDING_TIP_MODULES_PLUGINS', 'COM_CROWDFUNDING_TIP_MENU_ITEMS', 'COM_CROWDFUNDING_TIP_DEBUG',
            'COM_CROWDFUNDING_TIP_PLG_CONTENT_VALIDATOR'
        );
    }

    /**
     * Return a tip. If you provide an index, it will return the tip assigned to that index.
     * Otherwise, it will return random tip.
     * NOTE: It return language constant that you have to translate using JText.
     *
     * <code>
     * $tip  = new Crowdfunding\Tip();
     * $languageConstant = $tip->getTip();
     *
     * echo JText::_($languageConstant);
     * </code>
     *
     * @param int|null $index
     *
     * @return string
     */
    public function getTip($index = null)
    {
        if ($index === null or !array_key_exists($index, $this->tips)) {
            $index = array_rand($this->tips);
        }

        return $this->tips[$index];
    }
}
