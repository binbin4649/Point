
━━━━━━━━━━━━━━━━━━━━━━━━━━
カード決済のお知らせ
━━━━━━━━━━━━━━━━━━━━━━━━━━

いつもご利用いただきありがとうございます。
<?php echo $mailConfig['site_name'] ?>です。

以下の通り決済が完了しました。

会員番号：<?php echo $PointBook['mypage_id']."\n" ?>
日時：<?php echo $PointBook['created']."\n" ?>
決済：<?php echo $PointUser['payjp_brand'].' **** **** **** '.$PointUser['payjp_last4']."\n" ?>
金額：<?php echo number_format($PointBook['charge']).'円'."\n" ?>
決済番号：<?php echo $PointBook['id']."\n" ?>


引き続き、どうぞよろしくお願い申し上げます。


---

マイページログイン
　<?php echo $mailConfig['site_url'] ?>members/mypages/login

お問合せ
　<?php echo $mailConfig['site_url'] ?>contact/

不具合報告
　<?php echo $mailConfig['site_url'] ?>error_report/

---
<?php echo $mailConfig['site_name'] ?>　
<?php echo $mailConfig['site_url'] ?>　
<?php echo $mailConfig['site_email'] ?>　