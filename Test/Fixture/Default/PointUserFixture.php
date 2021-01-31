<?php

class PointUserFixture extends CakeTestFixture {
	
	//public $useDbConfig = 'test';
	public $import = array('model' => 'Point.PointUser');
	
	public function init(){
		$this->records = [
			[
				'id' => 1,
				'mypage_id' => 1,
				'point' => 100,
				'credit' => 0,
				'available_point' => 100,
				'pay_plan' => 'basic',
				'exp_date' => '',
				'created' => '2018-07-30 16:26:01',
				'modified' => '2018-07-30 16:26:01',
			],
			[
				'id' => 2,
				'mypage_id' => 2,
				'point' => 100,
				'credit' => 100,
				'available_point' => 100,
				'pay_plan' => 'basic',
				'charge_point' => '05',
				'exp_date' => '2021-01-04',
				'auto_charge_status' => '',
				'created' => '2018-07-30 16:26:01',
				'modified' => '2018-07-30 16:26:01',
			],
			[
				'id' => 3,
				'mypage_id' => 3,
				'point' => 10000,
				'credit' => 0,
				'available_point' => 10000,
				'pay_plan' => 'auto',
				'exp_date' => '',
				'created' => '2018-07-30 16:26:01',
				'modified' => '2018-07-30 16:26:01',
			],
			[
				'id' => 4,
				'mypage_id' => 4,
				'point' => '-1000',
				'credit' => 0,
				'available_point' => 10000,
				'pay_plan' => 'pay_off',
				'exp_date' => '',
				'created' => '2018-07-30 16:26:01',
				'modified' => '2018-07-30 16:26:01',
			],
			[
				'id' => 5,
				'mypage_id' => 5,
				'point' => 0,
				'credit' => 0,
				'available_point' => 0,
				'payjp_card_token' => 'testtest',
				'auto_charge_status' => 'success',
				'pay_plan' => 'month',
				'exp_date' => date('Y-m-d'),
				'auto_charge_status' => 'success',
				'created' => '2018-07-30 16:26:01',
				'modified' => '2018-07-30 16:26:01',
			],
		];
		parent::init();
	}

}