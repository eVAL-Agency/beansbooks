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

$(function() {
	var $loginButton = $('.auth-login-submit'),
		$loginForm = $loginButton.closest('form');

	// On submit of this form, throw up a loading splash div to preoccupy the user, but continue submitting otherwise.
	$loginForm.submit(function() {
		showPleaseWait();
	});
	
	// This button is so that it looks pretty, but is otherwise useless and simply calls submit on the parent form.
	$loginButton.click(function(){
		$loginForm.submit();
		return false;
	});
});