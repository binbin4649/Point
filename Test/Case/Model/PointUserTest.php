<?php
App::uses('PointUser', 'Point.Model');

class PointUserTest extends BaserTestCase {
	
	
    public $fixtures;
    
    public function __construct(){
	    $fixtures = array(
	        'plugin.point.Default/PointUser',
	        'plugin.point.Default/PointBook',
	        'plugin.point.Default/Mypage',
	        'plugin.point.Default/Mylog',
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
	    Configure::write('MccPlugin.TEST_MODE', true);
        $this->PointUser = ClassRegistry::init('Point.PointUser');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PointUser);
	    parent::tearDown();
    }
	
	public function testPayPlanEdit(){
		$data['PointUser']['mypage_id'] = 2;
		$data['PointUser']['pay_plan'] = 'pay_off';
		$data['PointUser']['invoice_plan'] = 'pm_month';
		$data['PointUser']['exp_date'] = '';
		$r = $this->PointUser->payPlanEdit($data);
		$this->assertEquals('pay_off', $r['PointUser']['pay_plan']);
	}
	
	public function testPayjpCharge(){
		$data = [
			'payjp_token' => 'test',
			'amount' => '',
			'mypage_id' => '1',
		];
		$r = $this->PointUser->payjpCharge($data);
		$this->assertFalse($r);
	}
	
	public function testNextMonthExpdate(){
		$exp_date = '2021-01-31';
		$base_day = '31';
		$strtime = '2021-01-31';
		$r = $this->PointUser->nextMonthExpdate($exp_date, $base_day, $strtime);
		$this->assertEquals('2021-02-28', $r);
	}
	
	public function testNextMonthExpdate2(){
		$exp_date = '';
		$base_day = '';
		$r = $this->PointUser->nextMonthExpdate($exp_date, $base_day);
		$this->assertTrue(!empty($r));
	}
	
	public function testExtensionDays(){
		$mypage_id = '1';
		$days = '3';
		$r = $this->PointUser->extensionDays($mypage_id, $days);
		$this->assertTrue($r);
	}
	
	public function testMonthlyRangeTrue(){
		$mypage_id = '2';
		$target_date = '20210104';
		$r = $this->PointUser->monthlyRange($mypage_id, $target_date);
		$this->assertTrue($r);
	}
	
	public function testMonthlyRangeFalse(){
		$mypage_id = '2';
		$target_date = '20210105';
		$r = $this->PointUser->monthlyRange($mypage_id, $target_date);
		$this->assertFalse($r);
	}
	
	public function testCustomerChargeCheck(){
		$pointUser = $this->PointUser->findByMypageId('5');
		$r = $this->PointUser->customerChargeCheck($pointUser);
		$this->assertFalse($r);
	}
	
	public function testRunSubscription(){
		$r = $this->PointUser->runSubscription();
		$this->assertTrue($r);
	}
	
	public function testCustomerChargeAfter(){
		$pointUser = $this->PointUser->findByMypageId('2');
		$pay_token = 'testtesttest';
		$amount = 2200;
		$r = $this->PointUser->customerChargeAfter($pointUser, $pay_token, $amount);
		$this->assertTrue($r);
	}
	
	public function testGetNextMonth(){
		$date = '2020-12-05';
		$r = $this->PointUser->getCurrectedNextMonth($date);
		$this->assertEquals('2021-01-05', $r);
	}
	
	public function testGetNextMonth2(){
		$date = '2021-01-30';
		$r = $this->PointUser->getCurrectedNextMonth($date);
		$this->assertEquals('2021-02-28', $r);
	}
	
	public function testIsInExpdateFalse(){
		$mypage_id = '2';
		$r = $this->PointUser->isInExpdate($mypage_id);
		$this->assertFalse($r);
	}
	
	public function testIsInExpdate(){
		$mypage_id = '1';
		$r = $this->PointUser->isInExpdate($mypage_id);
		$this->assertTrue($r);
	}
	
    public function testGetPointUserId(){
        $this->assertEquals('1', $this->PointUser->getPointUserId(1));
    }
    
    public function testPointAddPointMinus(){
	    $data = ['mypage_id'=>4, 'point'=>'-200', 'reason'=>'test'];
	    $r = $this->PointUser->pointAdd($data);
	    $this->assertEquals('-1200', $r['PointBook']['point_balance']);
    }
    
    public function testFalsePointAdd(){
	    // mypage_idが無い
	    $data = ['mypage_id'=>'', 'point'=>'100', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointAdd($data));
	    
	    // pointが無い
	    $data = ['mypage_id'=>1, 'point'=>'', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointAdd($data));
	    
	    // reasonが無い
	    $data = ['mypage_id'=>1, 'point'=>'100', 'reason'=>''];
	    $this->assertFalse($this->PointUser->pointAdd($data));
    }
    
    public function testTruePointAdd(){
	    $data = ['mypage_id'=>1, 'point'=>'100', 'reason'=>'test'];
	    $r = $this->PointUser->pointAdd($data);
	    $this->assertEquals(200, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testFalsePointExp(){
	    // mypage_idが無い
	    $data = ['mypage_id'=>'', 'point'=>'-50', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointExp($data));
	    
	    // pointが無い
	    $data = ['mypage_id'=>1, 'point'=>'', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointExp($data));
	    
	    // reasonが無い
	    $data = ['mypage_id'=>1, 'point'=>'-50', 'reason'=>''];
	    $this->assertFalse($this->PointUser->pointExp($data));
    }
    
/*
    public function testPointExpクレジットはマイナスにならない(){
	    $data = ['mypage_id'=>1, 'point'=>'-50', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointExp($data));
    }
*/
    
    public function testPointExpポイントはマイナスにならない(){
	    $data = ['mypage_id'=>1, 'point'=>'-200', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointExp($data));
    }
    
    public function testPointExpAutoで減算(){
	    $data = ['mypage_id'=>3, 'point'=>'-50', 'reason'=>'test'];
	    $r = $this->PointUser->pointExp($data);
	    $this->assertEquals(0, $r['PointBook']['credit']);
	    $this->assertEquals(9950, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testPointExpBasicで減算(){
	    $data = ['mypage_id'=>2, 'point'=>'-50', 'reason'=>'test'];
	    $r = $this->PointUser->pointExp($data);
	    $this->assertEquals(50, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testPointExpAutoで減算credit追加(){
	    $data = ['mypage_id'=>3, 'point'=>'-45', 'reason'=>'test', 'credit'=>'-50'];
	    $r = $this->PointUser->pointExp($data);
	    $this->assertEquals(0, $r['PointBook']['credit']);
	    $this->assertEquals(9955, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testPointExpBasicで減算credit追加(){
	    $data = ['mypage_id'=>2, 'point'=>'-45', 'reason'=>'test', 'credit'=>'-50'];
	    $r = $this->PointUser->pointExp($data);
	    $this->assertEquals(55, $r['PointBook']['point_balance']);
	    $this->assertEquals(50, $r['PointBook']['credit_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testFalseCreditAdd(){
	    // mypage_idが無い
	    $data = ['mypage_id'=>'', 'point'=>'50', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->creditAdd($data));
	    
	    // pointが無い
	    $data = ['mypage_id'=>1, 'point'=>'', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->creditAdd($data));
	    
	    // reasonが無い
	    $data = ['mypage_id'=>1, 'point'=>'50', 'reason'=>''];
	    $this->assertFalse($this->PointUser->creditAdd($data));
    }
    
    public function testCreditAddクレジットはポイントを超えてはならない(){
	    $data = ['mypage_id'=>2, 'point'=>'50', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->creditAdd($data));
    }
    
    public function testTrueCreditAdd(){
	    $data = ['mypage_id'=>1, 'point'=>'50', 'reason'=>'test'];
	    $r = $this->PointUser->creditAdd($data);
	    $this->assertEquals(50, $r['PointBook']['credit_balance']);
	    $this->assertEquals(100, $r['PointBook']['point_balance']);
    }
    
    public function testPointCheckTrue(){
	    $r = $this->PointUser->pointCheck(1, '50');
	    $this->assertTrue($r);
    }
    
    public function testPointCheckFalse(){
	    $r = $this->PointUser->pointCheck(1, '500');
	    $this->assertFalse($r);
    }

}