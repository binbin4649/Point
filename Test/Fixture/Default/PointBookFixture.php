<?php

class PointBookFixture extends CakeTestFixture {
	
	public $import = array('model' => 'Point.PointBook');

/*
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mypage_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'point_user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'point' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'credit' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'point_balance' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'credit_balance' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'pay_token' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'pay_plan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'charge' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'reason' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'reason_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);
*/
	
	public $records = array(
		array(
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
		),
	);

}