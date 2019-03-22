<?php

class PointBookFixture extends CakeTestFixture {
	
	public $import = array('model' => 'Point.PointBook');
	
	public $records = array(
		array(

		),
	);
	
	public function init(){
		$this->records = [
			[
				'id' => 1,
				'mypage_id' => '999',
				'point_user_id' => '999',
				'point' => 500,
				'credit' => 0,
				'point_balance' => 500,
				'credit_balance' => 0,
				'reason' => 'coupon',
				'reason_id' => 'testtest',
				'created' => '2018-08-01 18:26:01',
				'modified' => '2018-08-01 18:26:01'
			],
			[
				'id' => 40,
				'mypage_id' => '40',
				'point_user_id' => '40',
				'point' => 100,
				'credit' => 0,
				'point_balance' => 500,
				'credit_balance' => 0,
				'reason' => 'run',
				'reason_id' => 'testtest',
				'created' => date('Y-m-d H:i:s'),
				'modified' => date('Y-m-d H:i:s')
			],
		];
		parent::init();
	}

}