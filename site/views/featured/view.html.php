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

class CrowdfundingViewFeatured extends JViewLegacy
{
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

    protected $numberInRow;
    protected $displayCreator;
    protected $socialProfiles;
    protected $layoutData;

    protected $option;
    protected $currency;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->getCmd('option');

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get params
        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        $this->numberInRow = (int)$this->params->get('featured_items_row', 3);
        $helperBus = new Prism\Helper\HelperBus($this->items);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemsHelper());
        $helperBus->handle();

        $this->displayCreator = (bool)$this->params->get('integration_display_creator', true);

        // Prepare integration. Load avatars and profiles.
        if ($this->displayCreator) {
            $userIds = Prism\Utilities\ArrayHelper::getIds($this->items, 'user_id');
            $this->socialProfiles  = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $userIds);
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
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_FEaTURED_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        $title = $this->params->get('page_title', '');

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
