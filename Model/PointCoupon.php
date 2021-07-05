<?php
App::import('Model', 'AppModel');

class PointCoupon extends AppModel {

	public $name = 'PointCoupon';
	
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->PointBook = ClassRegistry::init('Point.PointBook');
		$this->PointUser = ClassRegistry::init('Point.PointUser');
	}
	
	public $validate = array(
		'name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => '必須'
            )
        ),
        'use_plan' => array(
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
	        ),
	        'exccedFinish' => array(
                'rule' => 'exccedFinish',
                'message' => '開始日が終了日を超えてはならない'
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
        'times' => array(
	        'limitedNotBlank' => array(
                'rule' => 'limitedNotBlank',
                'message' => 'limitedの場合は必須'
            ),
            'naturalNumber' => array(
	            'allowEmpty' => true,
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
        'target_id' => array(
            'targetNotBlank' => array(
                'rule' => 'targetNotBlank',
                'message' => 'targetが指定される場合は必須'
            ),
            'naturalNumber' => array(
	            'allowEmpty' => true,
	            'rule' => 'naturalNumber',
	            'message' => '数字で入力'
            ),
            'targetIdExt' => array(
                'rule' => 'targetIdExt',
                'message' => '対象idが存在しません。'
            ),
        ),
        'target' => array(
            'targetExt' => array(
                'rule' => 'targetExt',
                'message' => 'ターゲットとなるプラグイン、モデルがありません。'
            )
        ),
	);
	
	public function exccedFinish($field){
		if(empty($this->data['PointCoupon']['start'])){
			$this->data['PointCoupon']['start'] = date("Y-m-d");
		}
		$start = strtotime($this->data['PointCoupon']['start']);
		$finish = strtotime($this->data['PointCoupon']['finish']);
		if($start > $finish){
			return false;
		}
		return true;
	}
	
	public function targetIdExt($field){
		if(!empty($this->data['PointCoupon']['target']) && !empty($this->data['PointCoupon']['target_id']) ){
			$target = ClassRegistry::init($this->data['PointCoupon']['target']);
			if(get_class($target) != 'AppModel'){//無いとAppModelを呼んでしまうため。importとか使ったほうが良いんだろうけど書式が面倒だったのでClassRegistryにした。
				$target_id = $target->findById($this->data['PointCoupon']['target_id']);
				if($target_id){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
	
	public function targetExt($field){
		if(!empty($this->data['PointCoupon']['target'])){
			$target = ClassRegistry::init($this->data['PointCoupon']['target']);
			if(get_class($target) != 'AppModel'){
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
	
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
	
	// limitedの場合は回数必須
	public function limitedNotBlank($field){
		if($this->data['PointCoupon']['use_plan'] == 'limited'){
			if(empty($field['times'])){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	// targetの場合はtarget_id必須
	public function targetNotBlank($field){
		if(!empty($this->data['PointCoupon']['target'])){
			if(empty($field['target_id'])){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
    public function couponGenerator($data){
	    if($data['PointCoupon']['use_plan'] == 'unlimited' || $data['PointCoupon']['use_plan'] == 'limited'){
		   //無制限の場合、ほぼそのまま保存
		   $this->create();
		   $data['PointCoupon']['code'] = $data['PointCoupon']['division'];
		   if(empty($data['PointCoupon']['generated'])){
			   $data['PointCoupon']['generated'] = 1;
		   }
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
	
	// ステータスを都度変更する。　現在のステータスを返す
	public function couponStatus($code){
		if(empty($code)) return false;
		$status = 'valid'; //発行されたクーポンは基本有効だろう
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $code,
        	),
		));
		//codeが無い
		if(!$PointCoupon) return false;
		
		//前処理
		$finish_time = strtotime($PointCoupon['PointCoupon']['finish']);
		$start_time = strtotime($PointCoupon['PointCoupon']['start']);
		$use_plan = $PointCoupon['PointCoupon']['use_plan'];
		$use_time = $PointCoupon['PointCoupon']['use_time'];
		if(empty($use_time)){
			$use_time = 0;
		}
		$times = $PointCoupon['PointCoupon']['times'];
		if(empty($times)){
			$times = 0;
		}
		
		//判定
		if($finish_time < time()){//期限切れ
			$status = 'overdue';
		}elseif($use_plan == 'once' && $use_time >= 1){//once使用済み
			$status = 'invalid';
		}elseif($start_time > time()){// before 開始前
			$status = 'before';
		}elseif($use_plan == 'limited' && $use_time >= $times){// overtime 回数（人数）制限
			$status = 'overtime';
		}
		
		//保存
		$this->create();
		$PointCoupon['PointCoupon']['status'] = $status;
		if(!$this->save($PointCoupon, false, ['status'])){
			$this->log('PointCoupon.php couponStatus save error. '. print_r($PointCoupon, true));
		}
		return $status;
	}
	
	// 個人単位で unlimited の使用可否。　true = 使用済み, false = 未使用
	public function isUnlimitedUsed($code, $mypage_id){
		if(empty($code)) return false;
		if(empty($mypage_id)) return false;
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $code,
	        	'PointCoupon.use_plan' => 'unlimited',
        	),
		));
		if($PointCoupon){
			$PointBook = $this->PointBook->find('first',array(
				'conditions' => array(
					'PointBook.mypage_id' => $mypage_id,
					'PointBook.reason' => 'coupon',
		        	'PointBook.reason_id' => $code
	        	),
			));
			if($PointBook){
				return true;
			}else{
				return false;
			}
		}
		return false; 
	}
	
	// user_plan:limitedの場合、use_time カウントアップ
	public function countUpUseTime($code){
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $code,
        	),
		));
		if(!$PointCoupon){
			return false;
		}
		$PointCoupon['PointCoupon']['use_time'] =  $PointCoupon['PointCoupon']['use_time'] + 1;
		$this->create();
		if(!$this->save($PointCoupon, false, ['use_time'])){
			$this->log('PointCoupon.php countUpUseTime save error. '. print_r($PointCoupon, true));
		}
		return $PointCoupon;
	}
	
	// bookを削除した際にuse_time を1下げる
	public function countDownUseTime($coupon_id){
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.id' => $coupon_id,
        	),
		));
		if(!$PointCoupon){
			return false;
		}
		if($PointCoupon['PointCoupon']['use_time'] == '0'){
			return false;
		}
		$PointCoupon['PointCoupon']['use_time'] =  $PointCoupon['PointCoupon']['use_time'] - 1;
		$this->create();
		if(!$this->save($PointCoupon, false, ['use_time'])){
			$this->log('PointCoupon.php countDownUseTime save error. '. print_r($PointCoupon, true));
		}
		return $PointCoupon;
		
	}
	
	public function couponChrage($data){
		$PointCoupon = $this->find('first', array(
        	'conditions' => array(
	        	'PointCoupon.code' => $data['PointCoupon']['code'],
        	),
		));
		if(empty($data['Mypage']['id'])){
			$mypage_id = null;
		}else{
			$mypage_id = $data['Mypage']['id'];
		}
		$status = $this->couponStatus($data['PointCoupon']['code']);
		if(!$status || $status != 'valid'){
			return false;
		}
		if($this->isUnlimitedUsed($data['PointCoupon']['code'], $mypage_id)){
			return false;
		}
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
