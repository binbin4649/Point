<?php //$this->BcBaser->css(array('Members.members'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<table class="table text-nowrap">
		<thead>
			<tr>
				<th class="align-middle text-right">金額選択</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($amountList as $amount => $point): ?>
		<tr>
			<td class="align-middle text-right size320"><?php echo number_format($amount); ?>円（<?php echo number_format($point); ?>ポイント）</td>
			<td><?php echo $this->BcBaser->link( number_format($amount).'円', '/point/point_users/payment/'.$amount, ['class'=>'btn btn-outline-primary btn-e', 'role'=>'button']) ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>