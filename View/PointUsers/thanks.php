<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<p>下記のとおり決済が完了しました。ポイントのご購入ありがとうございます。</p>
	
	<table class="table text-nowrap md-4">
		<thead>
		<tr>
			<th>決済日時</th>
			<th>決済番号</th>
			<th>決済金額</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php echo $book['PointBook']['created']; ?></td>
			<td class="text-right"><?php echo $book['PointBook']['id']; ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['charge']); ?></td>
		</tr>
		</tbody>
	</table>
	
	<ul>
		<li><?php echo $this->BcBaser->link( 'ポイント履歴', '/point/point_books/');?></li>
	</ul>
	<small>ポイント履歴にて、これまでのポイント履歴が一覧できます。</small>
</div>

<?php if(Configure::read('PointPlugin.VC-EC-ID')): ?>
	<?php $ecid = Configure::read('PointPlugin.VC-EC-ID');  ?>
	<?php $siteUrl = Configure::read('BcEnv.siteUrl'); ?>
	<!----- ValueCommerce CV_DIV_F：ここから ----->
	<div data-vc-ec-id="<?php echo $ecid; ?>"
	data-vc-sales-amount="<?php echo $book['PointBook']['charge']; ?>"
	data-vc-order-id="<?php echo $book['PointBook']['id']; ?>"
	data-vc-src="<?php echo $siteUrl; ?>vc/vc_sample-v01.php">
	</div>
	<script type="text/javascript" src="//cv.valuecommerce.com/vccv.min.js"></script>
	<!----- ValueCommerce CV_DIV_F：ここまで ----->
<?php endif; ?>