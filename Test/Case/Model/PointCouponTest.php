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
	
	public function testCountUpUseTime(){
		$code = 'test5';
		$r = $this->PointCoupon->countUpUseTime($code);
		$this->assertEquals(1, $r['PointCoupon']['use_time']);
	}
	
	public function testCouponGeneratorUnlimited(){
		$data['PointCoupon'] = [
			'name' => 'アンリミテッドテスト',
		    'division' => 'unlimited_test',
		    'start' => '',
		    'finish' => date("Y-m-d", strtotime("+30 day")),
		    'point' => '1',
		    'times' => '',
		    'target' => '',
		    'target_id' => '',
		    'use_plan' => 'unlimited',
		    'generated' => '',
		];
		$this->PointCoupon->couponGenerator($data);
		$r = $this->PointCoupon->findByDivision('unlimited_test');
		$this->assertEquals('unlimited_test', $r['PointCoupon']['code']);
	}
	
	public function testCouponGeneratorLimited(){
		$data['PointCoupon'] = [
			'name' => 'リミテッドテスト',
		    'division' => 'limited_test',
		    'start' => '',
		    'finish' => date("Y-m-d", strtotime("+30 day")),
		    'point' => '1',
		    'times' => '100',
		    'target' => '',
		    'target_id' => '',
		    'use_plan' => 'limited',
		    'generated' => '',
		];
		$this->PointCoupon->couponGenerator($data);
		$r = $this->PointCoupon->findByDivision('limited_test');
		$this->assertEquals('limited_test', $r['PointCoupon']['code']);
	}
	
	public function testCouponGeneratorOnce(){
		$data['PointCoupon'] = [
			'name' => 'ワンステスト',
		    'division' => 'once_test',
		    'start' => '',
		    'finish' => date("Y-m-d", strtotime("+30 day")),
		    'point' => '10',
		    'times' => '',
		    'target' => '',
		    'target_id' => '',
		    'use_plan' => 'once',
		    'generated' => '10',
		];
		$this->PointCoupon->couponGenerator($data);
		$r = $this->PointCoupon->findAllByDivision('once_test');
		$this->assertEquals(10, count($r));
	}
	
	public function testValidateFalse(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => '',
			    'division' => '',
			    'start' => '',
			    'finish' => '',
			    'point' => '',
			    'times' => '',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => '',
			    'generated' => '',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('必須', current($this->PointCoupon->validationErrors['name']));
	    $this->assertEquals('必須', current($this->PointCoupon->validationErrors['division']));
	    $this->assertEquals('必須', current($this->PointCoupon->validationErrors['finish']));
	    $this->assertEquals('必須', current($this->PointCoupon->validationErrors['point']));
	    $this->assertEquals('必須', current($this->PointCoupon->validationErrors['use_plan']));
    }
    
    public function testValidateCivisionUnique(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test4',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('使用済みです', current($this->PointCoupon->validationErrors['division']));
    }
    
    public function testValidateExccedFinish(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("-1 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('開始日が終了日を超えてはならない', current($this->PointCoupon->validationErrors['finish']));
    }
    
    public function testValidateTargetExt(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => 'Hoge',
			    'target_id' => '1',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('対象idが存在しません。', current($this->PointCoupon->validationErrors['target_id']));
	    $this->assertEquals('ターゲットとなるプラグイン、モデルがありません。', current($this->PointCoupon->validationErrors['target']));
    }
    
    public function testValidateOnceNotBlank(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'once',
			    'generated' => '',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('onceの場合は必須', current($this->PointCoupon->validationErrors['generated']));
    }
    
    public function testValidateOnceNotBlank2(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'once',
			    'generated' => 'a',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('数字で入力', current($this->PointCoupon->validationErrors['generated']));
    }
    
    public function testValidateLimitedNotBlank(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('limitedの場合は必須', current($this->PointCoupon->validationErrors['times']));
    }
    
    public function testValidateLimitedNotBlank2(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => 'a',
			    'target' => '',
			    'target_id' => '',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('数字で入力', current($this->PointCoupon->validationErrors['times']));
    }
    
    public function testValidateTargetNotBlank(){
	    $this->PointCoupon->create([
		    'PointCoupon' => [
			    'name' => 'test',
			    'division' => 'test',
			    'finish' => date("Y-m-d", strtotime("+30 day")),
			    'point' => '1',
			    'times' => '1',
			    'target' => 'Members.Mypage',
			    'target_id' => '',
			    'use_plan' => 'limited',
			    'generated' => '1',
		    ]
	    ]);
	    $this->assertFalse($this->PointCoupon->validates());
	    $this->assertEquals('targetが指定される場合は必須', current($this->PointCoupon->validationErrors['target_id']));
    }
	
	public function testCouponStatusBefore(){
		$code = 'test5';
		$r = $this->PointCoupon->couponStatus($code);
		$this->assertEquals('before', $r);
	}
	
	public function testCouponStatusOverTime(){
		$code = 'test6';
		$r = $this->PointCoupon->couponStatus($code);
		$this->assertEquals('overtime', $r);
	}
	
	public function testCouponChrageクーポンコードがない(){
		$data['PointCoupon'] = ['code'=>'hoge'];
		$r = $this->PointCoupon->couponChrage($data);
	    $this->assertFalse($r);
	}
	
	public function testCouponChrage期限切れ(){
		$data['PointCoupon'] = ['code'=>'testtest3'];
		$data['Mypage'] = ['id'=>'999'];
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