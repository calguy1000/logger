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
class FileLogger implements Logger
{

    /**
     * @ignore
     */
    private $_filename;

    /**
     * The primary constructor.
     *
     * This method validates the input parameters and trims them to limits.
     *
     * The filename parameter (and it's directory) are checked for appropriate permissions.  If a directory is not specified in the $filename parameter, the current working directory is assumed.
     *
     * @param  string                                          $filename    The destination filename.
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($filename)
    {
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

        $this->_filename = $filename;
    }

    /**
     * @ignore
     */
    public function __get($key)
    {
        switch( $key ) {
        case 'filename':
            return $this->_filename;

        default:
            throw new \InvalidArgumentException("$key is not a gettable member of ".__CLASS__);
        }
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

        $out = [];
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
    private function _item_to_line($item)
    {
        $item['date'] = strftime('%d-%m-%Y %H:%M:%S',$item['date']);
        $line = implode(" // ",array_values($item));
        $line = substr($line,0,512);
        return $line;
    }

    /**
     * @ignore
     */
    private function _line_to_item($line)
    {
        $fields = explode('//',$line,6);
        // date, priority, repeats, key, item, msg
        if( count($fields) != 6 ) return;

        $out= array();
        $out['date'] = strtotime(trim($fields[0]));
        $out['priority'] = trim($fields[1]);
        $out['repeats'] = (int) trim($fields[2]);
        $out['section'] = trim($fields[3]);
        $out['item'] = trim($fields[4]);
        $out['msg'] = trim($fields[5]);
        return $out;
    }

    /**
     * @ignore
     */
    private function _write_item($fh, $item)
    {
        fseek($fh, 0, SEEK_END);
        $line = $this->_item_to_line($item);
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
        return( $item1['priority'] == $item2['priority'] && $item1['section'] == $item2['section'] && $item1['item'] == $item2['item'] && $item1['msg'] == $item2['msg'] );
    }

    /**
     * The primary logging function.
     *
     * @param string $msg      The output message.
     * @param string $section  An optional section key
     * @param int    $item     An optional item key
     * @param string $priority The priority... must be one of the PRIORITY constants.  The default value is Logger::PRIORITY_INFO;
     * @throws \InvalidArgumentException
     */
    protected function log()
    {
        $msg = $section = $item = null;
        $priority = self::PRIORITY_INFO;
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 4) {
            throw new \InvalidArgumentException("Invalid arguments passed to ".__METHOD__);
        }
        if( is_array($args[0]) && count($args) == 1 ) $args = $args[0];
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
                    $expanded = $this->_line_to_item($lastline);
                    if ($this->_compare_item($expanded,$item)) {
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
     * @throws \InvalidArgumentException
     */
    public function debug()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \InvalidArgumentException("Invalid arguments passed to ".__METHOD__);
        }
        $args = array_merge( $args, [ null, null, null ] );
        $args[3] = self::PRIORITY_DEBUG;
        call_user_func_array([ $this,'log' ], $args);
    }

    /**
     * A convenience function to add an info message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function info()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \InvalidArgumentException("Invalid arguments passed to ".__METHOD__);
        }
        $args = array_merge( $args, [ null, null, null ] );
        $args[3] = self::PRIORITY_INFO;
        call_user_func_array([ $this, 'log' ], $args);
    }

    /**
     * A convenience function to add a warning message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function warn()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \InvalidArgumentException("Invalid arguments passed to ".__METHOD__);
        }
        $args = array_merge( $args, [ null, null, null ] );
        $args[3] = self::PRIORITY_WARN;
        call_user_func_array([ $this, 'log' ], $args);
    }

    /**
     * A convenience function to add an error message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function error()
    {
        $args = func_get_args();
        if (count($args) < 1 || count($args) > 3) {
            throw new \InvalidArgumentException("Invalid arguments passed to ".__METHOD__);
        }
        $args = array_merge( $args, [ null, null, null ] );
        $args[3] = self::PRIORITY_ERROR;
        call_user_func_array([ $this, 'log' ], $args);
    }
} // end of class
