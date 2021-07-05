<?php 

class PointCouponsController extends PointAppController {
  
  public $name = 'PointCoupons';

  public $uses = array('Plugin', 'Point.PointUser', 'Point.PointBook', 'Members.Mypage', 'Point.PointCoupon');
  
  public $helpers = array('BcPage', 'BcHtml', 'BcTime', 'BcForm');
  
  public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];
  
  public $subMenuElements = array('');

  public $crumbs = array(
    array('name' => 'マイページトップ', 'url' => array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index')),
  );

  public function beforeFilter() {
    parent::beforeFilter();
	if(preg_match('/^admin_/', $this->action)){
	   $this->subMenuElements = array('point');
    }
    //$this->BcAuth->allow('');
  }

  //管理画面用のデフォルトアクション
  public function admin_index() {
    $this->pageTitle = 'クーポン一覧';
    $conditions = [];
	if ($this->request->is('post')){
      $data = $this->request->data;
      if($data['PointCoupon']['name']) $conditions[] = array('PointCoupon.name like' => '%'.$data['PointCoupon']['name'].'%');
      if($data['PointCoupon']['division']) $conditions[] = array('PointCoupon.division' => $data['PointCoupon']['division']);
      if($data['PointCoupon']['code']) $conditions[] = array('PointCoupon.code' => $data['PointCoupon']['code']);
      if($data['PointCoupon']['use_plan']) $conditions[] = array('PointCoupon.use_plan' => $data['PointCoupon']['use_plan']);
    }
    $this->paginate = array('conditions' => $conditions,
      'order' => 'PointCoupon.id ASC',
      'limit' => 50
    );
    $PointCoupon = $this->paginate('PointCoupon');
    $this->set('PointCoupon', $PointCoupon);
    $this->set('usePlan', Configure::read('PointPlugin.CouponUsePlan'));
  }
  
  public function admin_csv_download($division = null){
		$coupons = $this->PointCoupon->find('all', ['conditions'=>['PointCoupon.division'=>$division]]);
		$this->autoRender = false;
		$this->response->type('csv');
		$this->response->download($division.".csv");
		$fp = fopen('php://output','w');
		stream_filter_append($fp, 'convert.iconv.UTF-8/CP932', STREAM_FILTER_WRITE);
		//$head = ['code', 'name', 'division', 'start','finish', 'point', 'use_plan', 'use_time', 'generated', 'created'];
		$head = ['code'];
		fputcsv($fp, $head);
		foreach($coupons as $cp){
		  $output = [];
		  $output['code'] = $cp['PointCoupon']['code'];
/*
		  $output['name'] = $cp['PointCoupon']['name'];
		  $output['division'] = $cp['PointCoupon']['division'];
		  $output['start'] = $cp['PointCoupon']['start'];
		  $output['finish'] = $cp['PointCoupon']['finish'];
		  $output['point'] = $cp['PointCoupon']['point'];
		  $output['use_plan'] = $cp['PointCoupon']['use_plan'];
		  $output['use_time'] = $cp['PointCoupon']['use_time'];
		  $output['generated'] = $cp['PointCoupon']['generated'];
		  $output['created'] = $cp['PointCoupon']['created'];
*/
		  fputcsv($fp, $output);
		}
		fclose($fp);
	}
  
  //クーポン追加
  public function admin_add(){
	  $this->pageTitle = 'クーポン生成';
	  if($this->request->data){
		  $data = $this->request->data;
		  $this->PointCoupon->set($data);
		  if($this->PointCoupon->validates()){
			  if($this->PointCoupon->couponGenerator($data)){
				  $this->setMessage( '生成しました');
				  $this->redirect(array('action' => 'index'));
			  }else{
				  $this->setMessage( 'ERROR:', true);
			  }
		  }else{
			  $this->setMessage( 'エラー', true);
		  }
	  }
	  $this->set('usePlan', Configure::read('PointPlugin.CouponUsePlan'));
  }
  
  //クーポンチャージ
  public function add() {
	  $user = $this->BcAuth->user();
	  if($this->request->data){
		  $data = $this->request->data;
		  $data['Mypage']['id'] = $user['id'];
		  if($this->PointCoupon->couponChrage($data)){
			  $this->setMessage( 'ポイント追加：クーポンチャージしました。');
			  $this->redirect(array('controller' => 'point_books', 'action' => 'index'));
		  }else{
			  $this->setMessage( '無効なクーポン：使用済、期限切れ、またはクーポンコードを間違えて入力しています。', true);
		  }
	  }
	  
  }





}






?>