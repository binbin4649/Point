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
				'id' => 2,
				'mypage_id' => '18',
				'point_user_id' => '18',
				'point' => 374,
				'credit' => 0,
				'point_balance' => 0,
				'credit_balance' => 0,
				'reason' => 'original',
				'reason_id' => '7',
				'created' => '2018-08-01 18:26:01',
				'modified' => '2018-08-01 18:26:01'
			],
			[
				'id' => 40,
				'mypage_id' => '40',
				'point_user_id' => '40',
				'point' => '-100',
				'credit' => 0,
				'point_balance' => 500,
				'credit_balance' => 0,
				'reason' => 'run',
				'reason_id' => '1',
				'created' => date('Y-m-d H:i:s'),
				'modified' => date('Y-m-d H:i:s')
			],
			[
				'id' => 41,
				'mypage_id' => '40',
				'point_user_id' => '40',
				'point' => '-50',
				'credit' => 0,
				'point_balance' => 450,
				'credit_balance' => 0,
				'reason' => 'run',
				'reason_id' => '2',
				'created' => date('Y-m-d H:i:s'),
				'modified' => date('Y-m-d H:i:s')
			],
			[
				'id' => 42,
				'mypage_id' => '40',
				'point_user_id' => '40',
				'point' => '-20',
				'credit' => 0,
				'point_balance' => 430,
				'credit_balance' => 0,
				'reason' => 'receive',
				'reason_id' => '3',
				'created' => date('Y-m-d H:i:s'),
				'modified' => date('Y-m-d H:i:s')
			],
			[
				'id' => 43,
				'mypage_id' => '40',
				'point_user_id' => '40',
				'point' => '-20',
				'credit' => 0,
				'point_balance' => 410,
				'credit_balance' => 0,
				'reason' => 'receive',
				'reason_id' => '4',
				'created' => date('Y-m-d H:i:s'),
				'modified' => date('Y-m-d H:i:s')
			],
		];
		parent::init();
	}

}