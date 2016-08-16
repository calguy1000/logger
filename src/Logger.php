<?php

/**
 * The primary logging class to the logger library.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * The primary logging class for the logger library.
 *
 * @package   logger
 * @author    Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license   LGPL2.1
 */
class Logger
{
    /**
     * debug priority
     *
     * @var string
     */
    const PRIORITY_DEBUG = 'debug';

    /**
     * info priority
     *
     * @var string
     */
    const PRIORITY_INFO = 'info';

    /**
     * warning priority
     *
     * @var string
     */
    const PRIORITY_WARN = 'warn';

    /**
     * error priority
     *
     * @var string
     */
    const PRIORITY_ERROR = 'error';

    /**
     * @ignore
     */
    private $_filename;

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
        if (!$max_age_h) {
            $max_age_h = 30 * 24;
        }
        if (!$max_size_kb) {
            $max_size_kb = 10 * 1024;
        }
        if (!$keepmax) {
            $keepmax = 10;
        }

        // build and verify the absolute filename
        $bn = basename($filename);
        $dir = dirname($filename);
        if ($bn == $filename || !$dir) {
            $dir = getcwd();
            $filename = "$dir/$filename";
        }
        $dir = dirname($filename);
        if (!is_writable($dir)) {
            throw new \RuntimeException("Destination directory $dir is not writable");
        }
        if (is_file($filename) && !is_writable($filename)) {
            throw new \RuntimeException("$filename is not writable");
        }

        // validate the max_age and max_size
        $max_age_h = (int) $max_age_h;
        if ($max_age_h < 1 || $max_age_h > 60 * 24) {
            throw new \LogicException("Invalid value specified for max age");
        }

        $max_size_kb = (int) $max_size_kb;
        if ($max_size_kb < 16 || $max_size_kb > 50 * 1024) {
            throw new \LogicException("Invalid value specified for max_size");
        }

        $keepmax = (int) $keepmax;
        if ($keepmax < 1 || $keepmax > 1000) {
            throw new \LogicException("Invalid value for keepmax");
        }

        $this->_filename = $filename;
        $this->_max_age = $max_age_h;
        $this->_max_size = $max_size_kb;
        $this->_keepmax = $keepmax;
    }

    /**
     * @ignore
     */
    private function rotate()
    {
        if (!is_file($this->_filename)) {
            return;
        }
        clearstatcache($this->_filename);
        if (filesize($this->_filename) < $this->_max_size * 1024
            && filectime($this->_filename) > time() - $this->_max_age * 3600
        ) {
            return;
        }

        // gotta rotate
        $dest_pattern = $this->_filename.'.%d';
        $files = glob($this->_filename.'.*');
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
        rename($this->_filename, $dest_fn);
    }

    /**
     * @ignore
     */
    private function _last_line($fh)
    {
        $bufsiz = 4096;
        $orig_pos = ftell($fh);
        fseek($fh, $bufsiz * -1, SEEK_END);
        $buffer = fread($fh, $bufsiz);
        $lastline = null;
        if (strlen($buffer) > 0) {
            while (strlen($buffer) > 0 && $buffer[count($buffer)-1] == "\n") {
                $buffer = substr($buffer, 0, count($buffer)-1);
            }
            $lastline = trim(strrchr($buffer, "\n"));
            if (!$lastline) {
                $lastline = $buffer;
            }
        }
        fseek($fh, $orig_pos, SEEK_SET);
        return $lastline;
    }

    /**
     * @ignore
     */
    private function _new_item($msg, $section, $item, $priority)
    {
        // only keep the first line of the message.
        $msg = trim($msg);
        $pos = strpos($msg, "\n");
        if ($pos !== false) {
            $msg = substr($msg, 0, $pos);
        }
        $msg = rtrim($msg); // kill any nagging \r chars too.

        $out = array();
        $out['date'] = time();
        $out['priority'] = $priority;
        $out['repeats'] = 1;
        $out['section'] = $section;
        $out['item'] = $item;
        $out['msg'] = $msg;
        return $out;
    }

    /**
     * @ignore
     */
    private function _erase_last_line($fh)
    {
        $bufsiz = 4096;
        fseek($fh, $bufsiz * -1, SEEK_END);
        $start = ftell($fh);
        $buffer = fread($fh, $bufsiz);
        if (($pos = strrpos($buffer, "\n")) !== false) {
            $pos += $start;
            ftruncate($fh, $pos);
            fseek($fh, $pos);
        }
    }

    /**
     * @ignore
     */
    private function _write_item($fh, $item)
    {
        fseek($fh, 0, SEEK_END);
        $line = utils::item_to_line($item);
        if (ftell($fh) != 0) {
            $line = "\n".$line;
        }
        fwrite($fh, $line);
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
    public function log()
    {
        $this->rotate();

        $msg = $section = $item = null;
        $priority = self::PRIORITY_INFO;
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 4) {
            throw new \LogicException("Invalid arguments passed to ".__METHOD__);
        }
        $msg = $args[0];
        if (isset($args[1])) {
            $section = $args[1];
        }
        if (isset($args[2])) {
            $item = $args[2];
        }
        if (isset($args[3])) {
            $priority = $args[3];
        }

        // todo: validate arguments

        $item = $this->_new_item($msg, $section, $item, $priority);

        $fh = fopen($this->_filename, 'c+');
        if ($fh) {
            if (flock($fh, LOCK_EX)) {
                $lastline = $this->_last_line($fh);
                if ($lastline) {
                    $expanded = utils::line_to_item($lastline);
                    if ($this->_compare_item($item, $expanded)) {
                        // erase the last line
                        $this->_erase_last_line($fh);
                        // increment the repeats
                        $item = $expanded;
                        $item['date'] = time();
                        $item['repeats']++;
                    }
                }
                // write the new item
                $this->_write_item($fh, $item);
                flock($fh, LOCK_UN);
            }
            fclose($fh);
        }
    }

    /**
     * A convenience function to add a debug message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \LogicException
     */
    public function debug()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \LogicException("Invalid arguments passed to ".__METHOD__);
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        if (!isset($args[2])) {
            $args[2] = null;
        }
        $args[3] = self::PRIORITY_DEBUG;
        call_user_func_array(array($this,'log'), $args);
    }

    /**
     * A convenience function to add an info message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \LogicException
     */
    public function info()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \LogicException("Invalid arguments passed to ".__METHOD__);
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        if (!isset($args[2])) {
            $args[2] = null;
        }
        $args[3] = self::PRIORITY_INFO;
        call_user_func_array(array($this,'log'), $args);
    }

    /**
     * A convenience function to add a warning message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \LogicException
     */
    public function warn()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \LogicException("Invalid arguments passed to ".__METHOD__);
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        if (!isset($args[2])) {
            $args[2] = null;
        }
        $args[3] = self::PRIORITY_WARN;
        call_user_func_array(array($this,'log'), $args);
    }

    /**
     * A convenience function to add an error message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \LogicException
     */
    public function error()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \LogicException("Invalid arguments passed to ".__METHOD__);
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        if (!isset($args[2])) {
            $args[2] = null;
        }
        $args[3] = self::PRIORITY_ERROR;
        call_user_func_array(array($this,'log'), $args);
    }
} // end of class
