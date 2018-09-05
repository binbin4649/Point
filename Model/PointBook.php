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
			    $this->log('PointBook invoiceEndMonth error. '.print_r($PointUser, true));
		    }
	    }
	    return true;
    }

}
