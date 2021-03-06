<?php
 
$config['BcApp.adminNavi.point'] = array(
  'name' => 'ポイント管理プラグイン',
  'contents' => array(
    array('name' => 'ユーザー管理', 'url' => array('admin' => true, 'plugin' => 'point', 'controller' => 'point_users', 'action' => 'index')),
    array('name' => 'PointBook', 'url' => array('admin' => true, 'plugin' => 'point', 'controller' => 'point_books', 'action' => 'index')),
    array('name' => 'クーポン一覧', 'url' => array('admin' => true, 'plugin' => 'point', 'controller' => 'point_coupons', 'action' => 'index')),
    array('name' => 'クーポン生成', 'url' => array('admin' => true, 'plugin' => 'point', 'controller' => 'point_coupons', 'action' => 'add')),
  )
);


// viewでreasonを変換したい時に使う
// [PointBook] ポイント・クレジットの履歴
// [PointBook.reason] ポイント・クレジットの増減理由
$config['PointPlugin.ReasonList'] = [
	'welcome' => '新規登録',
	'payjp' => 'クレジットカード支払',
	'run' => 'サービス実行',
	'reserve' => 'サービス予約',
	'reserve_delete' => '予約取消',
	'payjp_auto' => 'オートチャージ',
	'other' => 'その他',
];

$config['PointPlugin.PayPlanList'] = ['basic'=>'basic', 'auto'=>'auto', 'pay_off'=>'pay_off'];
$config['PointPlugin.InvoicePlanList'] = ['end_month'=>'末締翌月末払'];

// 決済金額の種類。リスト。 決済金額 => 発行されるポイント
//$config['PointPlugin.AmountList'] = ['1500' => '1500','3000' => '3000','6000' => '6100','9000' => '9300'];

//何ポイント以下になったらオートチャージされるのかのブレイクポイント
//$config['PointPlugin.BreakPoint'] = 500;

?>