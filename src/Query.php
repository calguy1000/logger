<?php

/**
 * The class to define a query.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * This class defines a query for filtering data from a logfile.
 *
 * @package logger
 * @author Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license LGPL2.1
 * @property string $filename The primary log file name.
 * @property bool   $use_archives Wether or not to search through archives of the logfile.
 * @property int    $start_time A unix timestamp to filter results by.  No results before this time will be returned.
 * @property int    $end_time A unix timestamp to filter results by.  No results after this time will be returned.
 * @property string $priority A priority string from the Logger class.
 * @property string $section An optional section key to filter results by.  Wildcards are accepted.
 * @property int    $item An optional item key to filter results by.
 * @property string $msg An optional message string to filter results by.  Wildcards are accepted.
 * @property int    $limit The number of results to return.  The default, and maximum is 1000
 * @property int    $offset The offset of results to return (start returning matches after this number are found).  The default value is 0.
 */
class Query
{
    /**
     * @ignore
     */
    private $_data = array();

    /**
     * @constructor
     *
     * @param array $parms An associative array of properties for the object.
     */
    public function __construct($parms)
    {
        foreach( $parms as $key => $val ) {
            $this->$key = $val;
        }
    }

    /**
     * @ignore
     */
    public function __get($key)
    {
        switch( $key ) {
        case 'filename':
        case 'use_archives':
        case 'start_time':
        case 'end_time':
        case 'priority':
        case 'section':
        case 'item':
        case 'msg':
        case 'limit':
        case 'offset':
            if( isset($this->_data[$key]) ) return $this->_data[$key];
            break;

        default:
            throw new \LogicException("$key is not a valid member of ".__CLASS__);
        }
    }

    /**
     * @ignore
     */
    public function __set($key,$val)
    {
        $val = trim($val);
        switch( $key ) {
        case 'filename':
            if( !is_file($val) || !is_readable($val) ) throw new \LogicException("$val is not readable");
            $this->_data[$key]= $val;
            break;

        case 'use_archives':
            $val = (bool) $val;
            $this->_data[$key] = $val;
            break;

        case 'end_time':
        case 'start_time':
            $val = max(0,$val);
            $this->_data[$key] = $val;
            break;

        case 'priority':
            $tmp = $val;
            if( $val[0] == '<' || $val[0] == '>' || $val[0] == '!' || $val[0] == '=') {
                $tmp = substr($val,1);
            }
            switch( $tmp ) {
            case Logger::PRIORITY_DEBUG:
            case Logger::PRIORITY_INFO:
            case Logger::PRIORITY_WARN:
            case Logger::PRIORITY_ERROR:
                $this->_data[$key] = $val;
                break;
            default:
                throw new \LogicException("$val is an invalid value for the priority of a ".__CLASS__);
            }
            break;

        case 'section':
        case 'item':
        case 'msg':
            $this->_data[$key] = $val;
            break;

        case 'limit':
            $val = (int) $val;
            $val = max(1,min(1000,$val));
            $this->_data[$key] = $val;
            break;

        case 'offset':
            $val = (int) $val;
            $offset = max(0,$val);
            $this->_data[$key] = $val;
            break;

        default:
            throw new \LogicException("$key is not a valid member of ".__CLASS__);
        }
    }

    /**
     * Execute the query given the current parameters.
     *
     * @return ResultSet
     */
    public function &execute()
    {
        // results in a resultset object
        if( !isset($this->_data['filename']) ) throw new \LogicException("A filename must be provided to ".__CLASS__);
        $obj = new ResultSet($this);
        return $obj;
    }
} // end of class