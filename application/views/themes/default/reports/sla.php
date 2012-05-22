<?php defined('SYSPATH') OR die('No direct access allowed.');?>
<?php
$nr = 0;
foreach($report_data as $i =>  $report) {
	$nr++;
	$custom_group = explode(',',$report['source']);
	if (!empty($report['data_str'])) {
		if (count($custom_group) > 1)
			$str_source = 'SLA breakdown for Custom group';
		else {
			if (!$use_alias || $report['group_title'] !== false)
				$str_source = _('SLA breakdown for').': '.$report['source'];
			else
				$str_source = _('SLA breakdown for').': '.$this->_get_host_alias($report['source']).' ('.$report['source'].')';
		}

	?>
	<div class="setup-table members">
		<h2 style="margin-top: 20px; margin-bottom: 4px"><?php echo help::render('sla_graph').' '.$str_source; ?></h2>
		<?php
		$avail_links = html_entity_decode($report['avail_links']);
		parse_str(substr($avail_links, strpos($avail_links, '?')+1), $avail_links); ?>
		<form action="<?php echo url::site().Kohana::config('reports.reports_link').'/generate?type=avail' ?>" method="post">
			<input type="image" src="<?php echo url::site() ?>reports/barchart/<?php echo $report['data_str'] ?>" title="<?php echo _('Uptime');?>" />
			<?php foreach($avail_links as $key => $value) {
				if(is_array($value)) {
					foreach($value as $value_part) { ?>
					<input type="hidden" name="<?php echo $key ?>[]" value="<?php echo $value_part ?>" />
					<?php }
				} else { ?>
					<input type="hidden" name="<?php echo $key ?>" value="<?php echo $value ?>" />
				<?php }
			} ?>
		</form>
		<?php } else {
			echo "#chart_placeholder_$nr#";
		} ?>
	</div>
	<div id="slaChart<?php echo $nr ?>"></div>
	<?php  if (!empty($report['table_data'][$report['source']])) {
		$data = $report['table_data'][$report['source']]; ?>
		<div class="sla_table">
		<h2 style="margin: 15px 0px 4px 0px"><?php echo help::render('sla_breakdown').' '.$str_source; ?></h2>
		<table class="auto" border="1">

			<tr>
				<th class="headerNone"</th>
				<?php
					$n = 0;
					foreach ($data as $month => $values) {
					$n++;
				?>
				<th class="headerNone"<?php echo $month ?></th>
				<?php } ?>
			</tr>
			<tr class="even">
				<td class="label"<?php echo _('SLA') ?></td><?php
				$j = 0;
				foreach ($data as $month => $value) {
					$j++; ?>
				<td class="data"<?php echo reports::format_report_value($value[0][1]) ?> %</td>
				<?php
				} ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Real') ?></td><?php
				$y = 0;
				foreach ($data as $month => $value) {
					$y++;?>
				<td class="data"
					<?php echo reports::format_report_value($value[0][0]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(($value[0][0] < $value[0][1]) ? 'down' : 'up').'.png'),
							array(
							'alt' => '',
							'title' => $value[0][0] < $value[0][1] ? _('Below SLA') : _('OK'),
							'style' => 'width: 11px; height: 12px'));
					if (isset($value[0][2]) && $value[0][2] > 0) {
						echo "<br />(" . reports::format_report_value($value[0][2]) ."% in other states)";
					}?></td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<?php } if (isset ($report['member_links']) && count($report['member_links']) > 0) { ?>
	<div class="setup-table members">

		<table style="margin-bottom: 20px;">
			<caption style="margin-top: 15px;"><?php echo help::render('sla_group_members').' '._('Group members');?></caption>
			<tr><th class="headerNone"><?php echo !empty($report['group_title']) ? $report['group_title'] : _('Custom group') ?></th></tr>
			<?php
				$x = 0;
				foreach($report['member_links'] as $member_link) {
					$x++;
					echo "<tr class=\"".($x%2 == 0 ? 'odd' : 'even')."\"><td style=\" border-right: 1px solid #dcdcdc\">".$member_link."</td></tr>\n";
				}
				?>
			</table>
			<br />
		</div>
	<?php } } ?>
