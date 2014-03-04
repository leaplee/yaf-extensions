<?php
class UserModel extends Model{
	protected $tableName = 'user';

	function getUserInfo($map) {
		$result = $this->where($map)->find();
		return $result;
	}
}