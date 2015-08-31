<?php

namespace calguy1000\logger;

class ResultSet implements \Iterator
{
    private $_cur_offset = 0;
    private $_matches = array();
    private $_filter;
    private $_buffer;
    private $_bufsize = 4096;
    private $_cur_match = 0;

    public function __construct(query $filter)
    {
        $this->_filter = $filter;
        // validate that the file exists and is readable.

        $this->_execute();
    }

    private function _get_buffer($fh)
    {
        // if we have a buffer, that has a newline in it, just return the buffer
        $pos = strpos($this->_buffer,"\n");
        if( $pos !== FALSE ) return $this->_buffer;

        // if not.. read some characters from the end of the file
        $fpos = ftell($fh);
        $nbytes = $this->_bufsize - strlen($this->_buffer);
        $nbytes = min($fpos,$nbytes);
        fseek($fh,$nbytes * -1,SEEK_CUR);
        $fpos = ftell($fh);
        $this->_buffer = fread($fh,$nbytes) . $this->_buffer;
        fseek($fh,$fpos,SEEK_SET);
        return $this->_buffer;
    }

    private function _read_line($fh)
    {
        $line = null;
        $buffer = $this->_get_buffer($fh);
        if( strlen($buffer) ) {
            // read backwards through the buffer for a newline
            if( $buffer[count($buffer) - 1] == "\n" ) $buffer[count($buffer) - 1] = '\0';
            $pos = strrpos($buffer,"\n");
            if( $pos !== FALSE ) {
                $line = substr($buffer,$pos);
                $this->_buffer = substr($buffer,0,$pos);
            }
        }
        return $line;
    }

    private function _read_item($fh)
    {
        $line = $this->_read_line($fh);
        if( $line ) return utils::item_to_line($line);
    }

    private function _item_matches($item)
    {
        $filter = $this->_filter;
        $p_list = array(\calguy1000\logger\Logger::PRIORITY_DEBUG,
                        \calguy1000\logger\Logger::PRIORITY_INFO,
                        \calguy1000\logger\Logger::PRIORITY_WARN,
                        \calguy1000\logger\Logger::PRIORITY_ERROR);
        // here we test the given item to see if it matches the filter.
        if( $filter->start_time && $item['date'] < $filter->start_time ) return FALSE;
        if( $filter->end_time && $item['date'] < $filter->end_time ) return FALSE;

        // priority supports =,!,< and > operators.
        if( ($priority = $filter->priority) ) {
            $op = '=';
            if( in_array($priority[0],array('<','>','!','=')) ) {
                $op = $priority[0];
                $priority = substr($priority,1);
            }
            switch( $op ) {
            case '=':
                if( $item['priority'] != $priority ) return FALSE;
                break;

            case '!':
                if( $item['priority'] == $priority ) return FALSE;
                break;

            case '<':
                if( array_search($item['priority'],$p_list) >= array_search($priority,$p_list) ) return FALSE;
                break;

            case '>':
                if( array_search($item['priority'],$p_list) <= array_search($priority,$p_list) ) return FALSE;
                break;
            }
        }

        // item is an integer match only
        if( ($kitem = $filter->item) ) {
            if( $item['item'] != $kitem ) return FALSE;
        }

        // section, and msg support wildcard matches
        if( ($section = $filter->section) ) {
            if( !fnmatch($section,$item['section']) ) return FALSE;
        }

        if( ($msg = $filter->msg) ) {
            if( !fnmatch($msg,$item['msg']) ) return FALSE;
        }

        return TRUE;
    }

    private function _scan_file($filename)
    {
        $fh = null;
        try {
            // open the file, get a read lock
            $fh = fopen($filename,'r');
            if( !$fh ) throw new \RuntimeException('Could not open '.$fh.' for reading');
            if( !flock($fh,LOCK_SH) ) throw new \RuntimeException("Could not get read lock on ".$filename);

            // go to the end of the file
            fseek($fh,0,SEEK_END);

            while( ftell($fh) > 0 && count($this->_matches) < $this->_filter->limit ) {
                $item = $this->_read_item($fh);
                if( $item && $this->_item_matches($item) ) {
                    if( $this->_cur_offset >= $this->_filter->offset ) {
                        $this->_matches[] = $item;
                    }
                    $this->_cur_offset++;
                }
            }

            // release lock, close the file.
            flock($fh,LOCK_UN);
            fclose($fh);
        }
        catch( \Exception $e ) {
            flock($fh,LOCK_UN);
            fclose($fh);
            throw $e;
        }
    }

    private function _execute()
    {
        $files = array($this->_filter->filename);
        if( $this->_filter->use_archives ) {
            $pattern = $this->_filter->filename.'*';
            $files = glob($pattern);
        }
        if( !count($files) ) throw new \RuntimeException("There are no log files to query from");
        $files_idx = 0;
        while( $files_idx < count($files) && count($this->_matches) < $this->_filter->limit ) {
            echo "DEBUG: process ".$files[$files_idx]."\n";
            $this->_scan_file($files[$files_idx]);
            ++$files_idx;
            echo "DEBUG: found ".count($this->_matches)."\n";
        }
    }

    public function current()
    {
        return $this->_matches[$this->_cur_match];
    }

    public function key()
    {
        return $this->_cur_match;
    }

    public function next()
    {
        ++$this->_cur_match;
    }

    public function rewind()
    {
        $this->_cur_match = 0;
    }

    public function valid()
    {
        return isset($this->_matches[$this->_cur_match]);
    }

} // end of class

/**
 * usage:
 * $parms = array('filename'=>$filename,'limit'=>10,'priority'=>10);
 * $query = new \Logger\query($parms);
 * $rs = $query->execute();
 * foreach( $rs as $item ) {
 *   print_r( $item );
 * }
 */
