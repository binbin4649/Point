<?php
/*
* Pointプラグイン
* 基底コントローラー
*/

/**
* Include files
*/
App::uses('AppController', 'Controller');

class PointAppController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
		//ログイン画面：デフォルトだとUserControllerになってしまうので強制的に変更する。
		$this->BcAuth->loginAction = array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'login');
		$this->BcAuth->loginRedirect = array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index');
		
		$this->set('user', $this->BcAuth->user());
		
		// サブサイト用の設定値なのかな？ない時はとりあえず0を入れておく。
		//サブサイト用の設定だとしたらPointsプラグインはサブサイトに対応してない。ということになるんだと思う。
		if(empty($this->request->params['Content'])) {
			$this->request->params['Site']['id'] = 0;
		}
	}
}
