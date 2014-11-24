<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2010
 * Date:		$Date$
 * -----------------------------------------------------------------------
 * @author		$Author$
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev$
 * 
 * $Id$
 */

if ( !defined('EQDKP_INC') ){
	die('Do not access this file directly.');
}

if ( !class_exists( "apa_startpoints" ) ) {
	class apa_startpoints extends apa_type_generic {
		public static $shortcuts = array('apa'=>'auto_point_adjustments');

		protected $ext_options = array(
			'start_date' => array(
				'timepicker' => true,
				'type'		=> 'datepicker',
				'default'	=> 'now',
				'class'		=> 'input'
			),
			'before'	=> array(
				'type'		=> 'radio',
				'default'	=> 0
			),
			'event'	=> array(
				'type'		=> 'dropdown',
				'options'	=> array(),
			),
			'value'		=> array(
				'type'		=> 'text',
				'inptype'	=> 'int',
				'size'		=> 5,
				'default'	=> 0
			),
			'creation'	=> array(
				'type'		=> 'radio',
				'default' 	=> 0,
			),
			'twinks'	=> array(
				'type'		=> 'radio',
				'default' 	=> 0,
			),
		);

		protected $required = array('name', 'event');
		
		private $options_merged = false;

		public function __construct() {
			unset($this->options['pools']);
			unset($this->options['exectime']);
		}
		
		public function get_options() {
			if($this->options_merged) return $this->options;
			$this->options = array_merge($this->options, $this->ext_options);
			$events = $this->pdh->aget('event', 'name', 0, array($this->pdh->get('event', 'id_list')));
			if(!empty($events)) {
				foreach($events as $id => $name) {
					$this->options['event']['options'][$id] = $name;
				}
			}
			$this->options['start_date']['value'] = $this->time->time;
			$this->options_merged = true;
			return $this->options;
		}
		
		public function update_startdkp($apa_id, $last_date) {
			$members = $this->pdh->get('member', 'id_list', array(true, false, true, !$this->apa->get_data('twinks', $apa_id)));
			if(!$last_date) $last_date = $this->apa->get_data('start_date', $apa_id);
			$startdkp_before = ($this->config->get('cron_startdkp_before')) ? $this->config->get('cron_startdkp_before') : array();

			if($this->apa->get_data('before', $apa_id) && !in_array($apa_id, $startdkp_before)) {
				$last_date = -1;
				$startdkp_before[] = $apa_id;
				$this->config->set('cron_startdkp_before', serialize($startdkp_before));
			}

			if($this->apa->get_data('creation', $apa_id)) {
				$dates = $this->pdh->aget('member', 'creation_date', 0, array($members));
			} else {
				$dates = array();
				$mdkpids = $this->apa->get_data('pools', $apa_id);
				foreach($mdkpids as $mdkpid) {
					$cur_dates = $this->pdh->aget('member_dates', 'first_raid', 0, array($members, $mdkpid, !$this->apa->get_data('twinks', $apa_id)));
					foreach($cur_dates as $member_id => $date) {
						if ($date === 0) $date = 1;
						if(empty($dates[$member_id]) || $dates[$member_id] > $date) $dates[$member_id] = $date;
					}
				}
			}

			foreach($dates as $member_id => $date) {
				if(($date && $date < $last_date) || !$date) continue;
				if ($date === 1) $date = $this->time->time;
				$this->pdh->put('adjustment', 'add_adjustment', array($this->apa->get_data('value', $apa_id), $this->apa->get_data('name', $apa_id), $member_id, $this->apa->get_data('event', $apa_id), NULL, $date));
			}

		}
		
		public function pre_save_func($apa_id, $options) {
			$options['pools'] = $this->pdh->get('event', 'multidkppools', array($options['event']));
			$this->timekeeper->add_cron('startpoints', array('active' => true), true);
			$this->timekeeper->run_cron('startpoints', true);
			$cron = $this->timekeeper->list_crons('startpoints');
			$options['exectime'] = date('H', $cron['start_time'])*3600 + date('i', $cron['start_time'])*60;
			return $options;
		}
		
		public function modules_affected($apa_id) { return array(); }
		public function get_cache_date($date, $apa_id) { return; }
		public function get_decay_val($apa_id, $cache_date, $module, $dkp_id, $data) { return; }
		
		public function recalculate($apa_id){
			if($this->apa->get_data('before', $apa_id)) {
				$startdkp_before = ($this->config->get('cron_startdkp_before')) ? $this->config->get('cron_startdkp_before') : array();
				$key = array_search($apa_id, $startdkp_before);
				if ($key !== false) unset($startdkp_before[$key]);
				$this->config->set('cron_startdkp_before', serialize($startdkp_before));
			}
			
			$this->db->prepare("DELETE FROM __adjustments WHERE adjustment_reason=? AND event_id=? ")->execute($this->apa->get_data('name', $apa_id), intval($this->apa->get_data('event', $apa_id)));
			$this->pdh->enqueue_hook('adjustment_update');
		}
	}//end class
}//end if
?>