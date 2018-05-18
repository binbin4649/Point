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
  
    //$this->BcAuth->allow('');
  }

  //管理画面用のデフォルトアクション
  public function admin_index() {
    
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
  		'limit' => 10,
  		'recursive' => -1
    );
    $PointBooks = $this->paginate('PointBook');
    $this->set('PointBooks', $PointBooks);
    $this->set('reasonList', Configure::read('PointPlugin.ReasonList'));
    
  }





}






?>