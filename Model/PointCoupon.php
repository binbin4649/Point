<?php
App::import('Model', 'AppModel');

class PointCoupon extends AppModel {

	public $name = 'PointCoupon';
	
	public $validate = array(
		'name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => '必須'
            )
        ),
        'division' => array(
	        'notBlank' => array(
                'rule' => 'notBlank',
                'message' => '必須'
            ),
	        'alphaNumeric' => array(
		        'rule' => 'alphaNumeric',
		        'message' => '文字と数字だけで入力'
	        ),
	        'isUnique' => array(
		        'rule' => 'isUnique',
		        'message' => '使用済みです'
	        )
        ),
        'start' => array(
	        'date' => array(
		        'allowEmpty' => true,
		        'rule' => array('date', 'ymd'),
		        'message' => 'YYYY/MM/DD で入力'
	        )
        ),
        'finish' => array(
	        'notBlank' => array(
                'rule' => 'notBlank',
                'message' => '必須'
            ),
	        'date' => array(
		        'rule' => array('date', 'ymd'),
		        'message' => 'YYYY/MM/DD で入力'
	        )
        ),
        'point' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => '必須'
            ),
            'naturalNumber' => array(
	            'rule' => 'naturalNumber',
	            'message' => '数字で入力'
            )
        ),
        'generated' => array(
            'onceNotBlank' => array(
                'rule' => 'onceNotBlank',
                'message' => 'onceの場合は必須'
            ),
            'naturalNumber' => array(
	            'allowEmpty' => true,
	            'rule' => 'naturalNumber',
	            'message' => '数字で入力'
            )
        ),
	);
	
	// onceの場合は枚数必須
	public function onceNotBlank($field){
		if($this->data['PointCoupon']['use_plan'] == 'once'){
			if(empty($field['generated'])){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
    public function couponGenerator($data){
	    if($data['PointCoupon']['use_plan'] == 'unlimited'){
		   //無制限の場合、ほぼそのまま保存
		   $this->create();
		   $data['PointCoupon']['code'] = $data['PointCoupon']['division'];
		   $data['PointCoupon']['generated'] = 1;
		   $data['PointCoupon']['use_time'] = 0;
		   if($this->save($data, false)){
			   return true;
		   }else{
			   $this->log('PointCoupon.php couponGenerator unlimited save error. '.print_r($data, true));
		   }
	    }elseif($data['PointCoupon']['use_plan'] == 'once'){
		    //1回限りの場合、枚数分codeを生成して保存
		    $generated = $data['PointCoupon']['generated'];
		    for($i=1; $i<=$generated; $i++){
			    $this->create();
			    $data['PointCoupon']['code'] = $this->generateCode();
			    $data['PointCoupon']['use_time'] = 0;
			    if(!$this->save($data, false)){
					$this->log('PointCoupon.php couponGenerator once once error. '.print_r($data, true));
				}
		    }
		    return true;
	    }
	    return false;
    }
    
    //ユニークコードを返す
    public function generateCode(){
	    $code = $this->generatePassword(8);
	    $code_count = $this->find('count', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $code,
        	),
		));
		if($code_count == 0){
			return $code;
		}else{
			$this->generateCode();
		}
    }
    
    protected function generatePassword($len = 8) {
		srand((double)microtime() * 1000000);
		$seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$password = "";
		while ($len--) {
			$pos = rand(0, 61);
			$password .= $seed[$pos];
		}
		return $password;
	}
	
	public function couponChrage($data){
		$this->PointBook = ClassRegistry::init('Point.PointBook');
		$this->PointUser = ClassRegistry::init('Point.PointUser');
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $data['PointCoupon']['code'],
        	),
		));
		//codeが無い
		if(!$PointCoupon) return false;
		
		//期限切れ
		$finish_time = strtotime($PointCoupon['PointCoupon']['finish']);
		if($finish_time < time()) return false;
		
		//once使用済み
		if($PointCoupon['PointCoupon']['use_plan'] == 'once' && $PointCoupon['PointCoupon']['use_time'] >= 1){
			return false;
		}
		
		//unlimited使用済み
		$book_count = $this->PointBook->find('count',array(
			'conditions' => array(
				'PointBook.mypage_id' => $data['Mypage']['id'],
				'PointBook.reason' => 'coupon',
	        	'PointBook.reason_id' => $data['PointCoupon']['code']
        	),
		));
		if($book_count >= 1) return false;
		
		//ポイントチャージ
		$book = [
			'mypage_id' => $data['Mypage']['id'],
			'point' => $PointCoupon['PointCoupon']['point'],
			'reason' => 'coupon',
			'reason_id' => $PointCoupon['PointCoupon']['code']
		];
		$PointBook = $this->PointUser->pointAdd($book);
		if($PointBook){
			$PointCoupon['PointCoupon']['use_time'] = $PointCoupon['PointCoupon']['use_time']+1;
			if(!$this->save($PointCoupon, false, ['use_time'])){
				$this->log('PointCoupon.php couponChrage save error. '. print_r($PointCoupon, true));
				return false;
			}
		}else{
			$this->log('PointCoupon.php couponChrage pointAdd error. '. print_r($book, true));
			return false;
		}
		return $PointBook;
	}
	
	

}
