<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding Navigation Plugin
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgContentCrowdfundingNav extends JPlugin
{
    /**
     * @param string  $context
     * @param stdClass $article
     * @param Joomla\Registry\Registry $params
     *
     * @return null|string
     * @throws Exception
     */
    public function onContentBeforeDisplay($context, &$article, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (strcmp('com_crowdfunding.details', $context) !== 0) {
            return null;
        }

        // Load language
        $this->loadLanguage();

        $itemId = $app->input->getInt('id');
        $stats  = CrowdfundingHelper::getProjectData($itemId);

        $screen = $app->input->getCmd('screen', 'home');

        $html = '<ul class="nav nav-pills cf-plg-navigation">';

        if ($this->params->get('display_home')) {
            $class = 'class="cf-plg-nav-home';
            if (strcmp($screen, 'home') === 0) {
                $class .= ' active';
            }
            $class .= '"';

            $html .= '<li ' . $class . '><a href="' . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($article->slug, $article->catslug)) . '">' . JText::_('PLG_CONTENT_CROWDFUNDINGNAV_HOME') . "</a></li>";
        }

        if ($this->params->get('display_updates')) {
            $class = 'class="cf-plg-nav-updates';
            if (strcmp($screen, 'updates') === 0) {
                $class .= ' active';
            }
            $class .= '"';

            $stat = '<span class="badge">' . Joomla\Utilities\ArrayHelper::getValue($stats, 'updates', 0) . '</span>';
            $html .= '<li ' . $class . '><a href="' . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, "updates")) . '">' . JText::_('PLG_CONTENT_CROWDFUNDINGNAV_UPDATES') . ' ' . $stat . '</a></li>';
        }

        if ($this->params->get('display_comments')) {
            $class = 'class="cf-plg-nav-comments';
            if (strcmp($screen, 'comments') === 0) {
                $class .= ' active';
            }
            $class .= '"';

            if (!$params->get('comments_enabled', 1)) {
                $stat = '<span class="cf-dclabel">&nbsp;</span>';
            } else {
                $stat = '<span class="badge">' . Joomla\Utilities\ArrayHelper::getValue($stats, 'comments', 0) . '</span>';
            }

            $html .= '<li ' . $class . '><a href="' . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, 'comments')) . '">' . JText::_('PLG_CONTENT_CROWDFUNDINGNAV_COMMENTS') . ' ' . $stat . '</a></li>';
        }

        if ($this->params->get('display_funders')) {
            $class = 'class="cf-plg-nav-funders';
            if (strcmp($screen, 'funders') === 0) {
                $class .= ' active';
            }
            $class .= '"';

            $stat = '<span class="badge">' . Joomla\Utilities\ArrayHelper::getValue($stats, 'funders', 0) . '</span>';
            $html .= '<li ' . $class . '><a href="' . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, 'funders')) . '">' . JText::_('PLG_CONTENT_CROWDFUNDINGNAV_FUNDERS') . ' ' . $stat . '</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
