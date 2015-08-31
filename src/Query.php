<?php

namespace calguy1000\logger;

class Query
{
    private $_data = array();

    public function __construct($parms)
    {
        foreach( $parms as $key => $val ) {
            $this->$key = $val;
        }
    }

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

    public function &execute()
    {
        // results in a resultset object
        if( !isset($this->_data['filename']) ) throw new \LogicException("A filename must be provided to ".__CLASS__);
        $obj = new ResultSet($this);
        return $obj;
    }
} // end of class