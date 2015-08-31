<?php

namespace calguy1000\logger;

final class utils
{
    private function __construct() {}

    public static function item_to_line($item)
    {
        $item['date'] = strftime('%d-%m-%Y %H:%M:%S',$item['date']);
        $line = implode(" // ",array_values($item));
        $line = substr($line,0,512);
        return $line;
    }

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