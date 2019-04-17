<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/12
 * Time: 12:05
 */

function cut_str($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8').'...';
    } else {
        return $text;
    }

}

function last_month($nowT,$i){
    $lastM1 = date('n',strtotime(" -".$i." month", strtotime("first day of 0 month",$nowT)));
    $lastM2 = date('n',strtotime(" -".$i." month", $nowT));
    if ($lastM1 != $lastM2){
        $expectD = date('Y-m-d',strtotime(" last day of -".$i." month", $nowT));
    }else{
        $expectD = date('Y-m-d',strtotime(" -".$i." month", $nowT));
    }
    return $expectD;
}

function get_the_month($date)
{
    $firstday = date('Y-m-01', strtotime($date));
    $lastday  = date('Y-m-d 23:59:59', strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}