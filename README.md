
# baserCMS用　ポイント管理プラグイン

会員管理プラグインと一緒に使うことを前提にしたプラグインです。  
つまりプラグインのプラグインみたいな感じ。  

会員管理プラグイン  
https://github.com/binbin4649/Members  

1. ユーザーがポイントを購入。
2. サービスを予約する（消費予定ポイント）。
3. ポイントを消費してサービスを受ける。

という流れを想定したポイント管理システムです。

 - ポイント管理：ポイント加算、ポイント減算
 - クレジット管理（消費予定ポイント）
 - 使用可能ポイント = ポイント　ー　クレジット
 - ポイント・クレジットのログ（pointbook）
 - 簡易カート（ポイントを購入）
 - 決済（pay.jp）※　SSL必須
 - 管理画面：ポイント調整


## payjpのライブラリ
composer でインストール。
https://github.com/payjp/payjp-php

composer.json設置済みなので、
baserCMS/app/Plugin/Point/
ここで、composer install

baserCMS/app/Config/install.php
に以下、payjpのキーを追記
Configure::write('payjp.secret', 'sk_test_000000000000000000000000');
Configure::write('payjp.public', 'pk_test_000000000000000000000000');



## PointUserモデルの説明

### ポイント加算（ポイント購入） PointUser->pointAdd($data)
point 増加
available_point 増加
縛りなし。いくらでも加算できる。
管理画面からマイナスも指定可能。
  
### ポイント減算（サービス消費） PointUser->pointExp($data)
point 減少
credit 減少
ポイント、クレジットはマイナスにならない。
0以上の数字は指定できない。

### クレジット加算（サービス予約）PointUser->creditAdd($data)
credit 増加
available_point 減少
クレジットはポイントを超えてはならない。
使用可能ポイントはマイナスにならない。
予約取消はマイナス指定。

## ポイント・クレジットのログ（pointbook）
マイページに以下のリンクを貼る。
path : /point/point_books/
http://sample.com/point/point_books/



 