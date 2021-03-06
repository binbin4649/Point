<?php
require_once(dirname(__FILE__)."/../vendor/autoload.php");

App::import('Model', 'AppModel');
App::import('Model', 'Plugin');
App::import('Model', 'Members.Mylog');
App::uses('CakeEmail', 'Network/Email');

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
				'pay_plan' => 'basic'
			]];
			if($this->save($PointUser)){
				return $this->getLastInsertId();
			}else{
				$this->log('PointUser.php getPointUserId save error.');
			}
		}
	}
	
	//引けるポイントが有るかチェック
	// $point 正の整数
	public function pointCheck($mypage_id, $point){
		$PointUser = $this->findByMypageId($mypage_id, null, null , -1);
		if($PointUser['PointUser']['pay_plan'] == 'pay_off'){
			return true;
		}
		if($PointUser['PointUser']['point'] >= $point){
			return true;
		}else{
			return false;
		}
	}
	
	//引ける使用可能ポイントが有るかチェック
	// $point 正の整数
	public function availablePointCheck($mypage_id, $point){
		$PointUser = $this->findByMypageId($mypage_id, null, null , -1);
		if($PointUser['PointUser']['pay_plan'] == 'pay_off'){
			return true;
		}
		if($PointUser['PointUser']['available_point'] >= $point){
			return true;
		}else{
			return false;
		}
	}
	
	//支払い方法変更、pay_offに切り替わったら既存のcall予約をすべて削除する。
	public function payPlanEdit($data){
		$PointUser = $this->findByMypageId($data['PointUser']['mypage_id']);
		$this->Plugin = new Plugin;
		$pluginList = $this->Plugin->find('list', ['conditions'=>['Plugin.status'=>1]]);
		if($data['PointUser']['pay_plan'] == 'pay_off' and $PointUser['PointUser']['pay_plan'] != 'pay_off'){
			// Nosプラグインがあったら、の場合
			foreach($pluginList as $plugin){
				if($plugin == 'Nos'){
					$this->NosCall = ClassRegistry::init('Nos.NosCall');
					if(!$this->NosCall->deleteAllReserve($data['PointUser']['mypage_id'])){
						$this->log('Pointuser.php payPlanEdit deleteAllReserve error: '.print_r($data, true));
						return false;
					}
				}
			}
		}
		$PointUser['PointUser']['pay_plan'] = $data['PointUser']['pay_plan'];
		$PointUser['PointUser']['invoice_plan'] = $data['PointUser']['invoice_plan'];
		$this->create();
		return $this->save($PointUser);
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
		// pay_off はマイナス入る
		if($PointUser['PointUser']['pay_plan'] == 'pay_off'){
			$new_point = $PointUser['PointUser']['point'] + $data['point'];
			$new_available_point = $PointUser['PointUser']['available_point'];
		}else{
			$new_point = $PointUser['PointUser']['point'] + $data['point'];
			$new_available_point = $PointUser['PointUser']['available_point'] + $data['point'];
			if($new_point < 0) return false; //ポイントはマイナスにならない。
			if($new_available_point < 0) return false;//使用可能ポイントはマイナスにならない
		}
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
			$this->log('PointUser.php PointAdd save error. : '.print_r($e->getMessage(), true));
			return false;
		}
		return $PointBook;
	}
	
	//ポイント減算（サービス消費）
	// $data['point'] 負の正数指定
	// $data['credit'] pointとcreditが一致しない場合に指定。例）利用時にポイントを引く場合など、['point'=>'-45', 'credit'=>'-50']
	//$data = ['mypage_id'=>0, 'point_user_id'=>0, 'point'=>0, 'reason'=>'', 'reason_id'=>'', 'credit'=>'0'];
	public function pointExp($data = []){
		if(empty($data['mypage_id'])) return false;
		if(empty($data['point_user_id'])) $data['point_user_id'] = $this->getPointUserId($data['mypage_id']);
		if(empty($data['point']) && $data['point'] == 0) return false; //0の数字は指定できない。
		if(empty($data['reason'])) return false;
		if(empty($data['reason_id'])) $data['reason_id'] = '';
		if(empty($data['credit'])) $data['credit'] = '';
		
		//ポイント計算、pay_plan,reasonで処理方法を変える
		$PointUser = $this->findById($data['point_user_id'], null, null, -1);
		$point_cal = $this->pointCal($PointUser, $data);
		if($point_cal){
			$point = $point_cal['point'];
			$credit = $point_cal['credit'];
			$new_point = $point_cal['new_point'];
			$new_credit = $point_cal['new_credit'];
		}else{
			return false;
		}
		
		$datasource = $this->getDataSource();
		try{
			$datasource->begin();
			$this->create();
			$saveField = ['point', 'credit', 'available_point'];
			$save_point_user = ['PointUser' => [
				'id' => $PointUser['PointUser']['id'],
				'point' => $new_point,
				'credit' => $new_credit,
				'available_point' => $new_point - $new_credit,
			]];
			if(!$this->save($save_point_user, null, $saveField)){
				throw new Exception();
			}
			$this->PointBook->create();
			$PointBook = ['PointBook' => [
				'mypage_id' => $PointUser['PointUser']['mypage_id'],
				'point_user_id' => $PointUser['PointUser']['id'],
				'point' => $point,
				'credit' => $credit,
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
			$this->log('PointUser.php PointExp save error. : '.print_r($e->getMessage(), true));
			return false;
		}
		if($PointUser['PointUser']['pay_plan'] == 'auto'){
			sleep('1');//2重決済防止
			$this->payjpRunAutoCharge($data['mypage_id']);
		}
		return $PointBook;
	}
	
	// ポイント計算　pointExpから引き継ぎ分離
	public function pointCal($PointUser, $data){
		$return = [];
		if($PointUser['PointUser']['pay_plan'] == 'auto'){
			$point = $data['point'];
			$credit = 0;
			$new_point = $PointUser['PointUser']['point'] + $point;
			$new_credit = 0;
			if($new_point < 0) return false; //ポイントはマイナスにならない。
		}elseif($PointUser['PointUser']['pay_plan'] == 'pay_off'){
			$point = $data['point'];
			$credit = 0;
			$new_point = $PointUser['PointUser']['point'] + $point;
			$new_credit = 0;
		}elseif($data['reason'] == 'call_out' || $data['reason'] == 'emergency'){
			//creditは変更しない
			$point = $data['point'];
			$new_point = $PointUser['PointUser']['point'] + $point;
			$new_credit = $PointUser['PointUser']['credit'];
			$credit = 0;
		}else{
			if(!empty($data['credit'])){
				$point = $data['point'];
				$credit = $data['credit'];
				$new_point = $PointUser['PointUser']['point'] + $point;
				$new_credit = $PointUser['PointUser']['credit'] + $credit;
			}else{
				$point = $credit = $data['point'];
				$new_point = $PointUser['PointUser']['point'] + $point;
				$new_credit = $PointUser['PointUser']['credit'] + $credit;
			}
			if($new_point < 0) return false; //ポイント、クレジットはマイナスにならない。
			if($new_credit < 0) return false;
		}
		$return = [
			'point' => $point,
			'credit' => $credit,
			'new_point' => $new_point,
			'new_credit' => $new_credit
		];
		return $return;
	}
	
	//クレジット加算（サービス予約） $data['point'] 正の正数指定
	//クレジット減算（サービス予約取消） $data['point'] 負の正数指定
 	//$data = ['mypage_id'=>0, 'point_user_id'=>0, 'point'=>0, 'reason'=>'', 'reason_id'=>''];
 	//最小 $data = ['mypage_id'=>0, 'point'=>0, 'reason'=>''];
	public function creditAdd($data = []){
		if(empty($data['mypage_id'])) return false;
		if(empty($data['point_user_id'])) $data['point_user_id'] = $this->getPointUserId($data['mypage_id']);
		if(empty($data['point'])) return false;
		if(empty($data['reason'])) return false;
		if(empty($data['reason_id'])) $data['reason_id'] = '';
		// pay_plan = basic のみで有効。basic以外はtrueを返してスルー。念の為pay_planが無かったら通常扱い。
		$PointUser = $this->findById($data['point_user_id'], null, null, -1);
		if(!empty($PointUser['PointUser']['pay_plan'])){
			if($PointUser['PointUser']['pay_plan'] != 'basic') return true;
		}
		
		//ポイント計算
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
				'id' => $PointUser['PointUser']['id'],
				'credit' => $new_credit,
				'available_point' => $new_available_point,
			]];
			if(!$this->save($save_point_user, null, $saveField)){
				throw new Exception();
			}
			$this->PointBook->create();
			$PointBook = ['PointBook' => [
				'mypage_id' => $PointUser['PointUser']['mypage_id'],
				'point_user_id' => $PointUser['PointUser']['id'],
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
	
	// payjp
	
	//都度払い
	public function payjpOnceCharge($payjp_token, $amount, $mypage_id){
		$siteUrl = Configure::read('BcEnv.siteUrl');
		$amountList = Configure::read('PointPlugin.AmountList');
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$charge = \Payjp\Charge::create([
			  'card' => $payjp_token,
			  'amount'=> $amount,
			  'currency' => 'jpy'
			]);
			if (isset($charge['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			$error_body = $e->getJsonBody();
			$this->log('Pointuser.php payjpOnceCharge : '.$error_body['error']['message']);
			return false;
		}
		// $charge->paid 与信が通った場合にtrue。都度決済で支払いと与信を同時に行うのでここではスルー。
		// $charge->captured 与信だけの場合はfalseが帰ってくる。ポイントは毎回決済するのでここではスルー。
		// $charge->id;//一意の決済key
		//念のため決済金額を確認。もし違っていたらログに残す。
		if($amount != $charge->amount){
			$this->log('Warning : PointUser.php payjpOnceCharge. Different amounts. payjp_id:'.$charge->id);
		}
		$point_add = [
			'mypage_id' => $mypage_id,
			'point' => $amountList[$amount],
			'reason' => 'payjp',
			'pay_token' => $charge->id,
			'charge' => $amount
		];
		$pointBook = $this->pointAdd($point_add);
		if($pointBook){
			$pointBook['PointBook']['brand'] = $charge->card->brand;//カードのブランド
			$pointBook['PointBook']['last4'] = $charge->card->last4;//カードの下4桁
			$pointBook['PointBook']['siteUrl'] = $siteUrl;
			$pointBook['PointBook']['loginUrl'] = $siteUrl.'members/mypages/login';
			return $pointBook;
		}else{
			$this->log('Warning : PointUser.php payjpOnceCharge. pointAdd error. payjp_id:'.$charge->id);
			return false;
		}
	}
	
	//オートチャージ　新規登録
	public function payjpNewAutoCharge($payjp_token, $charge, $mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$Customer = \Payjp\Customer::create([
			  'card' => $payjp_token,
			  'id'=> $mypage_id,
			]);
			if (isset($Customer['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			$error_body = $e->getJsonBody();
			$this->log('Pointuser.php payjpNewAutoCharge : '.$error_body['error']['message']);
			return false;
		}
		foreach($Customer->cards->data as $data){
			if($Customer->default_card == $data->id){
				$pointUser['PointUser']['payjp_card_token'] = $data->id;
				$pointUser['PointUser']['payjp_brand'] = $data->brand;
				$pointUser['PointUser']['payjp_last4'] = $data->last4;
			}
		}
		$pointUser['PointUser']['charge_point'] = $charge;
		$pointUser['PointUser']['pay_plan'] = 'auto';
		$pointUser['PointUser']['auto_charge_status'] = 'success';
		$pointUser['PointUser']['credit'] = 0;
		$pointUser['PointUser']['available_point'] = $pointUser['PointUser']['point'];
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_setup');
			return $this->payjpRunAutoCharge($mypage_id);
		}else{
			$this->log('Pointuser.php payjpNewAutoCharge save error: '.print_r($pointUser, true));
			return false;
		}
	}
	
	//オートチャージ　変更
	public function payjpEditAutoCharge($payjp_token, $charge, $mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		//2重クリック防止 8秒以内の更新は無効
		if((time() - strtotime($pointUser['PointUser']['modified'])) < 8) return false;
		if(!empty($charge)){
			$pointUser['PointUser']['charge_point'] = $charge;
		}
		if(!empty($payjp_token)){
			$secret_key = Configure::read('payjp.secret');
			\Payjp\Payjp::setApiKey($secret_key);
			$cu = \Payjp\Customer::retrieve($mypage_id);
			try {
				$card = $cu->cards->retrieve($pointUser['PointUser']['payjp_card_token']);
				$card->delete();
				if (isset($card['error'])) {
			        throw new Exception();
			    }
				$card = $cu->cards->create(array(
					"card" => $payjp_token,
					"default" => true
				));
				if (isset($card['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('Pointuser.php payjpEditAutoCharge new card add error: '.$error_body['error']['message']);
				return false;
			}
			$pointUser['PointUser']['payjp_card_token'] = $card->id;
			$pointUser['PointUser']['payjp_brand'] = $card->brand;
			$pointUser['PointUser']['payjp_last4'] = $card->last4;
			$pointUser['PointUser']['auto_charge_status'] = 'success';
		}
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_edit');
			return $this->payjpRunAutoCharge($mypage_id);
		}else{
			$this->log('Pointuser.php payjpEditAutoCharge save error: '.print_r($pointUser, true));
			return false;
		}
	}
	
	//オートチャージ実行。現ポイントを見てbreakPoint以下だったら実行。
	public function payjpRunAutoCharge($mypage_id){
		$pointUser = $this->findByMypageId($mypage_id);
		$BreakPoint = Configure::read('PointPlugin.BreakPoint');
		$siteUrl = Configure::read('BcEnv.siteUrl');
		$amountList = Configure::read('PointPlugin.AmountList');
		if(!empty($pointUser['PointUser']['payjp_card_token']) && 
				$pointUser['PointUser']['pay_plan'] == 'auto' &&
				$pointUser['PointUser']['point'] <= $BreakPoint &&
				$pointUser['Mypage']['status'] == 0)
			{
			$secret_key = Configure::read('payjp.secret');
			\Payjp\Payjp::setApiKey($secret_key);
			try {
				$charge = \Payjp\Charge::create([
				  'card' => $pointUser['PointUser']['payjp_card_token'],
				  'amount'=> $pointUser['PointUser']['charge_point'],
				  'customer' => $mypage_id,
				  'currency' => 'jpy'
				]);
				if (isset($charge['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('Pointuser.php payjpRunAutoCharge : '.$error_body['error']['message']);
				if($error_body['error']['type'] == 'server_error'){
					$this->sendEmail(Configure::read('BcSite.email'), 'オートチャージ server_error', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}elseif($error_body['error']['type'] == 'client_error'){
					$this->sendEmail(Configure::read('BcSite.email'), 'オートチャージ client_error', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}else{
					$pointUser['PointUser']['auto_charge_status'] = 'fail';
					$this->create();
					$this->save($pointUser);
					$pointUser['PointBook']['BreakPoint'] = $BreakPoint;
					$this->sendEmail($pointUser['Mypage']['email'], 'ポイントチャージに失敗しました。', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}
				return false;
			}
			$point_add = [
				'mypage_id' => $mypage_id,
				'point' => $amountList[$pointUser['PointUser']['charge_point']],
				'reason' => 'payjp_auto',
				'pay_token' => $charge->id,
				'charge' => $pointUser['PointUser']['charge_point']
			];
			$pointBook = $this->pointAdd($point_add);
			if($pointBook){
				$pointBook['PointBook']['BreakPoint'] = $BreakPoint;
				$pointBook['PointBook']['brand'] = $charge->card->brand;//カードのブランド
				$pointBook['PointBook']['last4'] = $charge->card->last4;//カードの下4桁
				$pointBook['PointBook']['siteUrl'] = $siteUrl;
				$pointBook['PointBook']['loginUrl'] = $siteUrl.'members/mypages/login';
				$this->sendEmail($pointUser['Mypage']['email'], 'ポイントチャージ', $pointBook, array('template'=>'Point.auto_charge', 'layout'=>'default'));
			}else{
				$this->log('Warning : PointUser.php payjpRunAutoCharge. pointAdd error. payjp_id:'.$charge->id);
				return false;
			}
		}
		return true;
	}
	
	//オートチャージ解除
	//サービス予約の解除が必要。auto_charge_status:cancell をイベントでキャッチして解除する。
	public function payjpCancellAutoCharge($mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$cu = \Payjp\Customer::retrieve($mypage_id);
			$cu->delete();
			if (isset($cu['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			$error_body = $e->getJsonBody();
			$this->log('Pointuser.php payjpCancellAutoCharge : '.$error_body['error']['message']);
			return false;
		}
		$pointUser['PointUser']['payjp_card_token'] = NULL;
		$pointUser['PointUser']['payjp_brand'] = NULL;
		$pointUser['PointUser']['payjp_last4'] = NULL;
		$pointUser['PointUser']['charge_point'] = NULL;
		$pointUser['PointUser']['pay_plan'] = 'basic';
		$pointUser['PointUser']['auto_charge_status'] = 'cancell';
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_cancell');
			return true;
		}else{
			$this->log('Pointuser.php payjpCancellAutoCharge : '.$error_body['error']['message']);
			return false;
		}
	}
	
	public function sendEmail($to, $title = '', $body = '', $options = array()){
		if(Configure::read('MccPlugin.TEST_MODE')){
			$email_piece = Configure::read('MccPlugin.TEST_EMAIL_PIECE');
			if(strpos($to, $email_piece) === false) return true;
		}
		if(!Configure::read('MccPlugin.TEST_MODE')){
			$bcc = Configure::read('MccPlugin.sendMailBcc');
			if(empty($bcc)){
				$bcc = Configure::read('BcSite.email');
			}
		}
		
		$this->siteConfigs = Configure::read('BcSite');
		$config = array(
			'transport' => 'Smtp',
			'host' => $this->siteConfigs['smtp_host'],
			'port' => ($this->siteConfigs['smtp_port']) ? $this->siteConfigs['smtp_port'] : 25,
			'username' => ($this->siteConfigs['smtp_user']) ? $this->siteConfigs['smtp_user'] : null,
			'password' => ($this->siteConfigs['smtp_password']) ? $this->siteConfigs['smtp_password'] : null,
			'tls' => $this->siteConfigs['smtp_tls'] && ($this->siteConfigs['smtp_tls'] == 1)
		);
		$cakeEmail = new CakeEmail($config);
		// charset
		if (!empty($this->siteConfigs['mail_encode'])) {
			$encode = $this->siteConfigs['mail_encode'];
		} else {
			$encode = 'ISO-2022-JP';
		}
		$cakeEmail->headerCharset($encode);
		$cakeEmail->charset($encode);
		$cakeEmail->emailFormat('text');
		
		$cakeEmail->addTo($to);
		$cakeEmail->subject($title);
		if (!empty($this->siteConfigs['formal_name'])) {
			$fromName = $this->siteConfigs['formal_name'];
		}else{
			$fromName = Configure::read('BcApp.title');
		}
		$from = $this->siteConfigs['email'];
		$body['mailConfig']['site_name'] = $fromName;
		$body['mailConfig']['site_url'] = Configure::read('BcEnv.siteUrl');
		$body['mailConfig']['site_email'] = $from;
		
		$cakeEmail->from($from, $fromName);
		if(!empty($bcc)){
			$cakeEmail->bcc($bcc);
		}
		$cakeEmail->replyTo($from);
		$cakeEmail->returnPath($from);
		$cakeEmail->viewRender('BcApp');
		if(empty($options['layout'])){
			$options['layout'] = 'default';
		}
		
		$cakeEmail->template($options['template'], $options['layout']);
		$cakeEmail->viewVars($body);
		
		try {
			$cakeEmail->send();
			return true;
		}catch(Exception $e){
			$this->log('PointUser.php sendEmail error. '.$e->getMessage());
			return false;
		}
	}

}