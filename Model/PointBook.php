<?php
App::import('Model', 'AppModel');

class PointBook extends AppModel {

	public $name = 'PointBook';
	
	public $belongsTo = [
		'Mypage' => [
			'className' => 'Members.Mypage',
			'foreignKey' => 'mypage_id'],
		'PointUser' => [
			'className' => 'Point.PointUser',
			'foreignKey' => 'point_user_id']
	];
	
    

}
