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
    //$this->BcAuth->allow('');
    if(preg_match('/^admin_/', $this->action)){
	   $this->subMenuElements = array('point');
    }
    $this->Security->unlockedActions = array('payment');
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
	  $pointUser = $this->PointUser->findById($id);
	  if ($this->request->is('post')){
	  	$data = $this->request->data['PointUser'];
	  	$pointBook = $this->PointUser->pointAdd($data);
		if($pointBook){
			$this->setMessage( '調整しました');
			$this->redirect(array('action' => 'edit'));
		}else{
			$this->setMessage('エラー', true);
		}
	  }
	  $this->set('pointUser', $pointUser);
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



}






?>