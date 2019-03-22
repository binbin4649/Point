<?php
App::uses('PointBook', 'Point.Model');

class PointBookTest extends BaserTestCase {
    public $fixtures = array(
        'plugin.point.Default/PointUser',
        'plugin.point.Default/PointBook',
        'plugin.point.Default/PointCoupon',
        'plugin.point.Default/Mypage'
    );

    public function setUp() {
        $this->PointBook = ClassRegistry::init('Point.PointBook');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PointBook);
	    parent::tearDown();
    }
	
	public function testMonthlyReasonIdBook(){
		$ym = date('Ym');
		$mypage_ids[] = '40';
		$r = $this->PointBook->monthlyUserBook($ym, $mypage_ids);
		$this->assertEquals('100', $r[0]['PointBook']['point']);
	}
	

}