<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>

	<style type="text/css">
		body {
			background-color: #efefef;
			font-family: Arial, Helvetica, sans-serif;
			font-size:12px;
			margin:0;
			padding:0;
		}

		#exception_content {
			width:960px;
			margin:0 auto;
			background-color:#FFF;
			padding:15px;
		}

		fieldset {
			width: 900px;
			margin: 0 auto;
			margin-bottom: 15px;
			background: #FFFFFF;
		}

		legend {
			font-size:24px;
			font-weight:bold;
			color:#0073BC;
		}

		h3 { 
			margin:8px 0;
		}

		.clear {
			float: none !important;
			clear: both;
		}

		div.summary {
			font-size: 16px;
		}

		div.entry > div {
			float: left;
		}

		div.summary div.label {
			width: 10%;
		}

		div.backtrace {
			font-size: 14px;
		}
		
		div.backtrace > div.entry {
			padding: 5px 0;
		}

		div.backtrace > div.entry:nth-child(odd) {
			background-color: #FAFAD2;
		}

		div.backtrace > div.entry:nth-child(even) {
			background-color: #FFDEAD;
		}

		div.backtrace div.expand {
			margin-right: 20px;
			text-align: center;
			font-weight: bold;
			padding-left: 5px;
			cursor: pointer;
		}

		div.backtrace.summary {
			font-size: 14px !important;
		}

		div.backtrace div.code_wrapper {
			background-color: #ACADF7;
			display: none;
			overflow-y: auto;
			width: 100%;
		}

		div.backtrace div.code_wrapper div.line {
			float: left;
			width: 50px;
			padding: 5px 0;
			padding-left: 5px;
		}

		div.backtrace div.code_wrapper div.code {
			float: left;
			padding: 5px 0;
		}

		div.backtrace div.code_wrapper div.selected {
			font-weight: bold;
		}

	</style>


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
</head>
<body>

<fieldset>
	<legend>Arbitrage Exception</legend>
	<div class="error_wrapper">
		<h3>Summary:</h3>
		<div class="summary">

			<!--<div class="entry">
				<div class="label">Scope</div>
				<div class="value"><?//=$ex['scope'];?></div>
				<div class="clear"></div>
			</div>-->

			<div class="entry">
				<div class="label">Error No</div>
				<div class="value"><?=$event->errno;?> <?=(($event->errstr !== "")? "({$event->errstr})" : "")?></div>
				<div class="clear"></div>
			</div>

			<div class="entry">
				<div class="label">Message</div>
				<div class="value"><?=$event->message;?></div>
				<div class="clear"></div>
			</div>

			<div class="entry">
				<div class="label">File</div>
				<div class="value"><?=$event->file;?></div>
				<div class="clear"></div>
			</div>

			<div class="entry">
				<div class="label">Line</div>
				<div class="value"><?=$event->line;?></div>
				<div class="clear"></div>
			</div>

		</div>

		<div style="height: 50px"></div>

		<h3>Backtrace:</h3>
		<div class="backtrace">
			<?
			$idx = 0;
			foreach($event->trace as $entry)
			{?>
				<div class="entry">
					<div class="expand" onclick="return toggleCode.call(this, <?=$idx?>);">[ + ]</div>
					<div class="summary"><span style="color: red; font-weight: bold">&nbsp;</span> <span style="color: #999999; font-weight: bold"><?=$entry['file']?>:<?=$entry['line']?></span></div>
					<div class="clear"></div>
					<div class="code_wrapper" id="code_<?=$idx?>"><?=$entry['code'];?></div>
				</div>
			<?
				$idx++;
			}
			?>
		</div>
	</div>
</fieldset>

</body>
</html>
