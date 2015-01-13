<?php
class Browser {
    public static function isBot(){
        $userAgent = $_SERVER['HTTP_USER_AGENT'];//"Mozilla/5.0 (compatible; YandexAdNet/1.0; +http://yandex.com/bots)" ;
        //$_SERVER['HTTP_USER_AGENT']
        return preg_match("/bot/i", $userAgent);
    }
}
?>