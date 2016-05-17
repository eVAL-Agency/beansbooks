<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 5/17/16
 * Time: 8:17 AM
 */
class Beans_Setup_Update_V_1_5_2_eVAL1 extends Beans_Setup_Update_V {

	public function __construct($data = NULL) {
		parent::__construct($data);
	}

	protected function _execute() {
		
		// Change the length of all code/ref/alt/aux column from 16 to 32.
		DB::query(
			Database::UPDATE,
			'ALTER TABLE `forms` CHANGE `code` `code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `forms` CHANGE `reference` `reference` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `forms` CHANGE `alt_reference` `alt_reference` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `forms` CHANGE `aux_reference` `aux_reference` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `roles` CHANGE `code` `code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `taxes` CHANGE `code` `code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();

		DB::query(
			Database::UPDATE,
			'ALTER TABLE `transactions` CHANGE `reference` `reference` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'
		)->execute();


		// WTF is this supposed to be?
		// 1) It tells the calling script JACK SHIT and 
		// 2) IS IT A FUCKING OBJECT OR AN ARRAY?!?!
		return (object)array();
	}
}