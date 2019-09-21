<?php 
require 'vendor/autoload.php';
require 'config/db.php';

spl_autoload_register( function( $class) {
    if(strpos($class, 'Models') !== FALSE) {
        require_once str_replace('\\', '/', $class).'.php';
    }
}) ;

use Models\Database;

new Database();
