<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;
use Crowdfunding\Category\Helper\Gateway\Joomla\ProjectCounter;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewCategory extends JViewLegacy
{
    use Crowdfunding\Helper\AccessHelper;

    /**
     * @var JDocumentHtml
     */
    public $document;

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

    protected $money;
    protected $filterPaginationLimit;
    protected $socialProfiles;

    protected $categories;
    protected $displaySubcategories;
    protected $subcategoriesPerRow;
    protected $displayProjectsNumber;
    protected $projectsNumber;

    /**
     * @var JCategoryNode
     */
    protected $item;

    protected $layoutData;

    protected $option;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function display($tpl = null)
    {
        $this->app      = JFactory::getApplication();
        $this->option   = $this->app->input->getCmd('option');

        // Get current category
        $categoryId     = $this->app->input->getInt('id');
        $categories     = CrowdfundingCategories::getInstance('crowdfunding');
        $this->item     = $categories->get($categoryId);

        // Check access view.
        $user = JFactory::getUser();
        if (!$this->item || !$this->canView($this->app, $user)) {
            $this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->app->setHeader('status', 403, true);
            return;
        }

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        // Prepare subcategories.
        $this->displaySubcategories = (bool)$this->params->get('category_show_subcategories', Prism\Constants::DO_NOT_DISPLAY);
        if ($this->displaySubcategories) {
            $this->prepareSubcategories();
        }

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
            $this->params->def('page_heading', $this->item->title);
        }
    }

    private function preparePageTitle()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page title
        if (!$menu) {
            $title = $this->item->title;
        } else {
            $title = $this->params->get('page_title', '');
        }

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

    private function prepareSubcategories()
    {
        $this->categories = $this->item->getChildren();

        $this->subcategoriesPerRow   = $this->params->get('categories_items_row', 3);
        $this->displayProjectsNumber = $this->params->get('categories_show_projects_number', 0);

        $options   = array(
            'count_projects' => $this->displayProjectsNumber,
            'project_state'  => array(
                'state'    => Prism\Constants::PUBLISHED,
                'approved' => Prism\Constants::APPROVED
            )
        );

        $helperBus = new Prism\Helper\HelperBus($this->categories);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareCategoriesHelper(new ProjectCounter(JFactory::getDbo())));
        $helperBus->handle($options);
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
