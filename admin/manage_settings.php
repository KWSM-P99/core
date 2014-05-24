<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2006
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

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../';
include_once($eqdkp_root_path . 'common.php');

class mmocms_settings extends page_generic {
	public static $shortcuts = array(
			'itt' => 'infotooltip',
			'social' => 'socialplugins',
			'form'	=> array('form', array('core_settings'))
		);

	public function __construct(){
		$this->user->check_auth('a_config_man');

		$handler = array(
			'ajax'	=> array(
				array('process' => 'ajax_gamelanguage',	'value' => 'games'),
			),
			'dellogo' => array('process' => 'delete_logo'),
		);
		parent::__construct(false, $handler, array(), null, '');
		$this->process();
	}
	
	public function delete_logo(){
		$this->pfh->Delete( $this->pfh->FolderPath('','files').$this->config->get('custom_logo'));
		$this->config->set("custom_logo", "");
	}

	public function ajax_gamelanguage() {
		$options = array(
			'options_only'	=> true,
			'tolang'		=> true,
			'no_key'		=> true,
			'format'		=> 'ucfirst',
			'options' 		=> sdir($this->root_path . 'games/'.$this->in->get('requestid').'/language/', '*.php', '.php'),
			'value'			=> $this->config->get('game_language'),
		);
		echo new hdropdown('dummy', $options);
		exit;
	}

	public function display(){
		// Build the default game array
		$games = array();
		foreach($this->game->get_games() as $sgame){
			$games[$sgame]		= $this->game->game_name($sgame);
		}

		// ---------------------------------------------------------
		// Build the Dropdown Arrays
		// ---------------------------------------------------------

		$a_startday = array(
			'sunday'	=> $this->user->lang(array('time_daynames', 6)),
			'monday'	=> $this->user->lang(array('time_daynames', 0)),
		);

		$a_calraid_status = array(
			0	=> $this->user->lang(array('raidevent_raid_status', 0)),
			1	=> $this->user->lang(array('raidevent_raid_status', 1)),
			2	=> $this->user->lang(array('raidevent_raid_status', 2)),
			3	=> $this->user->lang(array('raidevent_raid_status', 3)),
			4	=> $this->user->lang(array('raidevent_raid_status', 4))
		);

		$a_calraid_status2 = array(
			0	=> $this->user->lang(array('raidevent_raid_status', 0)),
			1	=> $this->user->lang(array('raidevent_raid_status', 1)),
		);

		$a_calraid_nsfilter = array(
			'twinks'	=> 'raidevent_raid_nsf_twink',
			'inactive'	=> 'raidevent_raid_nsf_inctv',
			'hidden'	=> 'raidevent_raid_nsf_hiddn',
			'special'	=> 'raidevent_raid_nsf_special',
		);

		$a_debug_mode = array(
			'0'			=> 'core_sett_f_debug_type0',
			'1'			=> 'core_sett_f_debug_type1',
			'2'			=> 'core_sett_f_debug_type2',
			'3'			=> 'core_sett_f_debug_type3',
			//'4'			=> 'pk_set_debug_type4',
		);

		$a_modelviewer = array(
			'0'			=> 'WoWHead',
			'1'			=> 'Thottbot',
			'2'			=> 'SpeedyDragon'
		);

		$accact_array = array(
			'0'			=> 'none',
			'1'			=> 'user',
			'2'			=> 'admin',
		);

		$portal_positions = array(
			'right'		=> 'portalplugin_right',
			'middle'	=> 'portalplugin_middle',
			'bottom'	=> 'portalplugin_bottom',
		);

		$mail_array = array(
			'mail'		=> 'lib_email_mail',
			'sendmail'	=> 'lib_email_sendmail',
			'smtp'		=> 'lib_email_smtp',
		);

		$smtp_connection_methods = array(
			''	=> 'none',
			'ssl'	=> 'SSL/TLS',
			'tls'	=> 'STARTTLS'
		);

		$a_calendar_addevmode = array(
			'event'		=> 'calendar_mode_event',
			'raid'		=> 'calendar_mode_raid'
		);
		
		$mobile_template_array = array("" => $this->user->lang('default_setting'));
		foreach($this->pdh->get('styles', 'styles', array(0, false)) as $styleid=>$row){
			$mobile_template_array[$styleid] = $row['style_name'];
		}
		
		$mobile_portallayout_array = array("" => $this->user->lang('default_setting'));
		$mobile_portallayout_array = array_merge($mobile_portallayout_array, $this->pdh->aget('portal_layouts', 'name', 0, array($this->pdh->get('portal_layouts', 'id_list'))));
		
		$mobile_pagelayout_array = array("" => $this->user->lang('default_setting'));
		foreach($this->pdh->get_layout_list() as $key => $val){
			$mobile_pagelayout_array[$val] = $val;
		}

		$a_groups = $this->pdh->aget('user_groups', 'name', 0, array($this->pdh->get('user_groups', 'id_list')));

		// Startpage
		$arrMenuItems = $this->core->build_menu_array(true, true);

		foreach($arrMenuItems as $page){
			$link = $this->user->removeSIDfromString($page['link']);
			if ($link != "" && $link != "#" && $link != "index.php"){
				if (isset($page['category'])){
					$strAlias = $this->pdh->get('article_categories', 'alias', array($page['id']));
					if(!isset($startpage_array[$strAlias])) $startpage_array[$this->pdh->get('article_categories', 'alias', array($page['id']))] = $this->pdh->get('article_categories', 'name_prefix', array($page['id'])).$this->pdh->get('article_categories', 'name', array($page['id']));
				} elseif(isset($page['article'])){
					$catid = $this->pdh->get('articles', 'category', array($page['id']));
					$startpage_array[$this->pdh->get('articles', 'alias', array($page['id']))] = $this->pdh->get('article_categories', 'name_prefix', array($catid)).' -> '.$this->pdh->get('articles', 'title', array($page['id']));
				} elseif(!isset($page['pluslink'])) {
					$startpage_array[$link] = $page['text'].' ('.$link.')';
				}
			}
		}

		// Build language array
		if($dir = @opendir($this->core->root_path . 'language/')){
			while ( $file = @readdir($dir) ){
				if ((!is_file($this->core->root_path . 'language/' . $file)) && (!is_link($this->core->root_path . 'language/' . $file)) && valid_folder($file)){
					include($this->core->root_path.'language/'.$file.'/lang_main.php');
					$lang_name_tp = (($lang['ISO_LANG_NAME']) ? $lang['ISO_LANG_NAME'].' ('.$lang['ISO_LANG_SHORT'].')' : ucfirst($file));
					$language_array[$file]					= $lang_name_tp;
					$locale_array[$lang['ISO_LANG_SHORT']]	= $lang_name_tp;
				}
			}
		}

		//Social Plugins
		$arrSocialPlugins = $this->social->getSocialPlugins();
		$arrSocialFields = array();
		foreach ($arrSocialPlugins as $key){
			$arrSocialFields['sp_'.$key] = array(
				'type' => 'radio',
			);
		}
		$arrSocialButtons = $this->social->getSocialButtons();
		foreach ($arrSocialButtons as $key){
			$arrSocialFields['sp_'.$key] = array(
				'type' => 'radio',
			);
		}

		// ---------------------------------------------------------
		// Member Array
		// ---------------------------------------------------------
		$members = $this->pdh->aget('member', 'name', 0, array($this->pdh->get('member', 'id_list', array(false, false, false))));
		asort($members);


		// ---------------------------------------------------------
		// Default email signature
		// ---------------------------------------------------------
		$signature  = "--\n";
		$signature .= $this->user->lang('lib_signature_defaultval');
		$signature .= ' '.$this->config->get('guildtag');
		$signature .= "\nEQdkp Plus: ";
		$signature .= $this->env->link;

		// Bit of jQuery..
		if($this->game->get_importAuth('a_members_man', 'char_mupdate')){
			$this->jquery->Dialog('MassUpdateChars', $this->user->lang('uc_import_adm_update'), array('url'=>$this->game->get_importers('char_mupdate', true), 'width'=>'600', 'height'=>'450', 'onclose'=>$this->env->link.'admin/manage_settings.php'));
		}
		if($this->game->get_importAuth('a_members_man', 'guild_import')){
			$this->jquery->Dialog('GuildImport', $this->user->lang('uc_import_guild_wh'), array('url'=>$this->game->get_importers('guild_import', true), 'width'=>'600', 'height'=>'450', 'onclose'=>$this->env->link.'admin/manage_settings.php'));
		}
		if(($this->game->get_importAuth('a_members_man', 'char_mupdate') || $this->game->get_importAuth('a_members_man', 'guild_import')) && $this->game->get_importers('import_data_cache')){
			$this->jquery->Dialog('ClearImportCache', $this->user->lang('uc_importer_cache'), array('url'=>$this->game->get_importers('import_reseturl', true), 'width'=>'400', 'height'=>'250', 'onclose'=>$this->env->link.'admin/manage_settings.php'));
		}

		// ---------------------------------------------------------
		// Output to the page
		// ---------------------------------------------------------
		$this->jquery->Dialog('template_preview', $this->user->lang('template_preview'), array('url'=>$this->root_path."viewnews.php".$this->SID."&amp;style='+ $(\"select[name='user_style'] option:selected\").val()+'", 'width'=>'750', 'height'=>'520', 'modal'=>true));
		
		// initialize form class
		$this->form->lang_prefix = 'core_sett_';
		$this->form->use_tabs = true;
		$this->form->use_fieldsets = true;
		
		// define standard data for settings
		$settingsdata = array(
			'global' => array(
				'global' => array(
					'main_title'	=> array(
						'type'		=> 'text',
						'size'		=> 40
					),
					'sub_title'		=> array(
						'type'		=> 'text',
						'size'		=> 40
					),
					'disable_points'=> array(
						'type'		=> 'radio',
					),
					'dkp_name'		=> array(
						'type'		=> 'text',
						'size'		=> 5,				
					),
					'color_items'	=> array(
						'type'		=> 'slider',
						'label'		=> $this->user->lang('core_sett_f_color_items'),
						'min'		=> 0,
						'max'		=> 100,
						'width'		=> '300px'
					),
					'enable_comments'	=> array(
						'type'		=> 'radio',
					),
					'round_activate'	=> array(
						'type'		=> 'radio',
						'default'	=> 0,
						'dependency' => array(1=>array('round_precision')),
					),
					'round_precision'	=> array(
						'type'		=> 'text',
						'size'		=> 2,
						'default'	=> 0
					),
					'debug'	=> array(
						'type'		=> 'dropdown',
						'tolang'	=> true,
						'options'	=> $a_debug_mode,
					)
				),
				'meta'	=> array(
					'meta_keywords' => array(
						'type'		=> 'text',
						'size'		=> 40
					),
					'meta_description' => array(
						'type'		=> 'text',
						'size'		=> 40
					),
				),
				'disclaimer'	=> array(
					'disclaimer_show'	=> array(
						'type'			=> 'radio',
						'dependency'	=> array(1 => array('disclaimer_name', 'disclaimer_address', 'disclaimer_email', 'disclaimer_irc', 'disclaimer_messenger', 'disclaimer_custom'))
					),
					'disclaimer_name'	=> array(
						'type'			=> 'text',
						'size'			=> 40,
					),
					'disclaimer_address'	=> array(
						'type'			=> 'textarea',
						'cols'			=> 50,
						'rows'			=> 4,
					),
					'disclaimer_email'	=> array(
						'type'			=> 'text',
						'size'			=> 40
					),
					'disclaimer_irc'	=> array(
						'type'			=> 'text',
						'size'			=> 40,
					),
					'disclaimer_messenger'	=> array(
						'type'			=> 'text',
						'size'			=> 40,
					),
					'disclaimer_custom'	=> array(
						'type'			=> 'text',
						'size'			=> 50,
					)
				)
			),
			'system'	=> array(
				'globalsettings'	=> array(
					'default_locale'	=> array(
						'type'			=> 'dropdown',
						'options'		=> $locale_array,
					),
					'server_path'	=> array(
						'type'		=> 'text',
						'size'		=> 50,
					),
					'enable_gzip'	=> array(
						'type'		=> 'radio',
						'default'	=> 0
					),
				),
				'auth'			=> array(
					'auth_method'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $this->user->get_available_authmethods(),
						'default'	=> 'db',
						
					),
				),
				'login'			=> array(
					'login_method'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $this->user->get_available_loginmethods(),
						'tolang'	=> true,
					),
				),
				'cookie'			=> array(
					'cookie_domain'	=> array(
						'type'		=> 'text',
						'size'		=> 25,
					),
					'cookie_name'	=> array(
						'type'		=> 'text',
						'size'		=> 25,
					),
					'cookie_path'	=> array(
						'type'		=> 'text',
						'size'		=> 25,
					),
				),
				'email'				=> array(
					'lib_email_method'	=> array(
						'type'			=> 'dropdown',
						'tolang'		=> true,
						'options'		=> $mail_array,
						'dependency'	=> array(
							'sendmail' => array('lib_email_sendmail_path'), 
							'smtp' => array('lib_email_smtp_host', 'lib_email_smtp_port', 'lib_email_smtp_connmethod', 'lib_email_smtp_auth', 'lib_email_smtp_user', 'lib_email_smtp_pw')
						)
					),
					'admin_email'	=> array(
						'type'		=> 'text',
						'size'		=> 30,
						'encrypt'	=> true,
					),
					'lib_email_sender_name'	=> array(
						'type'		=> 'text',
						'size'		=> 30
					),
					'lib_email_sendmail_path'	=> array(
						'type'			=> 'text',
						'size'			=> 30,
					),
					'lib_email_smtp_host'	=> array(
						'type'			=> 'text',
						'size'			=> 30,
					),
					'lib_email_smtp_port'	=> array(
						'type'			=> 'text',
						'size'			=> 5,
						'default'		=> 25,
					),
					'lib_email_smtp_connmethod'	=> array(
						'type'			=> 'dropdown',
						'options'		=> $smtp_connection_methods,
					),
					'lib_email_smtp_auth'	=> array(
						'type'			=> 'radio',
					),
					'lib_email_smtp_user'	=> array(
						'type'			=> 'text',
						'size'			=> 30,
					),
					'lib_email_smtp_pw'	=> array(
						'type'			=> 'password',
						'size'			=> 30,
					),
					'lib_email_signature'	=> array(
						'type'			=> 'radio',
						'dependency'	=> array(1 => array('lib_email_signature_value')),
					),
					'lib_email_signature_value'	=> array(
						'type'			=> 'textarea',
						'default'		=> $signature,
						'cols'			=> 80,
						'rows'			=> 5,
					),
				),
				'recaptcha'		=> array(
					'lib_recaptcha_okey'	=> array(
						'type'		=> 'text',
						'size'		=> 30
					),
					'lib_recaptcha_pkey'	=> array(
						'type'		=> 'text',
						'size'		=> 30
					)
				),
				'date'		=> array(
					'timezone'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $this->time->timezones,
					),
					'date_startday'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $a_startday,
					),
					'default_date_time'	=> array(
						'type'		=> 'text',
						'size'		=> 10,
						'default'	=> $this->user->lang('style_time')
					),
					'default_date_short'	=> array(
						'type'		=> 'text',
						'size'		=> 20,
						'default'	=> $this->user->lang('style_date_short')
					),
					'default_date_long'	=> array(
						'type'		=> 'text',
						'size'		=> 20,
						'default'	=> $this->user->lang('style_date_long')
					),
					'default_jsdate_time'	=> array(
						'type'		=> 'text',
						'size'		=> 20,
						'default'	=> $this->user->lang('style_jstime')
					),
					'default_jsdate_nrml'	=> array(
						'type'		=> 'text',
						'size'		=> 20,
						'default'	=> $this->user->lang('style_jsdate_nrml')
					),
					'default_jsdate_short'	=> array(
						'type'		=> 'text',
						'size'		=> 20,
						'default'	=> $this->user->lang('style_jsdate_short')
					)
				)
			),
			'user'	=> array(
				'user'	=> array(
					'default_lang'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $language_array,
					),
					'account_activation'	=> array(
						'type'		=> 'radio',
						'tolang'	=> true,
						'options'	=> $accact_array,
						'default'	=> 0
					),
					'failed_logins_inactivity'	=> array(
						'type'		=> 'text',
						'size'		=> 5,
						'default'	=> 5,
					),
					'disable_registration'	=> array(
						'type'		=> 'radio',
					),
					'enable_captcha'	=> array(
						'type'		=> 'radio',
					),
					'disable_username_change'	=> array(
						'type'		=> 'radio',
					),
					'default_style_overwrite'	=> array(
						'type'		=> 'radio',
					),
					'special_user'	=> array(
						'type'			=> 'multiselect',
						'options'		=> $this->pdh->aget('user', 'name', 0, array($this->pdh->get('user', 'id_list'))),
						'datatype'		=> 'int'
					),
				)
			),
			'chars'		=> array(
				'chars'		=> array(
					'class_color'	=> array(
						'type'		=> 'radio',
					),
					'special_members'	=> array(
						'type'		=> 'multiselect',
						'options'		=> $members,
					),
					'show_twinks'	=> array(
						'type'		=> 'radio'
					),
					'detail_twink'	=> array(
						'type'		=> 'radio'
					),
					'hide_inactive'	=> array(
						'type'		=> 'radio'
					),
					'inactive_period'	=> array(
						'type'		=> 'text',
						'size'		=> 5,
						'default'	=> 0
					)
				)
			),
			'calendar'	=> array(
				'calendar'	=> array(
					'calendar_addevent_mode'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $a_calendar_addevmode,
						'tolang'	=> true
					),
					'calendar_show_birthday'	=> array(
						'type'		=> 'radio',
					)
				),
				'raids'		=> array(
					'calendar_raid_guests'	=> array(
						'type'	=> 'radio',
					),
					'calendar_raid_random'	=> array(
						'type'	=> 'radio',
					),
					'calendar_raid_classbreak'	=> array(
						'type'	=> 'spinner',
						'size'	=> 4,
					),
					'calendar_raid_status'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $a_calraid_status,
						'datatype'	=> 'int'
					),
					'calendar_raid_nsfilter'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $a_calraid_nsfilter,
						'tolang'	=> true
					),
					'calendar_addraid_deadline'	=> array(
						'type'		=> 'spinner',
						'size'		=> 5,
						'default'	=> 1
					),
					'calendar_addraid_duration'	=> array(
						'type'		=> 'spinner',
						'size'		=> 5,
						'default'	=> 120
					),
					'calendar_addraid_use_def_start'	=> array(
						'type'			=> 'radio',
						'dependency'	=> array(1 => array('calendar_addraid_def_starttime')),
					),
					'calendar_addraid_def_starttime'	=> array(
						'type'		=> 'timepicker',
						'default'	=> '20:00'
					),
					'calendar_repeat_crondays'	=> array(
						'type'		=> 'spinner',
						'size'		=> 5,
						'default'	=> 40
					),
					'calendar_raid_autoconfirm'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $a_groups,
						'datatype'	=> 'int',
					),
					'calendar_raid_autocaddchars'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $a_groups,
						'datatype'	=> 'int',
					),
					'calendar_raid_shownotes'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $a_groups,
						'datatype'	=> 'int',
					),
					'calendar_raid_notsigned_classsort'	=> array(
						'type'		=> 'radio',
					),
					'calendar_raid_coloredclassnames'	=> array(
						'type'		=> 'radio',
					),
					'calendar_raid_shownotsigned'	=> array(
						'type'		=> 'radio',
					),
					'calendar_raid_allowstatuschange'	=> array(
						'type'		=> 'radio',
					),
					'calendar_raid_statuschange_status'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $a_calraid_status2,
						'datatype'	=> 'int'
					),
				),
				'calendar_mails'	=> array(
					'calendar_email_statuschange'	=> array(
						'type'		=> 'radio',
					),
					'calendar_email_newraid'	=> array(
						'type'		=> 'radio',
					),
					'calendar_email_openclose'	=> array(
						'type'		=> 'radio',
					),
				)
			),
			'game'		=> array(
				'game'	=> array(
					'default_game'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $games,
						'ajax_reload' => array('game_language', 'manage_settings.php'.$this->SID.'&ajax=games'),
					),
					'game_language'	=> array(
						'type'		=> 'dropdown',
						'options'	=> array('--------'),
					),
					'guildtag'		=> array(
						'type'		=> 'text',
						'size'			=> 35
					),
				)
			),
			'portal'	=> array(
				'portal'	=> array(
					'start_page'	=> array(
						'type'		=> 'dropdown',
						'options'	=> $startpage_array,
					),
					'disable_xframe_header' => array(
						'type'	=> 'radio',
					)	
				),
				'mobile' => array(
					'mobile_template' => array(
						'type'		=> 'dropdown',
						'options'	=> $mobile_template_array,
					),
					'mobile_portallayout' => array(
						'type'		=> 'dropdown',
						'options'	=> $mobile_portallayout_array,
					),
					'mobile_pagelayout' => array(
						'type'		=> 'dropdown',
						'options'	=> $mobile_pagelayout_array,
					),
				),
				'article'		=> array(
					'disable_embedly'	=> array(
						'type'	=> 'radio',
					),
					'thumbnail_defaultsize' => array(
						'type'		=> 'spinner',
						'size'		=> 3,
						'default'	=> 500,
						'step'		=> 25
					),
				),
				'seo'	=> array(
					'seo_remove_index'	=> array(
						'type'		=> 'radio',
					),
					'seo_extension'	=> array(
						'type'		=> 'dropdown',
						'options'	=> array("/", ".html", ".php"),
					),
				),
				'social_sharing' => $arrSocialFields,
			),
			'layout'	=> array(
				'layout'	=> array(
					'custom_logo'	=> array(
						'type'			=> 'imageuploader',
						'imgpath'		=> $this->pfh->FolderPath('','files'),
						//'noimgfile'		=> "templates/".$this->user->style['template_path']."/images/logo.png",
						'returnFormat'	=> 'in_data',
						'deletelink'	=> $this->root_path.'admin/manage_settings.php'.$this->SID.'&dellogo=true',
					),
					'itemhistory_dia'	=> array(
						'type'		=> 'radio',
					),
				),
				'default'	=> array(
					'default_alimit'	=> array(
						'type'		=> 'spinner',
					),
					'default_elimit'	=> array(
						'type'		=> 'spinner',
					),
					'default_ilimit'	=> array(
						'type'		=> 'spinner',
					),
					'default_nlimit'	=> array(
						'type'		=> 'spinner',
					),
					'default_rlimit'	=> array(
						'type'		=> 'spinner',
					),
				)
			),
			'itemtooltip'	=> array(),	// placeholder for sorting..
			'sms'		=> array(
				'sms'	=> array(
					'sms_enable'	=> array(
						'type'			=> 'radio',
						'dependency'	=> array(1 => array('sms_username', 'sms_password'))
					),
					'sms_username'	=> array(
						'type'		=> 'text',
						'size'		=> 40,
					),
					'sms_password'	=> array(
						'type'		=> 'password',
						'set_value'	=> true,
						'size'		=> 40,
					)
				)
			),
		);
		$this->form->add_tabs($settingsdata);

		// add some additional fields
		// ItemTooltip Inject
		if(count($this->itt->get_parserlist())){
			$fields = array(
				'infotooltip_use'	=> array(
					'type'		=> 'radio',
				),
				'itt_debug'	=> array(
					'type'		=> 'radio',
				),
				'itt_trash'	=> array(
					'type'		=> 'direct',
					'text'		=> '<input type="submit" name="itt_reset" value="'.$this->user->lang('itt_reset').'" class="mainoption bi_reset" />',
				),
			);
			$this->form->add_fields($fields, 'itemtooltip' ,'itemtooltip');

			$itt_parserlist	= $this->itt->get_parserlist();

			$fields	= array(
				'itt_prio1'	=> array(
					'type'		=> 'dropdown',
					'name'			=> 'itt_prio1',
					'options'		=> $itt_parserlist
				),
				'itt_prio2'	=> array(
					'type'		=> 'dropdown',
					'name'			=> 'itt_prio2',
					'options'		=> $itt_parserlist
				),
			);
			$this->form->add_fields($fields, 'priorities' ,'itemtooltip');

			// TODO: rework this, it overwrites settings of previous parsers (in ittsettdata), mb use ajax-dd
			$ittsettdata = $this->itt->get_extra_settings();
			if(is_array($ittsettdata)){
				$this->form->add_fields($ittsettdata, 'ittdbsettings', 'itemtooltip');
				//add button to reload defaults
				$this->form->add_field('itt_force_default', array(
					'type'		=> 'direct',
					'text'		=> '<input type="submit" name="itt_force_default" value="'.$this->user->lang('core_sett_f_itt_force_default').'" class="mainoption bi_reset" />',
				), 'ittdbsettings', 'itemtooltip');
			}

			$itt_langlist	= $this->itt->get_supported_languages();
			$fields = array();
			for($i=1; $i<=3; $i++){
				$fields['itt_langprio'.$i]	= array(
					'type'	=> 'dropdown',
					'options'	=> $itt_langlist,
				);
			}
			$this->form->add_fields($fields, 'ittlanguages', 'itemtooltip');
			
			//check if user wanted to reset itt-cache
			if($this->in->get('itt_reset', false)) {
				$this->itt->reset_cache();
				$this->core->message($this->user->lang('itt_reset_success'), $this->user->lang('success'), 'green');
			}

			//check if user wanted to reload defaults$
			if($this->in->get('itt_force_default', '') != ''){
				$this->config->set($this->itt->changed_prio1($this->in->get('default_game'), $this->in->get('itt_prio1')));
				$this->core->message($this->user->lang('itt_default_success'), $this->user->lang('success'), 'green');
			}
		}

		// The importer settings
		if($this->game->get_importAuth('a_members_man', 'guild_import')){
			if($this->game->get_importers('guild_imp_rsn') && $this->config->get('uc_servername') == ''){
				$gimport_out = '<input type="button" name="add" value="'.$this->user->lang('uc_bttn_import').'" disabled="disabled" />';
			}else{
				$gimport_out = '<input type="button" name="add" value="'.$this->user->lang('uc_bttn_import').'" class="mainoption" onclick="javascript:GuildImport()" />';
			}
			$this->form->add_field('uc_import_guild', array(
				'lang'	=> 'uc_import_guild',
				'type'	=> 'direct',
				'text'	=> $gimport_out,
			), 'importer', 'game');
		}

		if($this->game->get_importAuth('a_members_man', 'char_mupdate')){
			if($this->game->get_importers('guild_imp_rsn') && $this->config->get('uc_servername') == ''){
				$cupdate_out = '<input type="button" name="add" value="'.$this->user->lang('uc_bttn_update').'" disabled="disabled" />';
			}else{
				$cupdate_out = '<input type="button" name="add" value="'.$this->user->lang('uc_bttn_update').'" class="mainoption" onclick="javascript:MassUpdateChars()" />';
			}
			$cupdate_out .= ' ['.(($this->config->get('uc_profileimported')) ? $this->user->lang('uc_last_updated').': '.date($this->user->style['date_time'], $this->config->get('uc_profileimported')) : $this->user->lang('uc_never_updated')).']';
			$this->form->add_field('uc_update_all', array(
				'lang'	=> 'uc_update_all',
				'type'	=> 'direct',
				'text'	=> $cupdate_out,
			), 'importer', 'game');
		}

		// Importer cache reset button
		if(($this->game->get_importAuth('a_members_man', 'guild_import') || $this->game->get_importAuth('a_members_man', 'char_mupdate')) && $this->game->get_importers('import_data_cache')){
			$this->form->add_field('uc_importer_cache', array(
				'lang'	=> 'uc_importer_cache',
				'type'	=> 'direct',
				'text'	=> '<input type="button" name="add" value="'.$this->user->lang('uc_bttn_resetcache').'" class="mainoption" onclick="javascript:ClearImportCache()" />',
			), 'importer', 'game');
		}

		// merge the game admin array to the existing one
		$settingsdata_admin = $this->game->admin_settings();
		if(is_array($settingsdata_admin) && !empty($settingsdata_admin)){
			$this->form->add_fields($settingsdata_admin, 'gamesettings', 'game');
		}

		//Merge authmethod-settings
		if ($authmethodSettings = $this->user->get_authmethod_settings()){
			$this->form->add_fields($authmethodSettings, 'auth', 'system');
		}

		//Merge loginmethod-settings
		if ($arrLoginmethodSettings = $this->user->get_loginmethod_settings()){
			$this->form->add_fields($arrLoginmethodSettings, 'login', 'system');
		}

		// Inject Plugin Settings
		// PLACEHOLDER.. maybe we will do that one day...

		// save the setting
		if ($this->in->exists('save_plus') && $this->checkCSRF('display') && !$this->settings_saved){

			$save_array = $this->form->return_values();
			//check for changed game
			$game_changed = false;
			
			if (($this->in->get('default_game') != $this->config->get('default_game')) || ($this->in->get('game_language') != $this->config->get('game_language'))){
				$game_changed = true;
			}
			//check for changed itt 1.prio and load defaults if so
			if($this->config->get('itt_prio1') != $this->in->get('itt_prio1', '')) {
				$save_array = array_merge($save_array, $this->itt->changed_prio1($this->in->get('default_game'), $this->in->get('itt_prio1')));
			}
			
			//check for changed disable points
			if((int)$this->config->get('disable_points') != $this->in->get('disable_points', 0)){
				if ($this->in->get('disable_points', 0) == 1) {$this->config->set('eqdkp_layout', "nopoints");} else $this->config->set('eqdkp_layout', "normal");
			}

			// Save the settings array
			$this->config->set($save_array);

			// Since ChangeGame alters Config it has to be executed after config-save
			if($game_changed) {
				$this->game->installGame($this->in->get('default_game'), $this->in->get('game_language'));
				$this->pdc->flush();
				$this->form->reset_fields();
				$this->settings_saved = true;
				$this->display();
				#redirect('admin/manage_settings.php'.$this->SID);		// we need to reload cause of the per-game settings
			}

			//clear cache now
			$this->pdc->flush();

			// The Saved-Message
			$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('pk_save_title'), 'green');
		}

		// Output the form, pass values in
		$this->form->output($this->config->get_config());

		$this->core->set_vars(array(
			'page_title'		=> $this->user->lang('config_title'),
			'template_file'		=> 'admin/manage_settings.html',
			'display'			=> true)
		);
	}
}
registry::register('mmocms_settings');
?>