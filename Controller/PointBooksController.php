<?php 

class PointBooksController extends PointAppController {
  
  public $name = 'PointBooks';

  public $uses = array('Plugin', 'Point.PointUser', 'Point.PointBook', 'Members.Mypage');
  
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
    $this->pageTitle = 'PointBook';
    $conditions = [];
    if ($this->request->is('post')){
      $data = $this->request->data;
      if($data['PointBook']['mypage_id']) $conditions[] = array('PointBook.mypage_id' => $data['PointBook']['mypage_id']);
      if($data['PointBook']['reason']) $conditions[] = array('PointBook.reason' => $data['PointBook']['reason']);
    }
    $this->paginate = array('conditions' => $conditions,
      'order' => 'PointBook.id DESC',
      'limit' => 50
    );
    $PointBooks = $this->paginate('PointBook');
    $this->set('PointBooks', $PointBooks);
  }
  
  public function admin_edit($id = null){
	  $this->pageTitle = 'PointBook 編集';
	  if(empty($this->request->data)){
		  $PointBook = $this->PointBook->findById($id);
	  }else{
		  if($this->PointBook->save($this->request->data)){
	        $this->setMessage( '編集しました');
	        $this->redirect(array('action' => 'index'));
	      }else{
		    $PointBook = $this->PointBook->findById($id);
	        $this->setMessage('エラー', true);
	      }
	  }
	  $this->request->data = $PointBook;
  }
  
  
  // ポイント・クレジット履歴
  public function index() {
	$this->pageTitle = 'PointBook';
    $user = $this->BcAuth->user();
    if(!$user){
		$this->setMessage('エラー: user error.', true);
		$this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
	}
  	$this->paginate = array(
  		'conditions' => array('PointBook.mypage_id'=>$user['id']),
  		'order' => 'PointBook.id DESC',
  		'limit' => 20,
  		'recursive' => -1
    );
    $PointBooks = $this->paginate('PointBook');
    $reasonList = Configure::read('PointPlugin.ReasonList');
    foreach($PointBooks as $key=>$book){
	    if(!empty($reasonList[$book['PointBook']['reason']])){
			$PointBooks[$key]['PointBook']['reason'] = $reasonList[$book['PointBook']['reason']];
	    }
    }
    $this->set('PointBooks', $PointBooks);
    
  }





}






?>