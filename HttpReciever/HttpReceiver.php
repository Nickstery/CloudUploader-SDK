<?php

namespace HttpReceiver;
use \Dropbox as dbx;

class HttpReceiver{

    public static function get($name, $type){

        switch($type){
            case 'int':
                return intval($_GET[$name]);
            case 'string':
                return htmlspecialchars(strip_tags($_GET[$name]));
        }
        return '';
    }

}