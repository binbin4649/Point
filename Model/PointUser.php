<?php
App::import('Model', 'AppModel');

class PointUser extends AppModel {

	public $name = 'PointUser';
	
	public $belongsTo = [
		'Mypage' => [
			'className' => 'Members.Mypage',
			'foreignKey' => 'mypage_id']
	];
	
    public $hasMany = [
		'PointBook' => [
			'className' => 'Point.PointBook',
			'foreignKey' => 'point_user_id',
			'order' => 'PointBook.created DESC',
			'limit' => 50
	]];
	
	
	//無ければ作ってID返す。有ればそのIDを返す。
	public function getPointUserId($mypage_id){
		$PointUser = $this->findByMypageId($mypage_id, ['id'], null , -1);
		if($PointUser){
			return $PointUser['PointUser']['id'];
		}else{
			$PointUser = ['PointUser' => [
				'mypage_id' => $mypage_id,
				'point' => 0,
				'credit' => 0,
				'available_point' => 0,
			]];
			if($this->save($PointUser)){
				return $this->getLastInsertId();
			}else{
				$this->log('PointUser.php getPointUserId save error.');
			}
		}
	}
	
	//ポイント加算（ポイント購入）and 管理画面からポイント調整、イベントポイント, クーポンポイントなどを想定
	//最小 $data = ['mypage_id'=>0, 'point'=>0, 'reason'=>''];
	//最大 $data = ['mypage_id'=>0, 'point_user_id'=>0, 'point'=>0, 'reason'=>'', 'reason_id'=>'', 'pay_token'=>'', 'charge'=>0];
	public function pointAdd($data = []){
		if(empty($data['mypage_id'])) return false;
		if(empty($data['point_user_id'])) $data['point_user_id'] = $this->getPointUserId($data['mypage_id']);
		if(empty($data['point'])) return false;
		if(empty($data['reason'])) return false;
		if(empty($data['reason_id'])) $data['reason_id'] = '';
		if(empty($data['pay_token'])) $data['pay_token'] = '';
		if(empty($data['charge'])) $data['charge'] = '';
		
		//ポイント計算
		$PointUser = $this->findById($data['point_user_id'], null, null, -1);
		$new_point = $PointUser['PointUser']['point'] + $data['point'];
		$new_available_point = $PointUser['PointUser']['available_point'] + $data['point'];
		if($new_point < 0) return false; //ポイントはマイナスにならない。
		if($new_available_point < 0) return false;//使用可能ポイントはマイナスにならない
		
		$datasource = $this->getDataSource();
		try{
			$datasource->begin();
			$this->create();
			$saveField = ['point', 'available_point'];
			$save_point_user = ['PointUser' => [
				'id' => $data['point_user_id'],
				'point' => $new_point,
				'available_point' => $new_available_point,
			]];
			if(!$this->save($save_point_user, null, $saveField)){
				throw new Exception();
			}
			$this->PointBook->create();
			$PointBook = ['PointBook' => [
				'mypage_id' => $data['mypage_id'],
				'point_user_id' => $data['point_user_id'],
				'point' => $data['point'],
				'credit' => 0,
				'point_balance' => $new_point,
				'credit_balance' => $PointUser['PointUser']['credit'],
				'pay_token' => $data['pay_token'],
				'charge' => $data['charge'],
				'reason' => $data['reason'],
				'reason_id' => $data['reason_id']
			]];
			if($this->PointBook->save($PointBook)){
				$PointBook['PointBook']['created'] = date("Y-m-d H:i:s") ;
				$PointBook['PointBook']['id'] = $this->PointBook->getLastInsertId();
			}else{
				throw new Exception();
			}
			$datasource->commit();
		}catch(Exception $e){
			$datasource->rollback();
			$this->log('PointUser.php PointAdd save error. : '.print_r($e, true));
			return false;
		}
		return $PointBook;
	}
	
	//ポイント減算（サービス消費）
	// $data['point'] 負の正数指定
	//$data = ['mypage_id'=>0, 'point_user_id'=>0, 'point'=>0, 'reason'=>'', 'reason_id'=>''];
	public function pointExp($data = []){
		if(empty($data['mypage_id'])) return false;
		if(empty($data['point_user_id'])) $data['point_user_id'] = $this->getPointUserId($data['mypage_id']);
		if(empty($data['point']) && $data['point'] == 0) return false; //0の数字は指定できない。
		if(empty($data['reason'])) return false;
		if(empty($data['reason_id'])) $data['reason_id'] = '';
		
		//ポイント計算
		$PointUser = $this->findById($data['point_user_id'], null, null, -1);
		$new_point = $PointUser['PointUser']['point'] + $data['point'];
		$new_credit = $PointUser['PointUser']['credit'] + $data['point'];
		if($new_point < 0) return false; //ポイント、クレジットはマイナスにならない。
		if($new_credit < 0) return false;
		
		$datasource = $this->getDataSource();
		try{
			$datasource->begin();
			$this->create();
			$saveField = ['point', 'credit'];
			$save_point_user = ['PointUser' => [
				'id' => $data['point_user_id'],
				'point' => $new_point,
				'credit' => $new_credit,
			]];
			if(!$this->save($save_point_user, null, $saveField)){
				throw new Exception();
			}
			$this->PointBook->create();
			$PointBook = ['PointBook' => [
				'mypage_id' => $data['mypage_id'],
				'point_user_id' => $data['point_user_id'],
				'point' => $data['point'],
				'credit' => $data['point'],
				'point_balance' => $new_point,
				'credit_balance' => $new_credit,
				'reason' => $data['reason'],
				'reason_id' => $data['reason_id']
			]];
			if($this->PointBook->save($PointBook)){
				$PointBook['PointBook']['id'] = $this->PointBook->getLastInsertId();
			}else{
				throw new Exception();
			}
			$datasource->commit();
		}catch(Exception $e){
			$datasource->rollback();
			$this->log('PointUser.php PointExp save error. : '.$e);
			return false;
		}
		return $PointBook;
	}
	
	//クレジット加算（サービス予約） $data['point'] 正の正数指定
	//クレジット減算（サービス予約取消） $data['point'] 負の正数指定
 	//$data = ['mypage_id'=>0, 'point_user_id'=>0, 'point'=>0, 'reason'=>'', 'reason_id'=>''];
	public function creditAdd($data = []){
		if(empty($data['mypage_id'])) return false;
		if(empty($data['point_user_id'])) $data['point_user_id'] = $this->getPointUserId($data['mypage_id']);
		if(empty($data['point'])) return false;
		if(empty($data['reason'])) return false;
		if(empty($data['reason_id'])) $data['reason_id'] = '';
		
		//ポイント計算
		$PointUser = $this->findById($data['point_user_id'], null, null, -1);
		$new_credit = $PointUser['PointUser']['credit'] + $data['point'];
		$new_available_point = $PointUser['PointUser']['available_point'] - $data['point'];
		if($new_credit > $PointUser['PointUser']['point']) return false; //クレジットはポイントを超えてはならない。
		if($new_available_point < 0) return false; //使用可能ポイントはマイナスにならない。
		if($new_credit < 0) return false; //クレジットはマイナスにならない。
		
		$datasource = $this->getDataSource();
		try{
			$datasource->begin();
			$this->create();
			$saveField = ['credit', 'available_point'];
			$save_point_user = ['PointUser' => [
				'id' => $data['point_user_id'],
				'credit' => $new_credit,
				'available_point' => $new_available_point,
			]];
			if(!$this->save($save_point_user, null, $saveField)){
				throw new Exception();
			}
			$this->PointBook->create();
			$PointBook = ['PointBook' => [
				'mypage_id' => $data['mypage_id'],
				'point_user_id' => $data['point_user_id'],
				'point' => 0,
				'credit' => $data['point'],
				'point_balance' => $PointUser['PointUser']['point'],
				'credit_balance' => $new_credit,
				'reason' => $data['reason'],
				'reason_id' => $data['reason_id']
			]];
			if($this->PointBook->save($PointBook)){
				$PointBook['PointBook']['id'] = $this->PointBook->getLastInsertId();
			}else{
				throw new Exception();
			}
			$datasource->commit();
		}catch(Exception $e){
			$datasource->rollback();
			$this->log('PointUser.php creditAdd save error. : '.$e);
			return false;
		}
		return $PointBook;
	}
	

}
