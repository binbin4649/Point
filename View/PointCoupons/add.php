<?php $this->BcBaser->css(array('Members.members'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-4 mb-md-5 text-secondary">クーポンチャージ</h1>
<div class="my-3">
		<p>クーポンコードを入力してボタンを押してください。
		</p>
		<p><small>
			正常に登録されると所定のポイントが付与されます。<br>
			アルファベットは大文字・小文字の区別があります。<br>
			小文字のエル(l)・数字のイチ(1)、大文字のオー(O)・数字のゼロ(0)など、間違いやすい文字によく注意して入力してください。
		</small></p>
		<?php echo $this->BcForm->create('PointCoupon', array('class' => 'form-signin')) ?>
		<p>クーポンコード：
			<?php echo $this->BcForm->input('PointCoupon.code', array('type' => 'text', 'class' => 'form-control', 'placeholder' => 'Coupon Code')) ?>
		</p>
	<div class="submit">
		<?php echo $this->BcForm->submit('クーポンチャージ', array('div' => false, 'class' => 'btn btn-lg btn-primary btn-block mt-4 btn-e')) ?>
	</div>
	<?php echo $this->BcForm->end() ?>
</div>
