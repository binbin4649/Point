<?php
App::uses('PointUser', 'Point.Model');

class PointUserTest extends BaserTestCase {
    public $fixtures = array(
        'plugin.point.Default/PointUser',
        'plugin.point.Default/PointBook',
    );

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
    
    public function testPointAddポイントはマイナスにならない(){
	    $data = ['mypage_id'=>1, 'point'=>'-200', 'reason'=>'test'];
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
	    $this->assertEquals(50, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
    
    public function testPointExpBasicで減算(){
	    $data = ['mypage_id'=>2, 'point'=>'-50', 'reason'=>'test'];
	    $r = $this->PointUser->pointExp($data);
	    $this->assertEquals(50, $r['PointBook']['point_balance']);
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

}