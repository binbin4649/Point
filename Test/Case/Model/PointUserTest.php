<?php
App::uses('PointUser', 'Point.Model');

class PointUserTest extends BaserTestCase {
	
	
    public $fixtures;
    
    public function __construct(){
	    $fixtures = array(
	        'plugin.point.Default/PointUser',
	        'plugin.point.Default/PointBook',
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
        $this->PointUser = ClassRegistry::init('Point.PointUser');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PointUser);
	    parent::tearDown();
    }

    public function testGetPointUserId(){
        $this->assertEquals('1', $this->PointUser->getPointUserId(1));
    }
    
    public function testPointAddポイントマイナス(){
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
    
    public function testPointExpクレジットはマイナスにならない(){
	    $data = ['mypage_id'=>1, 'point'=>'-50', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointExp($data));
    }
    
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
    
    public function testPayPlanEdit(){
	    $data['PointUser']['mypage_id'] = 2;
	    $data['PointUser']['pay_plan'] = 'pay_off';
	    $data['PointUser']['invoice_plan'] = 'pm_month';
	    $r = $this->PointUser->payPlanEdit($data);
	    $this->assertEquals('pay_off', $r['PointUser']['pay_plan']);
    }
    

}