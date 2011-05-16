<?
$header = $this->_getHeaders();
$data   = $this->_getData();
$name   = $this->_getName();
$jsopts = $this->_getJSOptions();
?>
<table class="list" name="<?=$name?>" id="<?=$name?>">
	<thead>
		<tr colspan="<?=(count($header)+1);?>">
			<?
				foreach($header as $key=>$value)
				{
					$hname = $name . "_head_$key";
					echo "<th name=\"$hname\" id=\"$hname\">$value</th>\n";
				}
			?>
		</tr>
	</thead>

	<tbody>
		<?
		if(isset($this->_options['bulk_actions']) && $this->_options['bulk_actions'])
			echo $this->renderPartial('_bulk_options', array('name' => $name, 'colspan' => count($header)));
		?>
			<?
				if(count($data) == 0)
				{?>
					<tr>
						<td colspan="<?=count($header)?>"><i>No Data</i></td>
					</tr>
				<?
				}
				else
				{
					$ridx = 0;
					foreach($data as $row)
					{
						$rname = $name . "_row_" . $ridx;
						echo "<tr name=\"$rname\" id=\"$rname\">\n";

						//check if we are doing bulk options
						if(isset($this->_options['bulk_actions']) && $this->_options['bulk_actions'])
						{
							$id = ((isset($this->_options['id']))? $this->_options['id'] : '_id');
							echo "<td>" . HTMLComponent::inputCheckbox("muli_select", array('value' => $row['id'])) .  "</td>\n";
						}

						foreach($row as $key => $cell)
						{
							if(is_numeric($key))
								echo "<td>$cell</td>\n";
						}

						echo "</tr>\n";
						$ridx++;
					}
				}
			?>
	</tbody>
</table>

<script language="JavaScript">
	$(document).ready(function() {
		$('#<?=$name?>').datatable(<?=$jsopts?>);
	});
</script>
