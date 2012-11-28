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
class Cookie_consent_ext {

	public $name = 'Cookie Consent';
	public $version = '1.1';
	public $settings_exist = 'y';
	public $docs_url = 'http://expressionengine.com/user_guide/modules/cookie_consent/index.html';
	public $required_by = array('module');
	public $settings        = array();

	private $EE;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */

	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Checks if cookies are allowed- if not, deletes EE cookies
	 *
	 * Called by set_cookie_end hook in Functions library
	 *
	 * @access	public
	 * @param	array $data Associative array containing five different keys and values:
	 * 		- prefix: exp_ or as specified by config
	 * 		- name: cookie name without prefix appended
	 * 		- value: cookie value after stripslashes()
	 * 		- expire: expiration
	 * 		- domain: as specified in the config		
	 * 		- secure_cookie: 1 or 0, based on configs secure_cookie setting		
	 * @return	mixed Returns nothing if cookies are allowed, ends script and returns
	 * 			FALSE otherwise
	 *
	 */
	public function check_cookie_permission($data)
	{
		if ($this->EE->input->cookie('cookies_allowed'))
		{
			return;
		}

		// If they are setting cookies allowed- allow it
		if ($data['name'] == 'cookies_allowed')
		{
			return;
		}

		// This is a bit awkward, but it allows us to let them accept cookies from the
		// login pages.  The way cookies work, it's the only method I see.
		if ($this->EE->input->post('cookie_consent') == 'y')
		{
			return;
		}

		$time = time();

		// If they are unsetting a cookie- allow it
		if ($data['expire'] != 0 && $data['expire'] < $time)
		{
			return;
		}

		$this->EE->extensions->end_script = TRUE;

		$expire = $time - 86500;
		$prefix_length = strlen($data['prefix']);

		// Clear existing cookies
		// Note- could use function set_cookie, but this is leaner.
		// Why calculate all the variables again?
		
		foreach($_COOKIE as $name => $value)
		{
			if (strncmp($name, $data['prefix'], $prefix_length) == 0)
			{
				setcookie($name, FALSE, $expire, 
					$data['path'], $data['domain'], $data['secure_cookie']);
			}
			elseif ($this->settings['auto_delete_all'] == 'y')
			{
				setcookie($name, FALSE, $expire, '/');	
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Add new template file and folder to 
	 *
	 * Called by forum_add_template hook in mod.forum.php
	 *
	 * @access	public
	 * @param	string $which Name of the element called corresponds to template file name
	 * @param   mixed $classname Currently specified class and corresponding folder name or FALSE
	 * @return	string Class and corresponding folder name
	 *
	 */
	public function forum_add_template($which, $classname)
	{
		if ($which == 'cookie_consent_message')
		{
			$classname = 'forum_cookie_consent';
		}

		return $classname;
	}

	// --------------------------------------------------------------------

	/**
	 * Make the module work in the forum
	 *
	 * Called by forum_include_extras hook in mod.forum.php
	 *
	 * @access	public
	 * @param	object $obj The current Forum object
	 * @param	string $function Function name, corresponds to template name
	 * @param	string $template Forum template contents
	 * @return	string Parsed forum template
	 *
	 */
	public function parse_forum_template($obj, $function, $template)
	{
		// Check for altered template
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$template = $this->EE->extensions->last_call;
		}
		
		// Check for tags
		if ($function != 'cookie_consent_message')
		{
			return $template;
		}

		$cookies_allowed = ($this->EE->input->cookie('cookies_allowed')) ? TRUE : FALSE;

		if ($cookies_allowed)
		{
			$template = $obj->deny_if('cookies_not_allowed', $template);
			$template = $obj->allow_if('cookies_allowed', $template);
		}
		else
		{
			$template = $obj->allow_if('cookies_not_allowed', $template);
			$template = $obj->deny_if('cookies_allowed', $template);
		}

		// Load Cookie consent library
		$this->EE->load->library('cookie_consent_lib');

		$allowed_link = $this->EE->cookie_consent_lib->cookies_allowed_link();
		$clear_all_link = $this->EE->cookie_consent_lib->clear_cookies_link('all');
		$clear_ee_link = $this->EE->cookie_consent_lib->clear_cookies_link('ee');

		$vars = array('{cookies_allowed_link}', '{clear_ee_cookies_link}', '{clear_all_cookies_link}');
		$values = array($allowed_link, $clear_all_link, $clear_ee_link);
		$template = str_replace($vars, $values, $template);

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 * Create the 'clear cookies' checkbox on CP login
	 *
	 * Called by sessions_start hook in Sessions library
	 *	
	 * @access	public
	 * @param	object $obj The current Session object	
	 * @return	void
	 *
	 */
	public function cp_login_field_add($obj)
	{
		if ($this->EE->router->class == 'login' && $this->EE->router->method == 'index')
		{
			if ($this->settings['show_cp_login_cb'] != 'y')
			{
				return;
			}

			// If cookie is set- no need to display
			if ($this->EE->input->cookie('cookies_allowed') == 'y')
			{
				return;
			}
			

			$this->EE->load->library('javascript');
				
			$this->EE->javascript->output("
	var submitRow = $('.submit').closest('p');
	newRow = submitRow.clone(),
	input = newRow.find('.submit'),
	label = newRow.find('span');

	checkbox = '<input type=\"checkbox\" name=\"cookie_consent\" id=\"cookie_consent\" value=\"y\"/>'; 
	input.replaceWith(checkbox);
	label.replaceWith('<span class=\"alert\"><label for=\"cookie_consent\">Allow Cookies</label></span>');

	submitRow.before(newRow);
				");
		}
		
		return;	
	}

	// --------------------------------------------------------------------

	/**
	 * Require cookie consent for Frontend login
	 *
	 * Called by the member_member_login_start hook in mod.member_auth.php
	 *
	 * @access	public
	 * @return	mixed Returns void if cookies are allowed, outputs error 
	 * 			message otherwise
	 *
	 */
	public function front_login_cookie_required()
	{
		if ($this->_form_submission_check())
		{
			return;
		}

		$this->EE->extensions->end_script = TRUE;

		// I do not love this method of outputting the error
		// @todo- rewrite members!
		
		$this->EE->lang->loadfile('cookie_consent');

		$this->EE->output->show_user_error('general', lang('cookie_consent_required'));		
	}

	// --------------------------------------------------------------------

	/**
	 * Require cookie consent for Frontend registration
	 *
	 *	Called by member_member_register_errors hook in mod.member_register.php
	 *	
	 * @access	public
	 * @param	object $obj field data
	 * @return	void
	 *
	 */
	public function front_register_cookie_required($obj)
	{
		if ($this->_form_submission_check() == FALSE)
		{
			$this->EE->lang->loadfile('cookie_consent');
			$obj->errors[] = lang('cookie_consent_required');
		}

		return;		
	}

	// --------------------------------------------------------------------
	
	/**
 	* Settings Form
 	*
 	* @param   Array   Settings
 	* @return  void
 	*/
	public function settings_form($current)
	{
		// Let's just send them to the module's page
        $this->EE->functions->redirect(
            BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cookie_consent'
    		);		
	}

	// --------------------------------------------------------------------

	/**
	 * Require cookie consent for CP login
	 *
	 * Called by login_authenticate_start hook in Auth library
	 *	
	 * @access	public
	 * @return	mixed Returns void if cookies are allowed, outputs error 
	 * 			message otherwise 
	 *
	 */
	public function cp_login_cookie_required()
	{
		if ($this->_form_submission_check())
		{
			return;
		}

		// Bleh- errors here are displayed using flash data
		// Which yes- requires cookies be enabled
		// Thus we have to fall back on yee old style error display

		$this->EE->extensions->end_script = TRUE;
		$this->EE->lang->loadfile('cookie_consent');

		$this->EE->output->show_user_error('general', lang('cookie_consent_required'));	
	}

	// --------------------------------------------------------------------

	/**
	 * Handles form submission to see if cookies are allowed
	 *
	 * @access	private
	 * @return	bool TRUE if cookies allowed, FALSE otherwise
	 *
	 */
	private function _form_submission_check()
	{
		if ($this->EE->input->cookie('cookies_allowed'))
		{
			return TRUE;
		}

		if ($this->EE->input->post('cookie_consent') == 'y')
		{
			$expires = 60*60*24*365;  // 1 year

			$this->EE->functions->set_cookie('cookies_allowed', 'y', $expires);

			return TRUE;
		}

		return FALSE;		
	}

	// Note: activate_extension() and update_extension() handled by module upd file
	
}

/* End of file ext.cookie_consent.php */
/* Location: ./system/expressionengine/third_party/cookie_consent/ext.cookie_consent.php */