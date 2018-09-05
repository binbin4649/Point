<?php 
class PointBooksSchema extends CakeSchema {

	public $file = 'point_books.php';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $point_books = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mypage_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'point_user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'point' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'credit' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'point_balance' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'credit_balance' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'pay_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'pay_plan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'charge' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'reason' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'reason_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'close_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'deadline_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'invoice_amount' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'invoice_detail' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'invoice_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'payment_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

}
