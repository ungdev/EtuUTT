<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />

		<title>
			<?php
			if ($exceptionParsed->hasMessage()) {
				echo $exceptionParsed->getMessage().' - '.$exceptionParsed->getClass();
			} else {
				echo $exceptionParsed->getClass();
			}
			?>
		</title>

		<style>
			html {
				background: #EEEEEE;
				color: #313131;
				font-family: Arial;
				font-size: 13px;
				padding-top: 30px;
			}
			.exception {
				width: 940px;
				margin: 20px auto;
				padding: 20px 28px;
				background: white;
				border: 1px solid #DFDFDF;
				border-radius: 16px 16px 16px 16px;
				margin-bottom: 20px;
			}
			h2, h4 {
				font-weight: lighter;
			}
			h4 {
				font-size: 14px;
				margin-top: 5px;
				margin-left: 20px;
				margin-bottom: 40px;
			}
			p {
				margin: 7px 0;
				padding: 0;
			}
			pre {
				width: 910px;
				overflow: hidden;
				border: 1px solid #e5e5e5;
				padding: 5px;
			}
			li {
				padding: 5px;
				color: #888;
			}
			.clear {
				clear: both;
			}
			.exception-stacked pre {
				width: 870px;
			}
			.exception-stacked-toggle, .exception-stacked-content {
				float: left;
			}
			.exception-stacked-toggle {
				padding: 20px;
				padding-left: 0;
			}
		</style>

		<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>

		<script type="text/javascript">
			$(function() {
				$('.exception-stacked-hidden').hide();

				$('.exception-stacked-toggle-link').click(function() {
					$('.exception-stacked-hidden-'+ $(this).attr('id')).toggle();
					return false;
				});
			});
		</script>
	</head>
	<body>
		<div class="exception">
			<?php
			if ($exceptionParsed->hasMessage()) {
				echo '<h2>'.$exceptionParsed->getMessage().'</h2><h4>'.$exceptionParsed->getClass().'</h4>';
			} else {
				echo '<h2>'.$exceptionParsed->getClass().'</h2>';
			}
			?>

			<p>
				In <strong><?php echo $exceptionParsed->getFile(); ?></strong>
				on line <strong><?php echo $exceptionParsed->getLine(); ?></strong>
			</p>

			<h3>Trace</h3>

			<ol>
				<?php
				foreach ($exceptionParsed->getStringTraceLines() as $line) :
					?>
					<li><?php echo $line; ?></li>
					<?php
				endforeach;
				?>
			</ol>

			<h3>File content</h3>

				<pre><?php

					foreach ($exceptionParsed->getLinesAround() as $lineNumber => $line) {
						echo str_pad($lineNumber, $exceptionParsed->getLinesAroundBiggestNumberLineSize() + 2, ' ').$line."\n";
					}

					?></pre>
		</div>

		<?php
		foreach ($exceptionParsed->getStack() as $key => $exception) :
		?>
		<div class="exception exception-stacked">
			<div class="exception-stacked-toggle">
				<a href="#" id="<?php echo $key; ?>" class="exception-stacked-toggle-link">
					<img alt="More" title="Display more" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEdJREFUeNpi/P//PwMxgAldgJGRcS8IY4ijmwhUBBYAijPiNREngJoIsuo/DrwXpAZm4i88Zv2ijRtZsIjtwKaQkdgABwgwAE0XJZ+ryjf7AAAAAElFTkSuQmCC" />
				</a>
			</div>

			<div class="exception-stacked-content">
				<?php
				if ($exception->hasMessage()) {
					echo '<h2>'.$exception->getMessage().'</h2><h4>'.$exception->getClass().'</h4>';
				} else {
					echo '<h2>'.$exception->getClass().'</h2>';
				}
				?>

				<p>
					In <strong><?php echo $exception->getFile(); ?></strong>
					on line <strong><?php echo $exception->getLine(); ?></strong>
				</p>

				<div class="exception-stacked-hidden exception-stacked-hidden-<?php echo $key; ?>">
					<h3>Trace</h3>

					<ol>
						<?php
						foreach ($exception->getStringTraceLines() as $line) :
							?>
							<li><?php echo $line; ?></li>
							<?php
						endforeach;
						?>
					</ol>

					<h3>File content</h3>

						<pre><?php

							foreach ($exception->getLinesAround() as $lineNumber => $line) {
								echo str_pad($lineNumber, $exception->getLinesAroundBiggestNumberLineSize() + 2, ' ').$line."\n";
							}

							?></pre>
				</div>
			</div>

			<div class="clear"></div>
		</div>
		<?php
		endforeach;
		?>
	</body>
</html>
