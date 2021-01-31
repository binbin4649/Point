<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<small>
	<ul>
		<?php if(empty($PointUser['PointUser']['payjp_card_token'])): ?>
			<li>カード情報入力、登録ボタンを押してください。</li>
			<li>登録と同時に初月の決済が行われます。</li>
			<li>毎月決済時にメールでお知らせします。</li>
		<?php else: ?>
			<li>再登録する際は、一旦「解除」してから、再登録お願いします。</li>
		<?php endif; ?>
	</ul>
	</small>
	<?php echo $this->BcForm->create('PointUser', array('url' => 'subscription')) ?>
	<?php echo $this->BcForm->input('charge', array('type'=>'hidden', 'value'=>$AmountList['month'])) ?>
	<div class="row mb-3 mt-sm-5 mt-4">
		<div class="col-4 text-right">
			月額
		</div>
		<div class="col-8">
			<?php echo number_format($AmountList['month']) ?>円
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-4 text-right">
			カード情報入力
		</div>
		<div class="col-8">
			<script
			  type="text/javascript"
			  src="https://checkout.pay.jp/"
			  class="payjp-button"
			  data-key="<?php echo $payjp_public ?>"
			  data-on-created="onCreated"
			  data-text="カード情報入力"
			  data-submit-text="入力"
			  data-partial="true">
			</script>
		</div>
	</div>
	<?php if(!empty($PointUser['PointUser']['payjp_card_token'])): ?>
		<div class="row py-3 mx-2 rounded border">
			<div class="col-4 text-right">
				登録済み
				<?php if($PointUser['PointUser']['auto_charge_status'] == 'success'): ?>
					<span class="badge badge-pill badge-success">有効</span>
				<?php elseif($PointUser['PointUser']['auto_charge_status'] == 'fail'): ?>
					<span class="badge badge-pill badge-danger">失敗</span>
				<?php endif; ?>
			</div>
			<div class="col-8">
				<?php echo $PointUser['PointUser']['payjp_brand'].' **** **** **** '.$PointUser['PointUser']['payjp_last4']; ?><br>
				<span class="text-muted">次回決済日：</span><?php echo $PointUser['PointUser']['exp_date']; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="text-center mt-sm-5 mt-4">
		<?php 
			$path = '/point/point_users/cancell_subscription';
			$attribute = ['class'=>'btn btn-outline-secondary mr-5 btn-e', 'role'=>'button'];
			$message = 'クレジットカードの登録を解除します。よろしいですか？';
			if(empty($PointUser['PointUser']['payjp_card_token'])){
				echo $this->BcBaser->link( '解除', $path, $attribute);
			}else{
				echo $this->BcBaser->link( '解除', $path, $attribute, $message);
			}
		?>
		<?php if(empty($PointUser['PointUser']['payjp_card_token'])): ?>
			<?php echo $this->BcForm->submit('　登録　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
		<?php endif; ?>
	</div>
	<?php echo $this->BcForm->end() ?>
	<div class="row mt-5 mx-1 p-2 bg-light rounded border">
		<div class="col-2 text-center">
			<i class="fab fa-expeditedssl fa-3x logo-gold"></i>
			<div class="text-muted d-none d-sm-block"><small>RSA:SHA-256</small></div>
		</div>
		<div class="col-10">
			<small>
			決済ページはSSL暗号通信により、通信の秘密が守られております。<br>
			また決済はトークン決済を用いることにより、当サイトは完全なカード情報を一切保持いたしません。<br>
			どうぞ安心してご利用くださいませ。
			</small>
		</div>
		
	</div>
</div>
