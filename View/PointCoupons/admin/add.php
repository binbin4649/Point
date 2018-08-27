<!-- form -->
<?php echo $this->BcForm->create('PointCoupon', ['url'=>'add']) ?>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table">
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.name', 'イベント名') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointCoupon.name', array('type' => 'text', 'size' => '50')) ?>
				<?php echo $this->BcForm->error('PointCoupon.name') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.division', '識別子') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointCoupon.division', array('type' => 'text')) ?>
				<?php echo $this->BcForm->error('PointCoupon.division') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.start', '開始日') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->datepicker('PointCoupon.start') ?>
				<?php echo $this->BcForm->error('PointCoupon.start') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.finish', '終了日') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->datepicker('PointCoupon.finish') ?>
				<?php echo $this->BcForm->error('PointCoupon.finish') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.point', '付与ポイント') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointCoupon.point', array('type' => 'text')) ?>
				<?php echo $this->BcForm->error('PointCoupon.point') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.use_plan', '使用回数') ?></th>
			<td class="col-input">
				<?php $option = ['once'=>'一回限り(once)', 'unlimited'=>'一人一回限りで無制限(unlimited)']; ?>
				<?php echo $this->BcForm->input('PointCoupon.use_plan', array('type' => 'radio', 'options' => $option)) ?>
				<?php echo $this->BcForm->error('PointCoupon.use_plan') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointCoupon.generated', '生成枚数') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointCoupon.generated', array('type' => 'text')) ?>
				<?php echo $this->BcForm->error('PointCoupon.generated') ?>
			</td>
		</tr>
	</table>
</div>
<!-- button -->
<div class="submit">
<?php echo $this->BcForm->submit('生成', array('div' => false, 'class' => 'button')) ?>
</div>
<?php echo $this->BcForm->end() ?>
<div class="section">
	<ul>
		<li>イベント名：日本語で。後から分かりやすくするために付けるただの名称。</li>
		<li>識別子：ユニークID。unlimitedの場合はそのままクーポンコードに。</li>
		<li>開始日：後から分かりやすくするため日付。制御には使われない。実際開始日前からクーポンは使用できる。</li>
		<li>終了日：終了日の翌日からクーポンは使用不可になる。</li>
		<li>付与ポイント：使用された時にユーザーに付与されるポイント。</li>
		<li>使用回数：once：クーポンコードは自動生成。</li>
		<li>生成枚数：onceクーポンの発行枚数。unlimitedの場合は無効、1枚固定。</li>
	</ul>
</div>