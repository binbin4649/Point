<?php
App::import('Model', 'AppModel');

class PointBook extends AppModel {

	public $name = 'PointBook';
	
	public $belongsTo = [
		'Mypage' => [
			'className' => 'Members.Mypage',
			'foreignKey' => 'mypage_id'],
		'PointUser' => [
			'className' => 'Point.PointUser',
			'foreignKey' => 'point_user_id']
	];
	
	// 2019-03-20 した2つ　使ってないじゃないかと思う、
	//締め作業振り分け
	// cron 23:52 とかに実行する
	public function invoiceJob(){
		$PointUsers = $this->PointUser->find('all', array(
        	'conditions' => array(
        		'PointUser.pay_plan' => 'pay_off',
        		'Mypage.status' => '0'
        	),
			'recursive' => 1,
		));
		foreach($PointUsers as $PointUser){
			$plan = $PointUser['PointUser']['invoice_plan'];
			//翌日が1日だったら末締め
			$next_day = date('d', strtotime('+1 day', time()));
			if($next_day == '01' && $plan == 'end_month'){
				$this->invoiceEndMonth($PointUser);
			}
		}
	}
	
	
    //締め作業 invoice_plan:end_month
    public function invoiceEndMonth($PointUser){
	    //ポイントがマイナスだったら締め作業開始
	    //マイナス分を正にしてamountに入れ、pointを0に戻す。
	    if($PointUser['PointUser']['point'] < 1){
		    $close_date = date('Y-m-d');
		    $deadline_date = date('Y-m-t', strtotime(date('Y-m-01') . '+1 month'));
		    $invoice_amount = abs($PointUser['PointUser']['point']);
		    $datasource = $this->getDataSource();
		    try{
			    $datasource->begin();
			    $PointUser['PointUser']['point'] = 0;
			    $PointUser['PointUser']['available_point'] = 0;
			    $this->PointUser->create();
			    if(!$this->PointUser->save($PointUser)){
				    throw new Exception();
			    }
			    $PointBook['PointBook'] = [
				    'mypage_id' => $PointUser['PointUser']['mypage_id'],
				    'point_user_id' => $PointUser['PointUser']['id'],
				    'point' => 0,
				    'credit' => 0,
				    'point_balance' => 0,
				    'credit_balance' => 0,
				    'reason' => 'end_month',
				    'close_date' => $close_date,
				    'deadline_date' => $deadline_date,
				    'invoice_amount' => $invoice_amount,
			    ];
			    $this->create();
			    if(!$this->save($PointBook)){
				    throw new Exception();
			    }
			    $datasource->commit();
		    }catch(Exception $e){
			    $datasource->rollback();
			    $this->log('PointBook invoiceEndMonth error. '.print_r($e->getMessage(), true));
			    $this->log('PointBook invoiceEndMonth error. '.print_r($PointUser, true));
		    }
	    }
	    return true;
    }
    
    public function monthlyUserBookConditions($ym, $mypage_ids){
	    $conditions = [];
		foreach($mypage_ids as $mypage_id){
			$conditions['OR'][] = ['PointBook.mypage_id' => $mypage_id];
		}
		if($ym === null){
		  $ym = date('Ym');
		}
		$year = substr($ym, 0, 4);
		$month = substr($ym, 4, 2);
		$conditions[] = ['PointBook.created >=' => date('Y-m-d 00:00:00', strtotime('first day of ' .$year.'-'.$month))];
		$conditions[] = ['PointBook.created <=' => date('Y-m-d 23:59:59', strtotime('last day of ' .$year.'-'.$month))];
		return $conditions;
    }
    
    // 月別にuserのPointBookを返す
    // $ym = YYYYMM
    // $mypage_ids array : mypage_idの配列
    public function monthlyUserBook($ym, $mypage_ids){
		$conditions = $this->monthlyUserBookConditions($ym, $mypage_ids);
		$books = $this->find('all', [
		  'conditions' => $conditions,
		  'order' => 'PointBook.created DESC',
		  'recursive' => 1,
		]);
		return $books;
    }
    
    // reason と reason_id からMypageを書き換えて、月別にuserのPointBookを返す。
    public function monthlyReasonIdBook($ym, $mypage_ids, $plugin_name){
	    //$this->ccCall = ClassRegistry::init('Nos.NosCall');
	    $cc_call_name = $plugin_name.'Call';
	    $cc_user_name = $plugin_name.'User';
	    $this->ccCall = ClassRegistry::init($plugin_name.'.'.$cc_call_name);
	    $this->ccUser = ClassRegistry::init($plugin_name.'.'.$cc_user_name);
	    $books = $this->monthlyUserBook($ym, $mypage_ids);
	    foreach($books as $key=>$book){
		    if(!empty($book['PointBook']['reason_id']) && !empty($book['PointBook']['point'])){
			    $call = $this->ccCall->findById($book['PointBook']['reason_id'], null, null, -1);
			    if($call){
				    $mypage_id = $call[$cc_call_name]['mypage_id'];
				    $new_mypage = $this->Mypage->findById($mypage_id, null, null, -1);
				    if($new_mypage){
					    $books[$key]['Mypage'] = $new_mypage['Mypage'];
					    $cc_user = $this->ccUser->findByMypageId($mypage_id, null, null, -1);
					    if($cc_user){
						    $books[$key][$cc_user_name] = $cc_user[$cc_user_name];
					    }
				    }
			    }
		    }
	    }
	    return $books;
    }
    
    // [reason:point] point、reasonごとに月別集計、マイナスプラス逆転
    public function monthlyTotalByPlan($ym, $mypage_ids){
	    $monthlyTotal = [];
	    $books = $this->monthlyUserBook($ym, $mypage_ids);
	    foreach($books as $book){
		    $point = $book['PointBook']['point'];
		    if($point < 0){
			    $point = abs($point);
		    }else{
			    $point = '-'.$point;
		    }
		    $reason = $book['PointBook']['reason'].':'.$point;
		    if(empty($monthlyTotal[$reason])){
			    $monthlyTotal[$reason] = 1;
		    }else{
			    $monthlyTotal[$reason] = $monthlyTotal[$reason] +1;
		    }
	    }
	    return $monthlyTotal;
    }
    

}
