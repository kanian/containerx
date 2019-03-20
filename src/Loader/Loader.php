<?php
namespace Kanian\ContainerX\Loader;


class Loader{
    
    public static function getLoader(){
        if(\Assoa\Loader\Loader::getLoader() === null){
            \Assoa\Loader\Loader::setLoader(require ("./vendor/autoload.php"));
        }
        return \Assoa\Loader\Loader::getLoader();
    }
}