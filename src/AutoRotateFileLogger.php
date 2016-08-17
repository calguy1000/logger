<?php

/**
 * The primary logging class to the logger library.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * A logging class that logs to file.
 *
 * @package   logger
 * @author    Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license   LGPL2.1
 */
class AutoRotateFileLogger extends FileLogger
{
    /**
     * @ignore
     */
    private $_rotated;

    /**
     * @ignore
     */
    private $_max_age;

    /**
     * @ignore
     */
    private $_max_size;

    /**
     * @ignore
     */
    private $_keepmax = 10;

    /**
     * The primary constructor.
     *
     * This method validates the input parameters and trims them to limits.
     *
     * The filename parameter (and it's directory) are checked for appropriate permissions.  If a directory is not specified in the $filename parameter, the current working directory is assumed.
     *
     * @param  string                                          $filename    The destination filename.
     * @param  int                                             $max_age_h   The maximum age of a log file before rotation.  Default is 30*24.  Max is 60*24.
     * @param  int                                             $max_size_kb The maximum size of a file before rotation (kilobytes).  Default is 10*1024.  Max is 50*1024
     * @param  int keepmax The maximum number of files to keep.  Default is 10.
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function __construct($filename, $max_age_h = null, $max_size_kb = null, $keepmax = null)
    {
        parent::__construct($filename);

        if (!$max_age_h) {
            $max_age_h = 30 * 24;
        }
        if (!$max_size_kb) {
            $max_size_kb = 10 * 1024;
        }
        if (!$keepmax) {
            $keepmax = 10;
        }

        $this->_max_age = $max_age_h;
        $this->_max_size = $max_size_kb;
        $this->_keepmax = $keepmax;
    }

    /**
     * @ignore
     */
    private function rotate()
    {
        if( $this->_rotated ) {
            return;
        }
        if (!is_file($this->filename)) {
            return;
        }
        clearstatcache($this->filename);
        if (filesize($this->filename) >= $this->_max_size * 1024
            && filectime($this->filename) >= time() - $this->_max_age * 3600
        ) {
            return;
        }

        // gotta rotate
        $dest_pattern = $this->filename.'.%d';
        $files = glob($this->filename.'.*');
        if (is_array($files) && count($files)) {
            for ($i = $this->_keepmax - 1; $i > 0; $i--) {
                $test_fn = sprintf($dest_pattern, $i);
                if (is_file($test_fn)) {
                    if ($i == $this->_keepmax) {
                        // only keeping a certain many of these.
                        unlink($test_fn);
                    } else {
                        // rename the file, incremeinging the number
                        $dest_fn = sprintf($dest_pattern, $i+1);
                        rename($test_fn, $dest_fn);
                    }
                }
            }
        }
        $dest_fn = sprintf($dest_pattern, 1);
        rename($this->filename, $dest_fn);
        $this->_rotated = 1;
    }


    /**
     * @ignore
     */
    private function _compare_item($item1, $item2)
    {
        return( $item1['priority'] == $item2['priority'] && $item1['section'] == $item2['section'] && $item1['item'] == $item2['item'] && $item1['msg'] = $item2['msg'] );
    }

    /**
     * The primary logging function.
     *
     * @param string $msg      The output message.
     * @param string $section  An optional section key
     * @param int    $item     An optional item key
     * @param string $priority The priority... must be one of the PRIORITY constants.  The default value is Logger::PRIORITY_INFO;
     */
    protected function log()
    {
        $this->rotate();
        $args = func_get_args();
        call_user_func( [ 'parent', __FUNCTION__], $args);
    }
} // end of class
