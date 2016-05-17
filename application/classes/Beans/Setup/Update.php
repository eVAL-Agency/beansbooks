<?php defined('SYSPATH') or die('No direct script access.');
/*
BeansBooks
Copyright (C) System76, Inc.

This file is part of BeansBooks.

BeansBooks is free software; you can redistribute it and/or modify
it under the terms of the BeansBooks Public License.

BeansBooks is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the BeansBooks Public License for more details.

You should have received a copy of the BeansBooks Public License
along with BeansBooks; if not, email info@beansbooks.com.
*/

class Beans_Setup_Update extends Beans_Setup {

	protected $_auth_role_perm = "UPDATE";

	public function __construct($data = NULL)
	{
		parent::__construct($data);
	}

	/**
	 * Get the next version string to upgrade to based on the filenames
	 * 
	 * If the next file doesn't exist, false is returned instead.
	 * 
	 * @param string $current_version
	 * @return bool|string
	 */
	protected function _get_next_update($current_version) {
		$updates = $this->_find_all_updates(Kohana::list_files('classes/Beans/Setup/Update/V'));

		if( ! in_array($current_version, $updates) ){
			$updates[] = $current_version;
		}
		
		// 'Naturally' sort these entries so the versions all line up in order.
		sort($updates, SORT_NATURAL);
		
		// Find the current version in the list of versions.
		$pos = array_search($current_version, $updates);
		if($pos === false){
			// No match found :(
			return false;
		}
		
		// The next is actually what I want.
		$pos++;
		if(!isset($updates[$pos])){
			// No future upgrade available!
			return false;
		}
		
		return $updates[$pos];
	}

	/**
	 * Sanitize the filenames out from the upgrade files to return only the raw version string itself.
	 * 
	 * @param array $files
	 * @return array
	 */
	protected function _find_all_updates($files) {
		$return_array = array();

		foreach( $files as $file ) {
			if( is_array($file) ) {
				$return_array = array_merge($return_array,$this->_find_all_updates($file));
			}
			else {
				// Kohana returns an array of filenames; the calling script expects them to only contain the version string itself.
				// As such, trim off the prefix and extension from the files.
				if(preg_match('/.*\.php$/', $file)){
					// Extension
					$file = substr($file, 0, -4);
				}
				
				if(($pos = strpos($file, 'Update/V/')) !== false){
					// Path and directory
					$file = substr($file, $pos+9);
				}
				
				// lastly, convert slashes to dots.
				$file = str_replace('/', '.', $file);
				$return_array[] = $file;
			}
		}

		return $return_array;
	}

	protected function _db_add_table_column($table_name, $column_name, $column_definition)
	{
		try
		{
			$table_column_exist_check = DB::Query(
				Database::SELECT, 
				'SELECT COUNT(COLUMN_NAME) as exist_check '.
				'FROM INFORMATION_SCHEMA.COLUMNS WHERE '.
				'TABLE_NAME = "'.$table_name.'" '.
				'AND COLUMN_NAME = "'.$column_name.'"'
			)->execute()->as_array();

			if( $table_column_exist_check[0]['exist_check'] == '0' )
			{
				DB::Query(
					NULL,
					'ALTER TABLE `'.$table_name.'` ADD `'.$column_name.'` '.$column_definition
				)->execute();
			}
		}
		catch( Exception $e )
		{
			throw new Exception('An error occurred when adding a column ('.$column_name.') to your database table('.$table_name.'): '.$e->getMessage());
		}
	}

	protected function _db_update_table_column($table_name, $column_name, $column_definition)
	{
		try
		{
			$table_column_exist_check = DB::Query(
				Database::SELECT, 
				'SELECT COUNT(COLUMN_NAME) as exist_check '.
				'FROM INFORMATION_SCHEMA.COLUMNS WHERE '.
				'TABLE_NAME = "'.$table_name.'" '.
				'AND COLUMN_NAME = "'.$column_name.'"'
			)->execute()->as_array();

			if( $table_column_exist_check[0]['exist_check'] == '0' )
				throw new Exception("Column ".$table_name.".".$column_name." does not exist.");

			DB::Query(
				NULL,
				'ALTER TABLE `'.$table_name.'` CHANGE `'.$column_name.'` '.$column_definition.' '
			)->execute();
		}
		catch( Exception $e )
		{
			throw new Exception('An error occurred when removing a column ('.$column_name.') from your database table('.$table_name.'): '.$e->getMessage());
		}
	}

	protected function _db_remove_table_column($table_name, $column_name)
	{
		try
		{
			$table_column_exist_check = DB::Query(
				Database::SELECT, 
				'SELECT COUNT(COLUMN_NAME) as exist_check '.
				'FROM INFORMATION_SCHEMA.COLUMNS WHERE '.
				'TABLE_NAME = "'.$table_name.'" '.
				'AND COLUMN_NAME = "'.$column_name.'"'
			)->execute()->as_array();

			if( $table_column_exist_check[0]['exist_check'] != '0' )
			{
				DB::Query(
					NULL,
					'ALTER TABLE `'.$table_name.'` DROP `'.$column_name.'`'
				)->execute();
			}
		}
		catch( Exception $e )
		{
			throw new Exception('An error occurred when removing a column ('.$column_name.') from your database table('.$table_name.'): '.$e->getMessage());
		}
	}

	protected function _db_add_table($table_name)
	{
		try
		{
			$table_exist_check = DB::Query(
				Database::SELECT, 
				'SELECT COUNT(TABLE_NAME) as exist_check '.
				'FROM INFORMATION_SCHEMA.TABLES WHERE '.
				'TABLE_NAME = "'.$table_name.'" '
			)->execute()->as_array();

			if( $table_exist_check[0]['exist_check'] == '0' )
			{
				DB::Query(
					NULL,
					'CREATE TABLE IF NOT EXISTS `'.$table_name.'` ( '.
					' `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, '.
					'  PRIMARY KEY (`id`) '.
					') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 '
				)->execute();
			}
		}
		catch( Exception $e )
		{
			throw new Exception('An error occurred when removing a table ('.$table_name.') database: '.$e->getMessage());
		}
	}

	protected function _db_remove_table($table_name)
	{
		try
		{
			$table_exist_check = DB::Query(
				Database::SELECT, 
				'SELECT COUNT(TABLE_NAME) as exist_check '.
				'FROM INFORMATION_SCHEMA.TABLES WHERE '.
				'TABLE_NAME = "'.$table_name.'" '
			)->execute()->as_array();

			if( $table_exist_check[0]['exist_check'] != '0' )
			{
				DB::Query(
					NULL,
					'DROP TABLE `'.$table_name.'`'
				)->execute();
			}
		}
		catch( Exception $e )
		{
			throw new Exception('An error occurred when removing a table ('.$table_name.') database: '.$e->getMessage());
		}
	}

}