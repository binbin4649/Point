
━━━━━━━━━━━━━━━━━━━━━━━━━━
カード決済失敗のお知らせ
━━━━━━━━━━━━━━━━━━━━━━━━━━

いつもご利用いただきありがとうございます。
<?php echo $mailConfig['site_email'] ?>です。

<?php echo $Mypage['name'] ?> 様の定期課金を実行しましたが、
カードの有効期限切れ、または一時的な停止などの理由により、クレジットカードの決済に失敗しました。

会員番号：<?php echo $Mypage['id']."\n" ?>
お名前：<?php echo $Mypage['name']."\n" ?>
ご登録カード：<?php echo $PointUser['payjp_brand'].' **** **** **** '.$PointUser['payjp_last4']."\n" ?>


恐れ入りますが、クレジットカードのご利用状況をご確認頂き、
改めてクレジットカードのご登録をお願い申し上げます。

マイページログイン
<?php echo $mailConfig['site_url'] ?>members/mypages/login


再登録（お支払い）が確認できない場合、予約の有無に関わらずモーニングコールは停止します。
予めご了承ください。

---

お問合せ
　<?php echo $mailConfig['site_url'] ?>contact/

不具合報告
　<?php echo $mailConfig['site_url'] ?>error_report/

---
<?php echo $mailConfig['site_name'] ?>　
<?php echo $mailConfig['site_url'] ?>　
<?php echo $mailConfig['site_email'] ?>　