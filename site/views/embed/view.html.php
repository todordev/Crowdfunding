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

class CrowdfundingViewEmbed extends JViewLegacy
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

    protected $item;

    protected $currency;
    protected $moneyFormatter;
    protected $imageFolder;
    protected $embedLink;
    protected $socialProfileLink;
    protected $displayCreator;
    protected $embedCode;
    protected $form;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        /** @var $this->app JApplicationSite */

        $this->option = JFactory::getApplication()->input->get('option');
        
        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');

        // Get params
        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        $this->imageFolder = $this->params->get('images_directory', 'images/crowdfunding');

        if (!$this->item) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Integrate with social profile.
        $this->displayCreator = $this->params->get('integration_display_creator', true);

        // Prepare integration. Load avatars and profiles.
        if ($this->displayCreator && (is_object($this->item) && $this->item->user_id > 0)) {
            $socialProfile = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $this->item->user_id);
            $this->socialProfileLink  = (!$socialProfile) ? null : $socialProfile->getLink();
        }

        // Set a link to project page
        $uri              = JUri::getInstance();
        $host             = $uri->toString(array('scheme', 'host'));
        $this->item->link = $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);

        // Set a link to image
        $this->item->link_image = $host . '/' . $this->imageFolder . '/' . $this->item->image;

        $this->embedCode = $this->prepareEmbedCode($this->item, $host);

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Generate HTML code for embeding.
     *
     * @param stdclass $item
     * @param string $host
     *
     * @return string
     */
    protected function prepareEmbedCode($item, $host)
    {
        // Generate embed link
        $embedLink = $host . JRoute::_(CrowdfundingHelperRoute::getEmbedRoute($item->slug, $item->catslug) . '&layout=widget&tmpl=component', false);

        return '<iframe src="' . $embedLink . '" width="280px" height="560px" frameborder="0" scrolling="no"></iframe>';
    }

    protected function prepareDocument()
    {
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $this->app->getPathway();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb);

        // Add scripts
        JHtml::_('jquery.framework');
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
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->item->title . ' | ' . JText::_('COM_CROWDFUNDING_EMBED_CODE');

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
}
