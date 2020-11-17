<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends\ Codeception\ Module {
    public function _beforeSuite( $settings = array() ) {
        $path = realpath(__DIR__ . '/../../..').DIRECTORY_SEPARATOR."wordpress".DIRECTORY_SEPARATOR;
        
        if( !defined('ABSPATH') ){ define( "ABSPATH", $path );}
        if( !defined('WPINC') ){ define( "WPINC", 'wp-includes' );}
    }
}