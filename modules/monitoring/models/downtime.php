<?php

require_once( dirname(__FILE__).'/base/basedowntime.php' );

/**
 * Describes a single object from livestatus
 */
class Downtime_Model extends BaseDowntime_Model {
	/**
	 * A list of column dependencies for custom columns
	 */
	static public $rewrite_columns = array(
		'triggered_by_text' => array('triggered_by')
		);

	/**
	 * Get triggered by object, as a text.
	 */
	public function get_triggered_by_text() {
		// TODO: Don't nest queries... Preformance!!! (Do this in livestatus?)
		$trig_id = $this->get_triggered_by();
		if( !$trig_id ) return 'N/A';
		$trig = DowntimePool_Model::all()->reduce_by('id', $trig_id, '=')->it(array('host.name', 'service.description'), array(), 1, 0)->current();
		if( !$trig ) return 'Unknown';
		$host = $trig->get_host()->get_name();
		$svc = $trig->get_service()->get_description();
		if( $svc ) return $host.';'.$svc;
		return $host;
	}
}