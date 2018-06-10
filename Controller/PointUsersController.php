<?php 
require_once(dirname(__FILE__)."/../vendor/autoload.php");

class PointUsersController extends PointAppController {
  
  public $name = 'PointUsers';

  public $uses = array('Plugin', 'Point.PointUser', 'Point.PointBook', 'Members.Mypage');
  
  public $helpers = array('BcPage', 'BcHtml', 'BcTime', 'BcForm');
  
  public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];
  
  public $subMenuElements = array('');

  public $crumbs = array(
    array('name' => 'マイページトップ', 'url' => array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index')),
  );

  public function beforeFilter() {
    parent::beforeFilter();
    $this->BcAuth->allow('komoju_webhook');
    if(preg_match('/^admin_/', $this->action)){
	   $this->subMenuElements = array('point');
    }
    $this->Security->unlockedActions = array('payment', 'komoju', 'komoju_webhook');
  }

  //ポイントユーザー一覧。ユーザーを検索してポイント調整できる
  public function admin_index() {
	$this->pageTitle = '会員一覧(point)';
	$conditions = [];
	if ($this->request->is('post')){
      $data = $this->request->data;
      if($data['PointUser']['mypage_id']) $conditions[] = array('PointUser.mypage_id' => $data['PointUser']['mypage_id']);
      if($data['Mypage']['name']) $conditions[] = array('Mypage.name like' => '%'.$data['Mypage']['name'].'%');
    }
    $this->paginate = array('conditions' => $conditions,
      'order' => 'PointUser.id ASC',
      'limit' => 50
    );
    $this->PointUser->unbindModel(['hasMany' => ['PointBook']]);
    $pointUser = $this->paginate('PointUser');
    $this->set('pointUser', $pointUser);
  }
  
  // ポイント調整
  public function admin_edit($id){
	  $this->pageTitle = 'ポイント調整';
	  if(empty($this->request->data)){
		  $pointUser = $this->PointUser->findById($id);
	  }else{
		  $pointUser = $this->request->data;
		  if($this->PointUser->pointAdd($pointUser['PointUser'])){
			  $this->setMessage( '調整しました');
			  $this->redirect(array('action' => 'edit/'.$id));
		  }else{
			  $this->setMessage('エラー', true);
		  }
	  }
	  $this->request->data = $pointUser;
  }
  
  // フロント画面用のデフォルトアクション
  public function index() {
    $user = $this->BcAuth->user();
    $this->pageTitle = 'Point';
  }

  //支払い金額を選択
  public function payselect(){
	  $this->pageTitle = 'ポイント購入';
	  $this->set('amountList', Configure::read('PointPlugin.AmountList'));
  }
  
  //決済画面
  public function payment($amount){
	  $user = $this->BcAuth->user();
	  $amountList = Configure::read('PointPlugin.AmountList');
	  $siteUrl = Configure::read('BcEnv.siteUrl');
      if(!$user){
		$this->setMessage('エラー: user error.', true);
		$this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
	  }
	  $this->pageTitle = '決済';
	  $this->crumbs[] = ['name'=>'ポイント購入', 'url'=>['controller' => 'point_users', 'action' => 'payselect']];
	  if($this->request->data){
		$amount = $this->request->data['PointUser']['amount'];
		if(empty($amount)){
		  $this->setMessage('Error:', true);
		  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
		}
		$payjp_token = $this->request->data['payjp-token'];
		if(empty($payjp_token)){
		  $this->setMessage('カード情報が入力されていません。', true);
		  $this->redirect(array('controller'=>'point_users', 'action' => 'payment/'.$amount));
		}
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
		$charge = \Payjp\Charge::create([
			  'card' => $payjp_token,
			  'amount'=> $amount,
			  'currency' => 'jpy'
			]);
		}catch (\Payjp\Error\InvalidRequest $e){
			$message = $e->error->message;
			$this->log($message);
			$this->setMessage($message, true);
			$this->redirect(array('action' => 'payselect/'.$amount));
		}
		// $charge->paid 与信が通った場合にtrue。都度決済で支払いと与信を同時に行うのでここではスルー。
		// $charge->captured 与信だけの場合はfalseが帰ってくる。ポイントは毎回決済するのでここではスルー。
		// $charge->id;//一意の決済key
		//念のため決済金額を確認。もし違っていたらログに残す。
		if($amount != $charge->amount){
			$this->log('Warning : PointUserController.php payment. Different amounts. payjp_id:'.$charge->id);
		}
		$point_add = [
			'mypage_id' => $user['id'],
			'point' => $amountList[$amount],
			'reason' => 'payjp',
			'pay_token' => $charge->id,
			'charge' => $amount
		];
		$pointBook = $this->PointUser->pointAdd($point_add);
		if($pointBook){
			$pointBook['PointBook']['brand'] = $charge->card->brand;//カードのブランド
			$pointBook['PointBook']['last4'] = $charge->card->last4;//カードの下4桁
			$pointBook['PointBook']['siteUrl'] = $siteUrl;
			$pointBook['PointBook']['loginUrl'] = $siteUrl.'members/mypages/login';
			$this->sendMail($user['email'], 'ご購入ありがとうございます', $pointBook, array('template'=>'Point.thanks'));
			$this->setMessage('ご購入ありがとうございます。');
			$this->redirect(array('controller'=>'point_users', 'action' => 'thanks/'.$pointBook['PointBook']['id']));
		}else{
			$this->log('Warning : PointUserController.php payment. save error. payjp_id:'.$charge->id);
			$this->setMessage('決済エラー：お手数ですがお問合せよりご連絡ください。', true);
			$this->redirect(array('action' => 'payselect/'.$amount));
		}
	  }
	  $amountList = Configure::read('PointPlugin.AmountList');
	  $this->set('amount', $amount);
	  $this->set('point', $amountList[$amount]);
	  $this->set('payjp_public', Configure::read('payjp.public'));
  }
  
  public function thanks($pointbook_id){
	  $this->pageTitle = 'Thanks';
	  $pointBook = $this->PointBook->findById($pointbook_id);
	  $this->set('book', $pointBook);
  }
  
  public function komoju(){
	  $amountList = Configure::read('PointPlugin.AmountList');
	  $amount = 1500;
	  if($this->request->data){
		  $URL = "https://sandbox.komoju.jp/api/v1/payments";
		  $USERNAME = "sk_ff98c491524cc7fdc76fab77e0fd7321bde2b089";
		  $PASSWORD = "";
		  $POST_DATA = array(
			    'amount' => '1000',
			    'tax' => '0',
			    'currency' => 'JPY',
			    'external_order_num' => '2',
			    'metadata[foobar]' => 'hoge',
			    "payment_details" => $this->request->data['token'],
		  );
		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($POST_DATA));
		  curl_setopt($ch, CURLOPT_USERPWD, $USERNAME . ":" . $PASSWORD);
		  curl_setopt($ch, CURLOPT_URL, $URL);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		  $buf = curl_exec($ch);
		  curl_close($ch);
		  var_dump($buf);
	  }
	  $this->set('amount', $amount);
	  $this->set('point', $amountList[$amount]);
  }
  
  //https://qiita.com/TakahiroSakoda/items/65c3bd5aef8149e020dd
  //https://dev.dubmilli.com/point/point_users/komoju_webhook
  public function komoju_webhook(){
	$token = "rtyuikmnbvghio9876trfghj";
	$this->autoRender = false;
	$request_signature = '';
	foreach ($_SERVER as $name => $value) {
		if($name == "HTTP_X_KOMOJU_ID"){
			$request_id = $value;
		}
		if($name == "HTTP_X_KOMOJU_SIGNATURE"){
			$request_signature = $value;
		}
		if($name == "HTTP_X_KOMOJU_EVENT"){
			$request_event = $value;
		}
	}
	// bodyの中に、処理に必要なjsonが入ってる
	$body = file_get_contents('php://input');
	$signature = hash_hmac("sha256",$body,$token);
	if($signature === $request_signature){
		echo 'hello world.';
		$this->log('komoju id : '.$request_id);
		$this->log('komoju event : '.$request_event);
	}else{
		echo 'bad';
		$this->log('bad request!');
	}

  }



}






?>