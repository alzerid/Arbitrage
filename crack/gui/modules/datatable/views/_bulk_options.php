<!-- Bulk operations -->
<tr name="<?=$name?>_bulk_options" id="<?=$name?>_bulk_options">
	<td colspan="<?=$colspan?>" style="text-align: left; border-bottom: 1px solid black; background-color: #E9E9E9">
		<div style="width: 49%; display: block-inline; float: left;">
			Select:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=HTMLComponent::generateLink('All', '#', array('onclick' => '$(\'table input:checkbox\').attr(\'checked\', \'checked\')'));?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=HTMLComponent::generateLink('None', '#', array('onclick' => '$(\'table input:checkbox\').removeAttr(\'checked\')'))?>
		</div>
		<div style="width: 49%; display: block-inline; text-align: right; float: right;">
			Bulk Actions:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?=HTMLComponent::generateLink('Delete', '#', array('onclick' => 'return controller.bulkDelete();'))?>
		</div>
	</td>
</tr>
<!-- End Bulk operations -->
