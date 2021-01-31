<?php 

// komoju関連は全て中途半端です。

class PointUsersController extends PointAppController {
  
  public $name = 'PointUsers';

  public $uses = array('Plugin', 'Point.PointUser', 'Point.PointBook', 'Members.Mypage', 'Members.Mylog');
  
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
    $this->Security->unlockedActions = array('payment', 'auto_charge', 'subscription', 'komoju', 'komoju_webhook');
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
  public function admin_adjust($id){
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
  
  //ユーザー編集
  public function admin_edit($id = null){
	  $this->pageTitle = 'PointUser 編集';
	  $user = $this->BcAuth->user();
	  if(empty($this->request->data)){
		  $PointUser = $this->PointUser->findById($id);
	  }else{
		  if($this->PointUser->payPlanEdit($this->request->data)){
	        $this->Mylog->record($id, 'point_user_edit', $user['id']);
	        $this->setMessage( '編集しました');
	        $this->redirect(array('action' => 'index'));
	      }else{
		    $PointUser = $this->PointUser->findById($id);
	        $this->setMessage('エラー', true);
	      }
	  }
	  $this->set('PayPlan', Configure::read('PointPlugin.PayPlanList'));
	  $this->set('InvoicePlan', Configure::read('PointPlugin.InvoicePlanList'));  
	  $this->request->data = $PointUser;
  }
  
  // フロント画面用のデフォルトアクション
  public function index() {
    $user = $this->BcAuth->user();
    $this->pageTitle = 'Point';
  }

  //支払い金額を選択
  public function payselect(){
	$this->pageTitle = 'ポイント購入';
	// 有効のプラグインリストを取得
    $pluginList = $this->Plugin->find('list', ['conditions'=>['Plugin.status'=>1]]);
    $this->set('pluginList', $pluginList);
	$this->set('amountList', Configure::read('PointPlugin.AmountList'));
  }
  
  public function pay_method(){
	  if($this->request->data){
		  $data = $this->request->data['PointUser'];
		  if(empty($data['charge']) or empty($data['method'])){
			  $this->setMessage('金額、お支払い方法を選択してください。', true);
			  $this->redirect(array('plugin'=>'point', 'controller'=>'point_users', 'action'=>'payselect'));
		  }
		  if($data['method'] == 'credit'){
			  $this->redirect(array('plugin'=>'point', 'controller'=>'point_users', 'action'=>'payment/'.$data['charge']));
		  }
		  if($data['method'] == 'bitcash'){
			  $this->redirect(array('plugin'=>'bitcash', 'controller'=>'bitcashes', 'action'=>'setle/'.$data['charge']));
		  }
	  }else{
		  $this->redirect(array('plugin'=>'point', 'controller'=>'point_users', 'action'=>'payselect'));
	  }
  }
  
  
  // payjp 決済画面
  public function payment($amount){
	  $user = $this->BcAuth->user();
	  $amountList = Configure::read('PointPlugin.AmountList');
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
		$pointBook = $this->PointUser->payjpOnceCharge($payjp_token, $amount, $user['id']);
		if($pointBook){
			$this->sendMail($user['email'], 'ご購入ありがとうございます', $pointBook, array('template'=>'Point.thanks', 'layout'=>'default'));
			$this->setMessage('ご購入ありがとうございます。');
			$this->redirect(array('controller'=>'point_users', 'action' => 'thanks/'.$pointBook['PointBook']['id']));
		}else{
			$this->setMessage('決済エラー：時間を空けて再度お試しいただくか、お問合せよりご連絡ください。', true);
			$this->redirect(array('action' => 'payselect/'.$amount));
		}
	  }
	  $this->set('amount', $amount);
	  $this->set('point', $amountList[$amount]);
	  $this->set('payjp_public', Configure::read('payjp.public'));
  }
  
  public function thanks($pointbook_id){
	  $this->pageTitle = 'Thanks';
	  $pointBook = $this->PointBook->findById($pointbook_id);
	  $this->set('book', $pointBook);
  }
  
  //オートチャージの開始、設定変更、解除
  public function auto_charge(){
	  $user = $this->BcAuth->user();
	  $this->pageTitle = 'オートチャージ(自動決済)';
	  $PointUser = $this->PointUser->findByMypageId($user['id']);
	  if($PointUser['PointUser']['pay_plan'] == 'auto' && !empty($PointUser['PointUser']['payjp_card_token'])){
		  $isAutoCharge = true;
	  }else{
		  $isAutoCharge = false;
	  }
	  if($this->request->data){
		  $charge = $this->request->data['PointUser']['charge'];
		  $payjp_token = $this->request->data['payjp-token'];
		  if($isAutoCharge){
			  if(empty($charge) && empty($payjp_token)){
				  $this->setMessage('変更はありません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if($this->PointUser->payjpEditAutoCharge($payjp_token, $charge, $user['id'])){
				  $this->setMessage('オートチャージの設定を変更しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：時間を空けて再度お試しいただくか、お問合せからご連絡ください。', true);
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }
		  }else{
			  if(empty($charge)){
				  $this->setMessage('金額が選択されていません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if(empty($payjp_token)){
				  $this->setMessage('カード情報が入力されていません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if($this->PointUser->payjpNewAutoCharge($payjp_token, $charge, $user['id'])){
				  $this->setMessage('オートチャージを設定しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：時間を空けて再度お試しいただくか、お問合せからご連絡ください。', true);
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }
		  }
	  }
	  $chargeList = [];
	  $amountList = Configure::read('PointPlugin.AmountList');
	  foreach($amountList as $amount=>$point){
		  $chargeList[$amount] = number_format($amount).'円('.$point.'ポイント)';
	  }
	  $this->set('chargeList', $chargeList);
	  $this->set('BreakPoint', Configure::read('PointPlugin.BreakPoint'));
	  $this->set('payjp_public', Configure::read('payjp.public'));
	  $this->set('PointUser', $PointUser);
	  $this->set('isAutoCharge', $isAutoCharge);
  }
  
  //オートチャージ解除
  public function cancell_auto_charge(){
	  $user = $this->BcAuth->user();
	  $this->pageTitle = 'オートチャージ解除';
	  $PointUser = $this->PointUser->findByMypageId($user['id']);
	  if($PointUser['PointUser']['pay_plan'] == 'auto' && !empty($PointUser['PointUser']['payjp_card_token'])){
		  $isAutoCharge = true;
	  }else{
		  $isAutoCharge = false;
	  }
	  if(!$isAutoCharge){
		  $this->setMessage('オートチャージは登録されていません。', true);
		  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
	  }
	  if($this->request->data){
		  if($this->request->data['PointUser']['cancell'] == '1'){
			  if($this->PointUser->payjpCancellAutoCharge($user['id'])){
				  $this->setMessage('オートチャージを解除しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：解除失敗。お手数ですが、お問合せからご連絡ください。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'cancell_auto_charge'));
			  }
		  }else{
			  $this->setMessage('チェックを入れてボタンを押してください。', true);
			  $this->redirect(array('controller'=>'point_users', 'action' => 'cancell_auto_charge'));
		  }
	  }
	  
	  $this->set('PointUser', $PointUser);
  }
  
  
  //月額課金 pay_plan未対応（変更されたら日割りとか面倒くさいわ）
  public function subscription(){
	  $user = $this->BcAuth->user();
	  $this->pageTitle = 'クレジットカード登録(お支払い)';
	  $PointUser = $this->PointUser->getPointUser($user['id']);
	  if($this->request->data){
		  $charge = $this->request->data['PointUser']['charge'];
		  $payjp_token = $this->request->data['payjp-token'];
		  if(empty($payjp_token)){
			  $this->setMessage('カード情報が入力されていません。', true);
			  $this->redirect(array('controller'=>'point_users', 'action' => 'subscription'));
		  }
		  if($this->PointUser->payjpNewCustomer($payjp_token, 'month', $user['id'])){
			  $this->setMessage('クレジットカード登録。初月の決算を行いました。');
			  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
		  }else{
			  $this->setMessage('クレジットカードを登録しました。');
			  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
		  }
	  }
	  $this->set('AmountList', Configure::read('PointPlugin.AmountList'));
	  $this->set('payjp_public', Configure::read('payjp.public'));
	  $this->set('PointUser', $PointUser);
  }
  
  public function cancell_subscription(){
	  $this->autoRender = false;
	  $user = $this->BcAuth->user();
	  $PointUser = $this->PointUser->getPointUser($user['id']);
	  if(empty($PointUser['PointUser']['payjp_card_token'])){
		  $this->setMessage('登録されていません。', true);
		  $this->redirect(array('controller'=>'point_users', 'action' => 'subscription'));
	  }else{
		  if($this->PointUser->payjpCustomerCancell($user['id'])){
			  $this->setMessage('クレジットカードの登録を解除しました。');
		  }else{
			  $this->setMessage('エラー：解除に失敗しました。', true);
		  }
	  }
	  $this->redirect(array('controller'=>'point_users', 'action' => 'subscription'));
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