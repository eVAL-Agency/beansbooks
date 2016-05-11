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

class Controller extends Kohana_Controller {

	protected $_required_role_permissions = array();

	function before() {
		parent::before();
		
		try{
			$session = Session::instance();
		}
		catch(Exception $e){
			// Session is corrupt, destroy it and kick the user back to '/'!
			foreach($_COOKIE as $k => $v){
				setcookie($k, '', 1, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
			}
			if($this->request->uri() == '/'){
				// Already on home page, simply throw a notice/warning
				if(Kohana::$environment == Kohana::PRODUCTION){
					throw new HTTP_Exception_500('We are sorry, but storage for cookies could not be established.  Please contact the systems administrator for this site.');
				}
				else{
					// re-throw this Exception.
					throw $e;
				}
			}
			else{
				$this->redirect('/');	
			}
		}

		// Auth redirects.
		if( 
			$this->request->controller() != "Api" AND 
			$this->request->controller() != "Auth" AND
			$this->request->controller() != "Update" AND
			$this->request->controller() != "Install" AND
			$this->request->controller() != "Exception" AND
			(
				! strlen(Session::instance()->get('auth_uid')) OR
				! strlen(Session::instance()->get('auth_expiration')) OR
				! strlen(Session::instance()->get('auth_key')) OR 
				! Session::instance()->get('auth_role')
			)
		){
			$this->redirect('/');
		}
		// If logged in we redirect to /dash rather than auth
		else if(
			$this->request->controller() == "Auth" AND
			$this->request->action() != "logout" AND
			(
				strlen(Session::instance()->get('auth_uid')) AND
				strlen(Session::instance()->get('auth_expiration')) AND
				strlen(Session::instance()->get('auth_key')) AND 
				Session::instance()->get('auth_role')
			)
		){
			$this->redirect('/dash');
		}
			

		// Avoid a nested exception thrown.
		if( $this->request->controller() != "Api" AND 
			$this->request->controller() != "Auth" AND
			$this->request->controller() != "Update" AND 
			$this->request->controller() != "Install" AND 
			$this->request->controller() != "Exception" AND 
			count($this->_required_role_permissions) )
		{
			$auth_role = Session::instance()->get('auth_role');

			if( ! isset($this->_required_role_permissions['default']) )
				throw new HTTP_Exception_401("Developer Error! No default permission set!");

			if( isset($this->_required_role_permissions[$this->request->action()]) AND
				(
					! isset($auth_role->{$this->_required_role_permissions[$this->request->action()]}) OR 
					! $auth_role->{$this->_required_role_permissions[$this->request->action()]} 
				) )
				throw new HTTP_Exception_401("Your account does not have access to this feature.");

			if( ! isset($this->_required_role_permissions[$this->request->action()]) AND
				(
					! isset($auth_role->{$this->_required_role_permissions['default']}) OR 
					! $auth_role->{$this->_required_role_permissions['default']} 
				) )
				throw new HTTP_Exception_401("Your account does not have access to this feature.");
		}
	}

	// Beans Authentication
	protected function _beans_data_auth($data = NULL) {
		if( $data === NULL )
			$data = new stdClass;

		if( is_array($data) )
			$data = (object)$data;

		if( ! is_object($data) OR
			get_class($data) != "stdClass" )
			$data = new stdClass;

		// Set our auth keys.
		$data->auth_uid = Session::instance()->get('auth_uid');
		$data->auth_expiration = Session::instance()->get('auth_expiration');
		$data->auth_key = Session::instance()->get('auth_key');

		return $data;
	}

	protected function _get_numeric_value($value) {
		return preg_replace('/[^0-9.]*/','', $value);
	}

}