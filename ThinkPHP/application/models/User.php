<?php
/*
 * CREATE TABLE `user` (
 *   `id`  int(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
 *   `name`  varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
 *   PRIMARY KEY (`id`)
 *   ) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci ;
 * 
 */
class UserModel extends Model{
	protected $tableName = 'user';

	function getUserInfo($map) {
		$result = $this->where($map)->find();
		return $result;
	}
}