<div id="header">
	<h1 style="margin-top: 0px !important;"><?php echo isset($title) ? $title : $this->translate->_('SLA Breakdown'); ?></h1>
	<p><?php echo $label_report_period.': '.$report_time_formatted; ?>
	<?php echo (isset($str_start_date) && isset($str_end_date)) ? ' ('.$str_start_date.' '.$label_to.' '.$str_end_date.')' : '';
	if ($use_average) echo " <strong>(".$label_using_avg.")</strong>"; ?>
	</p><?php
	if (!$create_pdf) {
		echo html::anchor(
			'#',
			html::image(
				$this->add_path('icons/32x32/square-print.png'),
				array(
					'alt' => $label_print,
					'title' => $label_print,
					'style' => 'position: absolute; top: 16px; right: 0px;',
					'onclick' => 'window.print()'
				)
			)
		);
	}
	echo isset($csv_link) ? $csv_link : '';
	echo isset($pdf_link) ? $pdf_link : '';

?>
</div>