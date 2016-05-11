<?php defined('SYSPATH') or die('No direct access allowed.');

 return ['default' => array (
  'type' => 'MySQLi',
  'connection' => 
  array (
    'hostname' => 'localhost',
    'database' => 'beans',
    'username' => 'beans',
    'password' => 'beanspass',
    'persistent' => false,
  ),
  'table_prefix' => '',
  'charset' => 'utf8',
  'caching' => false,
  'profiling' => true,
)];
