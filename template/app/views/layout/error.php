<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Arbitrage Exception</title>

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
			margin-bottom:15px;
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

</head>
<body>
	<div id="exception_content">
		<?=$content?>
	</div>
</body>
</html>
