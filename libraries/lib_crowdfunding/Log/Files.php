<?php
/**
 * @package      Crowdfunding
 * @subpackage   Logs
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Log;

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');

/**
 * This class provides functionality that manage log files.
 *
 * @package      Crowdfunding
 * @subpackage   Logs
 */
class Files implements \Iterator, \Countable, \ArrayAccess
{
    protected $items = array();

    /**
     * A list with files, which should be in the list with items.
     *
     * @var array
     */
    protected $files = array();

    protected $position = 0;

    /**
     * Initialize the object.
     *
     * <code>
     * $files = array(
     *    "file1", "file2",
     *    "file3", "file4"
     * );
     *
     * $logFiles   = new CrowdfundingLogFiles($files);
     * </code>
     *
     * @param array $files
     */
    public function __construct(array $files = array())
    {
        $this->files = $files;
    }

    /**
     * Get the list with files from logs folder
     *
     * <code>
     * $logFiles   = new CrowdfundingLogFiles();
     * $logFiles->load();
     *
     * foreach ($logFiles as $file) {
     * ....
     * }
     * </code>
     */
    public function load()
    {
        // Read files in folder /logs
        $config    = \JFactory::getConfig();
        /** @var  $config Registry */

        $logFolder = $config->get('log_path');

        $files = \JFolder::files($logFolder);
        if (!is_array($files)) {
            $files = array();
        }

        foreach ($files as $key => $file) {
            if (strcmp('index.html', $file) !== 0) {
                $this->items[] = \JPath::clean($logFolder . DIRECTORY_SEPARATOR . $files[$key]);
            }
        }

        if (count($this->files) > 0) {

            foreach ($this->files as $fileName) {

                // Check for a file in site folder.
                $errorLogFile = \JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $fileName);
                if (\JFile::exists($errorLogFile)) {
                    $this->items[] = $errorLogFile;
                }

                // Check for a file in admin folder.
                $errorLogFile = \JPath::clean(JPATH_BASE . DIRECTORY_SEPARATOR . $fileName);
                if (\JFile::exists($errorLogFile)) {
                    $this->items[] = $errorLogFile;
                }
            }
        }

        sort($this->items);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return (!array_key_exists($this->position, $this->items)) ? null : $this->items[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return array_key_exists($this->position, $this->items);
    }

    public function count()
    {
        return (int)count($this->items);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->items) ? $this->items[$offset] : null;
    }
}
