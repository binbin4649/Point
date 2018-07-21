<?php
	
/**
 * 0.0.1 バージョン アップデートスクリプト
 */

/**
 * mcc_calls テーブル構造変更
 */
if($this->loadSchema('0.0.1', 'Point', 'point_users', $filterType = 'alter')) {
	$this->setUpdateLog('point_users テーブルの構造変更に成功しました。');
} else {
	$this->setUpdateLog('point_users テーブルの構造変更に失敗しました。', true);
}

if($this->loadSchema('0.0.1', 'Point', 'point_books', $filterType = 'alter')) {
	$this->setUpdateLog('point_books テーブルの構造変更に成功しました。');
} else {
	$this->setUpdateLog('point_books テーブルの構造変更に失敗しました。', true);
}