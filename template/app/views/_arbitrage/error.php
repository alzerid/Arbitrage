<script language="JavaScript" type="text/javascript">
	function toggleCode(idx)
	{
		var html = this.innerHTML;
		if(html === "[ + ]")
		{
			this.innerHTML = "[ - ]";
			document.getElementById('code_' + idx).style.display = "block";
		}
		else
		{
			this.innerHTML = "[ + ]";
			document.getElementById('code_' + idx).style.display = "none";
		}
	}
</script>


<fieldset>
	<legend>Arbitrage Exception</legend>

	<?foreach($exceptions as $ex)
	{
	?>
		<div class="error_wrapper">
			<h3>Summary:</h3>
			<div class="summary">

				<div class="entry">
					<div class="label">Scope</div>
					<div class="value"><?=$ex['scope'];?></div>
					<div class="clear"></div>
				</div>

				<div class="entry">
					<div class="label">Message</div>
					<div class="value"><?=$ex['message'];?></div>
					<div class="clear"></div>
				</div>

				<div class="entry">
					<div class="label">File</div>
					<div class="value"><?=$ex['file'];?></div>
					<div class="clear"></div>
				</div>

				<div class="entry">
					<div class="label">Line</div>
					<div class="value"><?=$ex['line'];?></div>
					<div class="clear"></div>
				</div>

			</div>

			<div style="height: 50px"></div>

			<h3>Backtrace:</h3>
			<div class="backtrace">
				<?
				$idx = 0;
				foreach($ex['trace'] as $entry)
				{
					?>
					<div class="entry">
						<div class="expand" onclick="return toggleCode.call(this, <?=$idx?>);">[ + ]</div>
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
		</div>
	<?
	}
	?>
</fieldset>
