<?php

namespace backend\models;


class Helper{

  public static function url_to_link($str){
    //$re = '/https?:\/\/(?:www\.)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b)*(\/[\/\d\w\.-]*)*(?:[\?])*(.+)*/i';


    $re = '/(https?:)?\/\/(?:www\.)?([-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b)*(\/[\/\d\w\.-]*)*(?:[\?])*(.+)*/i';

    $subst = '<a href="$0" target="_blank">$0</a>';

    $result['data'] = preg_replace($re, $subst, $str,$limit = -1,$cnt);
    $result['cnt'] = $cnt;
    return $result;
  }

}