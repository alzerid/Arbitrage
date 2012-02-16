<script language="JavaScript" type="text/javascript">
	function toggleCode(idx)
	{
		var html = this.innerHTML;
		console.debug(this.innerHTML);
		if(html === "[ - ]")
		{
			this.innerHTML = "[ + ]";
			document.getElementById('code_' + idx).style.display = "block";
		}
		else
		{
			this.innerHTML = "[ - ]";
			document.getElementById('code_' + idx).style.display = "none";
		}
	}

</script>


<fieldset>
	<legend>Arbitrage Exception</legend>

	<h3>Summary:</h3>
	<div class="summary">

		<div class="entry">
			<div class="label">Scope</div>
			<div class="value"><?=$scope;?></div>
			<div class="clear"></div>
		</div>

		<div class="entry">
			<div class="label">Message</div>
			<div class="value"><?=$message;?></div>
			<div class="clear"></div>
		</div>

		<div class="entry">
			<div class="label">File</div>
			<div class="value"><?=$file;?></div>
			<div class="clear"></div>
		</div>

		<div class="entry">
			<div class="label">Line</div>
			<div class="value"><?=$line;?></div>
			<div class="clear"></div>
		</div>

	</div>

	<div style="height: 50px"></div>

	<h3>Backtrace:</h3>
	<div class="backtrace">
		<?
		$idx = 0;
		foreach($trace as $entry)
		{
			?>
			<div class="entry">
				<div class="expand" onclick="return toggleCode.call(this, <?=$idx?>);">[ - ]</div>
				<div class="summary"><span style="color: red; font-weight: bold"><?=$entry['summary']?></span> <span style="color: #999999; font-weight: bold">(<?=$entry['file']?>:<?=$entry['line']?>)</span></div>
				<div class="clear"></div>
				<div class="code_wrapper" id="code_<?=$idx?>">
					<?
					foreach($entry['code'] as $c)
						echo '<div class="line ' . (($c['selected'])? 'selected' : '') . '">' . $c['line'] . '</div><div class="code ' . (($c['selected'])? 'selected' : '') . '">' . $c['code'] . '</div><div class="clear"></div>';
					?>
				</div>
			</div>
		<?
			$idx++;
		}
		?>
	</div>
</fieldset>
