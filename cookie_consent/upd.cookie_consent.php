<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Copyright (C) 2012 EllisLab, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of EllisLab, Inc. shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from EllisLab, Inc.
*/

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Cookie Consent Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Update File
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Cookie_consent_upd {

	public $version = '1.1';
	public $ext_settings = array();
	private $EE;
		
	/**
	  * Constructor
	 * @access	public
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->ext_settings = serialize(array('show_cp_login_cb' => 'y', 'auto_delete_all' => 'n'));
	}
	
	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	public function install()
	{
		// We'll set the accept_cookie cookie upon installation
		// to prevent the admin from being logged out while setting up
		$expires = 60*60*24*365;  // 1 year
		$this->EE->functions->set_cookie('cookies_allowed', 'y', $expires);		
		
		// Add Module
		$this->EE->db->insert('modules', array(
			'module_name'    => 'Cookie_consent',
			'module_version'     => $this->version,
			'has_cp_backend'   => 'y'
		));

		// Add action
		$this->EE->db->insert('exp_actions', array(
			'class' => 'Cookie_consent',
			'method' => 'set_cookies_allowed',
		));

		// Add action
		$this->EE->db->insert('exp_actions', array(
			'class' => 'Cookie_consent',
			'method' => 'clear_ee_cookies',
		));		

		// Checks if cookies are allowed before setting them
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'set_cookie_end',
			'method'   => 'check_cookie_permission',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));

		// Parse cookie_check_message forum theme page
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'forum_include_extras',
			'method'   => 'parse_forum_template',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));

		// Allow forum_cookie_consent/cookie_check_message.html in forum theme
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'forum_add_template',
			'method'   => 'forum_add_template',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));

		// Frontend login- require cookies
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'member_member_login_start',
			'method'   => 'front_login_cookie_required',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));	


		// Frontend registration- require cookies
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'member_member_register_errors',
			'method'   => 'front_register_cookie_required',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));	


		// CP login- require cookies
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'login_authenticate_start',
			'method'   => 'cp_login_cookie_required',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));	
		
		// CP login- add checkbox
		$this->EE->db->insert('extensions', array(
			'class'    => 'Cookie_consent_ext',
			'hook'     => 'sessions_start',
			'method'   => 'cp_login_field_add',
			'settings' => $this->ext_settings,
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));			

		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	public function uninstall()
	{
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Cookie_consent'));
				
		$this->EE->db->delete('module_member_groups', array('module_id' => $query->row('module_id')));
		$this->EE->db->delete('modules', array('module_name' => 'Cookie_Consent'));
		$this->EE->db->delete('actions', array('class' => 'Cookie_consent'));
		
		// Disable extension
		$this->EE->db->delete('extensions', array('class' => 'Cookie_consent_ext'));

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */	
	public function update($current='')
	{
		if (version_compare($current, $this->version, '=='))
		{
			return FALSE;
		}

		return TRUE;
	}
	
}
/* END Class */

/* End of file upd.cookie_consent.php */
/* Location: ./system/expressionengine/third_party/cookie_consent/upd.cookie_consent.php */