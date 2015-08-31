<?php

/**
 * A utilties class for the logger library.
 *
 * @package logger
 */
namespace calguy1000\logger;

/**
 * Some convenient internal utilities for the logger.
 *
 * @ignore
 * @package logger
 * @author Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license LGPL2.1
 */
final class utils
{
    /**
     * @ignore
     */
    private function __construct() {}

    /**
     * Convert an item to a line suitable for writing.
     * The line does not include newlines.
     *
     * @ignore
     * @param array $item An array containing date, priority, repeats, section, item and message keys.
     * @return string
     */
    public static function item_to_line($item)
    {
        $item['date'] = strftime('%d-%m-%Y %H:%M:%S',$item['date']);
        $line = implode(" // ",array_values($item));
        $line = substr($line,0,512);
        return $line;
    }

    /**
     * Parse a logger line into an item for use in comparisons and output.
     *
     * @ignore
     * @param string $line
     * @return array An array containing date, priority, section, item and message keys.
     */
    public static function line_to_item($line)
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
}