<?php defined('SYSPATH') OR die('No direct access allowed.');

class Report_options_core implements ArrayAccess, Iterator {
	protected $hosts = array();
	protected $services = array();
	protected static $rename_options = array(
		't1' => 'start_time',
		't2' => 'end_time',
		'host' => 'host_name',
		'service' => 'service_description',
		'hostgroup_name' => 'hostgroup',
		'servicegroup_name' => 'servicegroup'
	);
	protected $vtypes = array(
		'report_id' => array('type' => 'int', 'default' => false),
		'report_name' => array('type' => 'string', 'default' => false),
		'report_type' => array('type' => 'enum', 'default' => false, 'options' => array(
			'hosts' => 'host_name',
			'services' => 'service_description',
			'hostgroups' => 'hostgroup',
			'servicegroups' => 'servicegroup')),
		'report_period' => array('type' => 'enum', 'default' => false),
		'alert_types' => array('type' => 'enum', 'default' => 3),
		'state_types' => array('type' => 'enum', 'default' => 3),
		'host_states' => array('type' => 'enum', 'default' => 7),
		'service_states' => array('type' => 'enum', 'default' => 15),
		'summary_items' => array('type' => 'int', 'default' => 25),
		'cluster_mode' => array('type' => 'bool', 'default' => false),
		'st_state_calculator' => array('type' => 'string', 'default' => 'st_worst'),
		'keep_logs' => array('type' => 'bool', 'default' => false),
		'keep_sub_logs' => array('type' => 'bool', 'default' => false),
		'rpttimeperiod' => array('type' => 'string', 'default' => false),
		'scheduleddowntimeasuptime' => array('type' => 'enum', 'default' => 0),
		'assumeinitialstates' => array('type' => 'bool', 'default' => -1),
		'initialassumedhoststate' => array('type' => 'enum', 'default' => -1, 'options' => array(
			-1 => 'Current state',
			-2 => 'Unspecified',
			-3 => 'First Real State',
			 0 => 'Host Up',
			 1 => 'Host Down',
			 2 => 'Host Unreachable'
		)),
		'initialassumedservicestate' => array('type' => 'enum', 'default' => -1, 'options' => array(
			-1 => 'Current state',
			-2 => 'Unspecified',
			-3 => 'First Real State',
			 0 => 'Service Ok',
			 1 => 'Service Warning',
			 2 => 'Service Critical',
			 3 => 'Service Unknown'
		)),
		'assumestatesduringnotrunning' => array('type' => 'bool', 'default' => false),
		'includesoftstates' => array('type' => 'bool', 'default' => true),
		'host_name' => array('type' => 'list', 'default' => false),
		'service_description' => array('type' => 'list', 'default' => false),
		'hostgroup' => array('type' => 'array', 'default' => array()),
		'servicegroup' => array('type' => 'array', 'default' => array()),
		'options' => array('type' => 'array', 'default' => false),
		'start_time' => array('type' => 'timestamp', 'default' => 0),
		'end_time' => array('type' => 'timestamp', 'default' => 0),
		'use_average' => array('type' => 'enum', 'default' => 0),
		'host_filter_status' => array('type' => 'array', 'default' => array(
			Reports_Model::HOST_UP => 1,
			Reports_Model::HOST_DOWN => 1,
			Reports_Model::HOST_UNREACHABLE => 1,
			Reports_Model::HOST_PENDING => 1)),
		'service_filter_status' => array('type' => 'array', 'default' => array(
			Reports_Model::SERVICE_OK => 1,
			Reports_Model::SERVICE_WARNING => 1,
			Reports_Model::SERVICE_CRITICAL => 1,
			Reports_Model::SERVICE_UNKNOWN => 1,
			Reports_Model::SERVICE_PENDING => 1)),
		'include_trends' => array('type' => 'bool', 'default' => false),
		'master' => array('type' => 'object', 'default' => false));

	public $options = array();

	public function __construct($options=false) {
		if (isset($this->vtypes['report_period']))
			$this->vtypes['report_period']['options'] = array(
				"today" => _('Today'),
				"last24hours" => _('Last 24 Hours'),
				"yesterday" => _('Yesterday'),
				"thisweek" => _('This Week'),
				"last7days" => _('Last 7 Days'),
				"lastweek" => _('Last Week'),
				"thismonth" => _('This Month'),
				"last31days" => _('Last 31 Days'),
				"lastmonth" => _('Last Month'),
				"thisyear" => _('This Year'),
				"lastyear" => _('Last Year'));
		if (isset($this->vtypes['scheduleddowntimeasuptime']))
			$this->vtypes['scheduleddowntimeasuptime']['options'] = array(
				0 => _('Actual state'),
				1 => _('Uptime'),
				2 => _('Uptime, with difference'));
		if (isset($this->vtypes['use_average']))
			$this->vtypes['use_average']['options'] = array(
				0 => _('Group availability (SLA)'),
				1 => _('Average'));
		if (isset($this->vtypes['alert_types']))
			$this->vtypes['alert_types']['options'] = array(
				3 => _('Host and Service Alerts'),
				1 => _('Host Alerts'),
				2 => _('Service Alerts'));
		if (isset($this->vtypes['state_types']))
			$this->vtypes['state_types']['options'] = array(
				3 => _('Hard and Soft States'),
				2 => _('Hard States'),
				1 => _('Soft States'));
		if (isset($this->vtypes['host_states']))
			$this->vtypes['host_states']['options'] = array(
				7 => _('All Host States'),
				6 => _('Host Problem States'),
				1 => _('Host Up States'),
				2 => _('Host Down States'),
				4 => _('Host Unreachable States'));
		if (isset($this->vtypes['service_states']))
			$this->vtypes['service_states']['options'] = array(
				15 => _('All Service States'),
				14 => _('Service Problem States'),
				1 => _('Service Ok States'),
				2 => _('Service Warning States'),
				4 => _('Service Critical States'),
				8 => _('Service Unknown States'));
		if ($options)
			$this->set_options($options);
	}

	public function offsetGet($str)
	{
		if (!isset($this->vtypes[$str]))
			return false;

		return arr::search($this->options, $str, $this->vtypes[$str]['default']);
	}

	public function offsetSet($key, $val)
	{
		$this->set($key, $val);
	}

	public function offsetExists($key)
	{
		return isset($this->vtypes[$key]);
	}

	public function offsetUnset($key)
	{
		unset($this->options[$key]);
	}

	public function get_alternatives($key) {
		if (!isset($this->vtypes[$key]))
			return false;
		if ($this->vtypes[$key]['type'] !== 'enum')
			return false;
		return $this->vtypes[$key]['options'];
	}

	public function get_value($key) {
		if (!isset($this->options[$key]) || !isset($this->vtypes[$key]))
			return false;
		if ($this->vtypes[$key]['type'] !== 'enum')
			return false;
		if (!isset($this->vtypes[$key]['options'][$this->options[$key]]))
			return $key;
		return $this->vtypes[$key]['options'][$this->options[$key]];
	}

	public function get_report_members() {
		switch ($this['report_type']) {
		 case 'hosts':
		 case 'services':
			return $this[$this->get_value('report_type')];
		 case 'hostgroups':
			$model = new Hostgroup_Model();
			foreach ($this[$this->get_value('report_type')] as $group)
				$this->hosts = $model->member_names($group);
			return $this->hosts;
		 case 'servicegroups':
			$model = new Servicegroup_Model();
			foreach ($this[$this->get_value('report_type')] as $group)
				$this->services = $model->member_names($group);
			return $this->services;
		}
		return false;
	}

	/**
	 * Update the options for the report
	 * @param $options New options
	 */
	public function set_options($options)
	{
		$errors = false;
		foreach ($options as $name => $value) {
			$errors |= intval(!$this->set($name, $value));
		}

		return $errors ? false : true;
	}


	/**
	 * Calculates $this['start_time'] and $this['end_time'] based on an
	 * availability report style period such as "today", "last24hours"
	 * or "lastmonth".
	 *
	 * @param $report_period The textual period to set our options by
	 * @return false on errors, true on success
	 */
	protected function calculate_time($report_period)
	{
		$year_now 	= date('Y', time());
		$month_now 	= date('m', time());
		$day_now	= date('d', time());
		$week_now 	= date('W', time());
		$weekday_now = date('w', time())-1;
		$time_start	= false;
		$time_end	= false;
		$now = time();

		switch ($report_period) {
		 case 'today':
			$time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			$time_end 	= time();
			break;
		 case 'last24hours':
			$time_start = mktime(date('H', time()), date('i', time()), date('s', time()), $month_now, $day_now -1, $year_now);
			$time_end 	= time();
			break;
		 case 'yesterday':
			$time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
			$time_end 	= mktime(0, 0, 0, $month_now, $day_now, $year_now);
			break;
		 case 'thisweek':
			$time_start = strtotime('today - '.$weekday_now.' days');
			$time_end 	= time();
			break;
		 case 'last7days':
			$time_start	= strtotime('now - 7 days');
			$time_end	= time();
			break;
		 case 'lastweek':
			$time_start = strtotime('midnight last monday -7 days');
			$time_end	= strtotime('midnight last monday');
			break;
		 case 'thismonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			$time_end	= time();
			break;
		 case 'last31days':
			$time_start = strtotime('now - 31 days');
			$time_end	= time();
			break;
		 case 'lastmonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'thisyear':
			$time_start = strtotime('midnight '.$year_now.'-01-01');
			$time_end	= time();
			break;
		 case 'lastyear':
			$time_start = strtotime('midnight '.$year_now.'-01-01 -1 year');
			$time_end	= strtotime('midnight '.$year_now.'-01-01');
			break;
		 case 'last12months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -12 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'last3months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -3 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'last6months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -6 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'lastquarter':
			$t = getdate();
			if($t['mon'] <= 3){
				$lqstart = ($t['year']-1)."-10-01";
				$lqend = ($t['year']-1)."-12-31";
			} elseif ($t['mon'] <= 6) {
				$lqstart = $t['year']."-01-01";
				$lqend = $t['year']."-03-31";
			} elseif ($t['mon'] <= 9){
				$lqstart = $t['year']."-04-01";
				$lqend = $t['year']."-06-30";
			} else {
				$lqstart = $t['year']."-07-01";
				$lqend = $t['year']."-09-30";
			}
			$time_start = strtotime($lqstart);
			$time_end = strtotime($lqend);
			break;
		 case 'custom':
			# we'll have "start_time" and "end_time" in
			# the options when this happens
			return true;
		 default:
			# unknown option, ie bogosity
			return false;
		}

		if($time_start > $now)
			$time_start = $now;

		if($time_end > $now)
			$time_end = $now;

		$this->options['start_time'] = $time_start;
		$this->options['end_time'] = $time_end;
		return true;
	}

	/**
	 * Set an option, with some validation
	 *
	 * @param $name Option name
	 * @param $value Option value
	 */
	public function set($name, $value)
	{
		if (isset(self::$rename_options[$name]))
			$name = self::$rename_options[$name];

		if (!$this->validate_value($name, $value)) {
			return false;
		}
		return $this->update_value($name, $value);
	}

	protected function validate_value($key, &$value)
	{
		if (!isset($this->vtypes[$key]))
			return false;
		switch ($this->vtypes[$key]['type']) {
		 case 'bool':
			if ($value == 1 || !strcasecmp($value, "true") || !empty($value))
				$value = true;
			else
				$value = false;
			if (!is_bool($value))
				return false;
			break;
		 case 'int':
			if (!is_numeric($value) || $value != intval($value))
				return false;
			$value = intval($value);
			break;
		 case 'string':
			if (!is_string($value))
				return false;
			break;
		 case 'list':
			if (is_array($value) && count($value) === 1)
				$value = array_pop($value);
			if (is_string($value))
				break;
			/* fallthrough */
		 case 'array':
			if (!is_array($value))
				return false;
			break;
		 case 'timestamp':
			if (!is_numeric($value)) {
				if (strstr($value, '-') === false)
					return false;
				$value = strtotime($value);
				if ($value === false)
					return false;
			}
			break;
		 case 'object':
			if (!is_object($value)) {
				return false;
			}
			break;
		 case 'enum':
			if (!isset($this->vtypes[$key]['options'][$value]))
				return false;
			break;
		 default:
			# this is an exception and should never ever happen
			return false;
		}
		return true;
	}
	
	protected function update_value($name, $value)
	{
		switch ($name) {
		 case 'cluster_mode':
			# check things in 'cluster mode' (ie, consider a group of
			# objects ok if one of the objects is)
			if ($value === true)
				$this->options['st_state_calculator'] = 'st_best';
			else
				$this->options['st_state_calculator'] = 'st_worst';
			break;
		 case 'report_period':
			if (!$this->calculate_time($value))
				return false;
			break;
		 # lots of fallthroughs. lowest must come first
		 case 'state_types': case 'alert_types':
			if ($value > 3)
				return false;
		 case 'host_states':
			if ($value > 7)
				return false;
		 case 'service_states':
			if ($value > 15)
				return false;
		 case 'summary_items':
			if ($value < 0)
				return false;
			break;
		 # fallthrough end

		 case 'assumeinitialstates':
			if (!$value) {
				$this->set('initialassumedhoststate', false);
				$this->set('initialassumedservicestate', false);
			}
			break;
		 case 'initialassumedhoststate': case 'initialassumedservicestate':
			if ($value < -3 || !$this['assumeinitialstates'])
				return false;
			break;
		 case 'host_filter_status':
		 case 'service_filter_status':
			if ($value === null)
				$value = false;
			else if (!is_array($value))
				$value = i18n::unserialize($value);
			break;
		 case 'include_trends':
			if ($value === true) {
				$this->set('keep_logs', true);
				$this->set('keep_sub_logs', true);
			}
			break;
		 case 'host_name':
			if (!$value)
				return;
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$this->options['host_name'] = $value;
			$this->options['report_type'] = isset($this->options['service_description']) && $this->options['service_description'] ? 'services' : 'hosts';
			$this->hosts = array();
			return true;
		 case 'service_description':
			if (!$value)
				return;
			if (!is_array($value))
				$value = array($value);
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$host = arr::search($this->options, 'host_name');
			$new_val = array();
			foreach ($value as $name) {
				if (strpos($name, ';') === false) {
					// no hostname involved here - let's just see if we find
					// a common host among the others and assume it's supposed
					// to be here too
					$new_val[] = $name;
					continue;
				}
				$parts = explode(';', $name);
				if ($host === false) {
					$host = $parts[0];
				}
				else if ($host !== $parts[0]) {
					// different hosts, so bail
					$host = false;
					$new_val = false;
					break;
				}
				$new_val[] = $parts[1];
			}
			if (empty($new_val))
				$this->options['service_description'] = $value;
			else
				$this->options['service_description'] = $new_val;
			if (count($this->options['service_description']) === 1)
				$this->options['service_description'] = array_pop($this->options['service_description']);
			$this->options['host_name'] = $host;
			$this->options['report_type'] = 'services';
			$this->services = array();
			return true;
		 case 'hostgroup':
			if (!$value)
				return;
			$this->options['host_name'] = false;
			$this->options['service_description'] = false;
			$this->options['servicegroup'] = array();
			$this->options['hostgroup'] = $value;
			$this->options['report_type'] = 'hostgroups';
			$this->hosts = array();
			return true;
		 case 'servicegroup':
			if (!$value)
				return;
			$this->options['host_name'] = false;
			$this->options['service_description'] = false;
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = $value;
			$this->options['report_type'] = 'servicegroups';
			$this->services = array();
			return true;
		 case 'start_time':
		 case 'end_time':
			// value "impossible", or value already set by report_period
			if ($value <= 0 || $value === 'undefined' || $this[$name])
				return false;
			if (!is_numeric($value))
				$value = strtotime($value);
			break;
		 case 'filename':
			if (strpos($value, '.pdf') !== false) {
				$this->options['output_format'] = 'pdf';
			}
			if (strpos($value, '.csv') !== false)
				$this->options['output_format'] = 'csv';
			break;
		 default:
			break;
		}
		if (!isset($this->vtypes[$name]))
			return false;
		$this->options[$name] = $value;
		return true;
	}

	/**
	 * Generate a standard HTTP keyval string, suitable for URLs or POST bodies.
	 * @param $anonymous If true, any option on the exact objects in this report
	 *                   will be purged, so it's suitable for linking to sub-reports.
	 *                   If false, all options will be kept, completely describing
	 *                   this exact report.
	 */
	public function as_keyval_string($anonymous=false, $obj_only=false) {
		if ($anonymous) {
			unset($opts['host_name']);
			unset($opts['service_description']);
			unset($opts['hostgroup']);
			unset($opts['servicegroup']);
			unset($opts['report_type']);
		}
		$opts_str = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if (is_array($val)) {
				foreach ($val as $vk => $member) {
					$opts_str .= "&{$key}[$vk]=$member";
				}
				continue;
			}
			$opts_str .= "&$key=$val";
		}
		return substr($opts_str, 1);
	}

	public function as_form($anonymous=false, $obj_only=false) {
		if ($anonymous) {
			$opts = $this;
			unset($opts['host_name']);
			unset($opts['service_description']);
			unset($opts['hostgroup']);
			unset($opts['servicegroup']);
			unset($opts['report_type']);
		}

		$html_options = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if (is_array($val)) {
				foreach ($val as $k => $v)
				$html_options .= form::hidden($key.'['.$k.']', $v);
			}
			else {
				$html_options .= form::hidden($key, $val);
			}
		}
		return $html_options;
	}

	public function as_json() {
		// because the person who wrote the js became sick of all our special cases,
		// it expects the objects to be called 'objects'. Which makes sense, really...
		$opts = $this->options;
		if ($this->get_value('report_type')) {
			$opts['objects'] = $opts[$this->get_value('report_type')];
			unset($opts[$this->get_value('report_type')]);
		}
		return json_encode($opts);
	}

	function rewind() { reset($this->options); }
	function current() { return current($this->options); }
	function key() { return key($this->options); }
	function next() { return next($this->options); }
	function valid() { return array_key_exists(key($this->options), $this->options); }

	protected static function discover_options($type, $input = false)
	{
		# not using $_REQUEST, because that includes weird, scary session vars
		if (!empty($input)) {
			$report_info = $input;
		} else if (!empty($_POST)) {
			$report_info = $_POST;
		} else {
			$report_info = $_GET;
		}

		if (isset($report_info['report_id'])) {
			$saved_report_info = Saved_reports_Model::get_report_info($type, $report_info['report_id']);
			if ($saved_report_info) {
				foreach ($saved_report_info as $key => $sri) {
					if (!isset($report_info->options[$key]) || $report_info->options[$key] === $report_info->vtypes[$key]['default']) {
						$report_info[$key] = $sri;
					}
				}
			}
		}
		return $report_info;
	}

	protected static function create_options_obj($type, $report_info = false) {
		$class = ucfirst($type) . '_options';
		if (!class_exists($class))
			$class = 'Report_options';
		if (isset($report_info['report_id']) && !isset($report_info['objects'])) {
			// empty reports are no reports at all
			// this can happen when a user deletes a report and re-requests the old ID
			unset($report_info['report_id']);
		}
		$options = new $class($report_info);
		if (isset($report_info['report_id'])) {
			# now that report_type is set, ship off objects to the correct var
			$options[$options->get_value('report_type')] = $report_info['objects'];
		}
		return $options;
	}

	public static function setup_options_obj($type, $input = false)
	{
		$report_info = self::discover_options($type, $input);
		$options = self::create_options_obj($type, $report_info);
		return $options;
	}
}
