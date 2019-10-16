<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewDiscover extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var JApplicationSite
     */
    public $app;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $items;
    protected $pagination;

    protected $currency;
    protected $filterPaginationLimit;
    protected $socialProfiles;
    protected $layoutData;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->app         = JFactory::getApplication();
        $this->option      = $this->app->input->getCmd('option');

        $this->state       = $this->get('State');
        $this->items       = $this->get('Items');
        $this->pagination  = $this->get('Pagination');

        $this->params      = $this->state->get('params');

        $this->prepareItems($this->items);

        // Prepare social integration.
        $showAuthor                = CrowdfundingHelper::isShowAuthor($this->items, $this->params);
        if ($showAuthor) {
            $usersIds              = Prism\Utilities\ArrayHelper::getIds($this->items, 'user_id');
            $this->socialProfiles  = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $usersIds);
        }

        $this->layoutData                 = new stdClass;
        $this->layoutData->items          = $this->items;
        $this->layoutData->params         = $this->params;
        $this->layoutData->currency       = JoomlaFacade::getCurrency();
        $this->layoutData->moneyFormatter = JoomlaFacade::getMoneyFormatter();
        $this->layoutData->socialProfiles = $this->socialProfiles;
        $this->layoutData->imageFolder    = $this->params->get('images_directory', 'images/crowdfunding');

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }
    }

    private function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_DISCOVER_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->params->get('page_title', '');

        // Add title before or after Site Name
        if (!$title) {
            $title = $this->app->get('sitename');
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }

        $this->document->setTitle($title);
    }

    private function prepareItems($items)
    {
        $options   = array();

        $helperBus = new Prism\Helper\HelperBus($items);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemsHelper());

        // Count the number of funders.
        if (strcmp('items_grid_two', $this->params->get('grid_layout')) === 0) {
            $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemFundersHelper(JFactory::getDbo()));
        }

        $helperBus->handle($options);
    }
}
