<?php

class PointCouponFixture extends CakeTestFixture {
	
	public $import = array('model' => 'Point.PointCoupon');
	
	public $records = array(
		array(
			'id' => 1,
			'name' => 'イベント名1',
			'division' => 'testtest',
			'start' => '2018-08-01',
			'finish' => '2038-01-19',
			'point' => 300,
			'code' => 'testtest',
			'use_plan' => 'unlimited',
			'use_time' => 1,
			'generated' => 1,
			'created' => '2018-08-01 18:26:01'
		),
		array(
			'id' => 2,
			'name' => 'イベント名２',
			'division' => 'test2',
			'start' => '2018-08-01',
			'finish' => '2018-08-31',
			'point' => 500,
			'code' => 'hhU78G1k',
			'use_plan' => 'once',
			'use_time' => 0,
			'generated' => 50,
			'created' => '2018-08-01 18:26:01'
		),
		array(
			'id' => 3,
			'name' => 'イベント名3',
			'division' => 'testtest3',
			'start' => '2018-07-01',
			'finish' => '2018-07-30',
			'point' => 300,
			'code' => 'testtest3',
			'use_plan' => 'unlimited',
			'use_time' => 0,
			'generated' => 1,
			'created' => '2018-07-01 18:26:01'
		),
		array(
			'id' => 4,
			'name' => 'イベント名4',
			'division' => 'test4',
			'start' => '2018-08-01',
			'finish' => '2018-08-31',
			'point' => 500,
			'code' => 'u6SgzdZL',
			'use_plan' => 'once',
			'use_time' => 1,
			'generated' => 50,
			'created' => '2018-08-01 18:26:01'
		),
		
	);

}