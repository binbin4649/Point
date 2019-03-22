<?php
App::uses('PointCoupon', 'Point.Model');

class PointCouponTest extends BaserTestCase {
    public $fixtures = array(
        'plugin.point.Default/PointUser',
        'plugin.point.Default/PointBook',
        'plugin.point.Default/PointCoupon',
        //'plugin.members.Default/Mypage'
        'plugin.point.Default/Mypage'
    );

    public function setUp() {
        $this->PointCoupon = ClassRegistry::init('Point.PointCoupon');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PointCoupon);
	    parent::tearDown();
    }
	
	public function testCouponChrageクーポンコードがない(){
		$data['PointCoupon'] = ['code'=>'hoge'];
		$r = $this->PointCoupon->couponChrage($data);
	    $this->assertFalse($r);
	}
	
	public function testCouponChrage期限切れ(){
		$data['PointCoupon'] = ['code'=>'testtest3'];
		$r = $this->PointCoupon->couponChrage($data);
	    $this->assertFalse($r);
	}
	
	public function testCouponChrageOnce使用済み(){
		$data['PointCoupon'] = ['code'=>'u6SgzdZL'];
		$r = $this->PointCoupon->couponChrage($data);
	    $this->assertFalse($r);
	}
	
	public function testCouponChrageUnlimited使用済み(){
		$data['PointCoupon'] = ['code'=>'testtest'];
		$data['Mypage'] = ['id'=>'999'];
		$r = $this->PointCoupon->couponChrage($data);
	    $this->assertFalse($r);
	}
	
	public function testCouponChrageOnce成功(){
		$data['PointCoupon'] = ['code'=>'hhU78G1k'];
		$data['Mypage'] = ['id'=>'1'];
		$r = $this->PointCoupon->couponChrage($data);
		$this->assertEquals(600, $r['PointBook']['point_balance']);
	}
	
	public function testCouponChrageUnlimited成功(){
		$data['PointCoupon'] = ['code'=>'testtest'];
		$data['Mypage'] = ['id'=>'1'];
		$r = $this->PointCoupon->couponChrage($data);
		$e = $this->PointCoupon->findByCode($data['PointCoupon']['code']);
		$this->assertEquals('2', $e['PointCoupon']['use_time']);
	}

}