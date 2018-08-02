<?php

class PointUserFixture extends CakeTestFixture {
	
	public $useDbConfig = 'test';
	public $import = array('model' => 'Point.PointUser');
	
/*
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mypage_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'point' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'credit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'available_point' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'payjp_card_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'payjp_customer_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'payjp_brand' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'payjp_last4' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'auto_charge_status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'pay_plan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'charge_point' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
*/
	
	public $records = array(
		array(
			'id' => 1,
			'mypage_id' => 1,
			'point' => 100,
			'credit' => 0,
			'available_point' => 100,
			'pay_plan' => 'basic',
			'created' => '2018-07-30 16:26:01',
			'modified' => '2018-07-30 16:26:01',
		),
		array(
			'id' => 2,
			'mypage_id' => 2,
			'point' => 100,
			'credit' => 100,
			'available_point' => 100,
			'pay_plan' => 'basic',
			'created' => '2018-07-30 16:26:01',
			'modified' => '2018-07-30 16:26:01',
		),
		array(
			'id' => 3,
			'mypage_id' => 3,
			'point' => 100,
			'credit' => 0,
			'available_point' => 100,
			'pay_plan' => 'auto',
			'created' => '2018-07-30 16:26:01',
			'modified' => '2018-07-30 16:26:01',
		),
	);

}