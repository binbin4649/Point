<?php
App::uses('PointBook', 'Point.Model');

class PointBookTest extends BaserTestCase {
    
    public $fixtures;
	
	public function __construct(){
	    $fixtures = array(
	        'plugin.point.Default/PointUser',
	        'plugin.point.Default/PointBook',
	        'plugin.point.Default/PointCoupon',
	        'plugin.point.Default/Mypage'
	    );
	    $Plugin = ClassRegistry::init('Plugin');
	    $lists = $Plugin->find('list');
	    foreach($lists as $list){
		    if($list == 'Nos'){
			    $fixtures[] = 'plugin.point.Default/NosCall';
			    $fixtures[] = 'plugin.point.Default/NosUser';
		    } 
	    }
	    $this->fixtures = $fixtures;
    }
	
    public function setUp() {
        $this->PointBook = ClassRegistry::init('Point.PointBook');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PointBook);
	    parent::tearDown();
    }
	
	public function testGetPointCouponId(){
		$code = 'test6';
		$mypage_id = '19';
		$r = $this->PointBook->getPointCouponId($code, $mypage_id);
		$this->assertEquals(44, $r);
	}
	
	public function testAddPointCoupon(){
		$code = 'test5';
		$mypage_id = '1';
		$point = '1';
		$r = $this->PointBook->addPointCoupon($code, $mypage_id, $point);
		$this->assertTrue(!empty($r));
	}
	
	public function testMonthlyReasonIdBook(){
		if(in_array('plugin.point.Default/NosCall', $this->fixtures, true)){
			$ym = '201808';
			$mypage_ids[] = '18';
			$plugin_name = 'Nos';
			$r = $this->PointBook->monthlyReasonIdBook($ym, $mypage_ids, $plugin_name);
			$this->assertEquals('374', $r[0]['PointBook']['point']);
		}
	}
	
	public function testMonthlyUserBook(){
		$ym = date('Ym');
		$mypage_ids[] = '40';
		$r = $this->PointBook->monthlyUserBook($ym, $mypage_ids);
		$this->assertEquals('-100', $r[0]['PointBook']['point']);
	}
	
	public function testMonthlyTotalByPlan(){
		$ym = null;
		$mypage_ids = ['40'];
		$r = $this->PointBook->monthlyTotalByPlan($ym, $mypage_ids);
		$this->assertEquals(2, $r['receive:20']);
	}
	
	

}