<?php defined('SYSPATH') or die('No direct access allowed.');
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

class Controller_Update extends Controller_View {

	public function before()
	{
		$setup_check = new Beans();
		$setup_check_result = $setup_check->execute();
		
		if(
			PHP_SAPI != 'cli' && 
			$this->request->action() != 'manual' &&
			(
				$setup_check_result->success ||
				! isset($setup_check_result->config_error) ||
				strpos(strtolower($setup_check_result->config_error),'update') === FALSE
			)
		){
			$this->redirect('/');
		}
		
		parent::before();	

		$this->_view->head_title = "Update BeansBooks";
		$this->_view->page_title = "Updates Ready to Install";
	}

	public function action_index() {
		// Update!
		
		$setup_update_pending = new Beans_Setup_Update_Pending((object)array(
			'auth_uid' => "UPDATE",
			'auth_key' => "UPDATE",
			'auth_expiration' => "UPDATE",
		));
		$setup_update_pending_result = $setup_update_pending->execute();

		// Check if there is a legacy config file; this has been ported to the Kohana-standard locations on the eVAL fork of the project.
		$file = Kohana::find_file('classes', 'beans/config');
		
		if(
			$file !== false && 
			isset($setup_update_pending_result->data->current_version) && 
			$setup_update_pending_result->data->current_version === false
		){
			// A legacy file exists and the current database is not readable.... good candidate for a legacy upgrade!
			$path = ROOT_PDIR . 'application/config/';
			if(!is_dir($path)){
				// Abort if the path doesn't exist yet, (not sure how this would happen, but that's what a sanity check is for!)
				die('Please create "' . $path . '" and refresh this page to continue.');
			}
			
			if(!is_writable($path)){
				die('Please ensure that "' . $path . '" is writable by the web server and refresh this page to continue!');
			}
			
			$config = include_once($file);
			
			// Restructure this legacy configuration to the new standard.
			$config_encrypt  = $config['modules']['encrypt']['default'];
			$config_database = $config['modules']['database']['default'];
			$config_email    = $config['modules']['email'];
			$config_url      = [
				'trusted_hosts' => [
					str_replace('.', '\\.', $_SERVER['SERVER_NAME'])
				],
			];
			$config_beans    = [
				'sha_hash' => $config['sha_hash'],
				'sha_salt' => $config['sha_salt'],
				'cookie_salt' => $config['cookie_salt'],
			];

			if(function_exists('mysqli_connect')){
				$config_database['type'] = 'MySQLi';
			}
			elseif(function_exists('mysql_connect')){
				$config_database['type'] = 'MySQL';
			}
			else{
				die('Only MySQL or MySQLi data drivers are supported!');
			}

			// php file header for config files.
			$configHeader = "<?php defined('SYSPATH') or die('No direct access allowed.');\n\n return ";
			$configFooter = ";\n";

			// Write each config to its own file.
			file_put_contents(
				APPPATH . 'config/beans.php',
				$configHeader .
				var_export($config_beans, true) .
				$configFooter
			);

			file_put_contents(
				APPPATH . 'config/database.php',
				$configHeader .
				"['default' => " .
				var_export($config_database, true) .
				"]" .
				$configFooter
			);

			file_put_contents(
				APPPATH . 'config/email.php',
				$configHeader .
				var_export($config_email, true) .
				$configFooter
			);

			file_put_contents(
				APPPATH . 'config/encrypt.php',
				$configHeader .
				"['default' => " .
				var_export($config_encrypt, true) .
				"]" .
				$configFooter
			);

			file_put_contents(
				APPPATH . 'config/url.php',
				$configHeader .
				var_export($config_url, true) .
				$configFooter
			);
			
			die('Migrated configuration parameters from ' . APPPATH . 'beans/config.php to ' . APPPATH . 'config/*.php!  Please refresh the page to continue.');
		}

		

		if( $this->_beans_result_check($setup_update_pending_result) )
			$this->_view->setup_update_pending_result = $setup_update_pending_result;
	}

	public function action_run() {
		// Run Update!
		$target_version = $this->request->post('target_version');

		if( ! $target_version ){
			$this->redirect('/update/');
		}

		$setup_update_run = new Beans_Setup_Update_Run((object)array(
			'auth_uid' => "UPDATE",
			'auth_key' => "UPDATE",
			'auth_expiration' => "UPDATE",
			'target_version' => $target_version,
		));
		$setup_update_run_result = $setup_update_run->execute();

		if( $setup_update_run_result->success ) {
			Session::instance()->set('global_success_message','Your instance of BeansBooks has been successfully upgraded to version '.$setup_update_run_result->data->current_version);
			$this->redirect('/');
		}
		else {
			Session::instance()->set('global_error_message',$setup_update_run_result->error);
			$this->redirect('/update');
		}
	}

	public function action_manual()
	{
		set_time_limit(60 * 10);
		
		if( PHP_SAPI != 'cli' ){
			$this->redirect('/');
		}

		if( ! file_exists(APPPATH.'classes/beans/config.php') OR 
			filesize(APPPATH.'classes/beans/config.php') < 1 )
			die("Error: Missing config.php\n");

		$setup_update_pending = new Beans_Setup_Update_Pending((object)array(
			'auth_uid' => "UPDATE",
			'auth_key' => "UPDATE",
			'auth_expiration' => "UPDATE",
		));
		$setup_update_pending_result = $setup_update_pending->execute();

		if( ! $setup_update_pending_result->success )
		{
			die(
				"Error querying version info: ".
				$setup_update_pending_result->error.
				$setup_update_pending_result->auth_error.
				$setup_update_pending_result->config_error."\n"
			);
		}

		if( $setup_update_pending_result->data->current_version == $setup_update_pending_result->data->target_version )
			die('BeansBooks is already fully updated to the local source version '.$setup_update_pending_result->data->current_version.'.'."\n");

		// Run all possible updates.
		$target_version = $setup_update_pending_result->data->target_version;
		$current_version = $setup_update_pending_result->data->current_version;
		while( $current_version != $target_version )
		{
			$setup_update_run = new Beans_Setup_Update_Run((object)array(
				'auth_uid' => "UPDATE",
				'auth_key' => "UPDATE",
				'auth_expiration' => "UPDATE",
				'target_version' => $target_version,
			));
			$setup_update_run_result = $setup_update_run->execute();

			if( ! $setup_update_run_result->success )
			{
				die(
					'Error running update to version '.
					$target_version.': '.
					$setup_update_run_result->error.
					$setup_update_run_result->auth_error.
					$setup_update_run_result->config_error."\n"
				);
			}

			$current_version = $setup_update_run_result->data->current_version;
			$target_version = $setup_update_run_result->data->target_version;

			echo 'Successfully updated to version '.$current_version.".\n";
		}

		die('BeansBooks has been updated to local source version '.$current_version.'.'."\n");

	}

}