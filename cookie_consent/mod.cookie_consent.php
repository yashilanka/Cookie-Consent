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

// --------------------------------------------------------------------

/**
 * ExpressionEngine Cookie Consent Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */

class Cookie_consent {

	public $return_data			= '';	 	// Final data	
	private $EE;

	/**
	  * Constructor
	 * @access	public
	 */
	public function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->load->library('cookie_consent_lib');

	}

	// ------------------------------------------------------------------------

	/**
	 *  Cookie tag
	 *
	 * @access	public
	 * @return	string
	 *
	 */
	public function message()
	{
		$cookies_allowed = ($this->EE->input->cookie('cookies_allowed')) ? 'yes' : 'no';
		
		$variables[] = array(
			'cookies_allowed' => $cookies_allowed,
			'cookies_allowed_link' => $this->EE->cookie_consent_lib->cookies_allowed_link(),
			'clear_all_cookies_link' => $this->EE->cookie_consent_lib->clear_cookies_link('all'),
			'clear_ee_cookies_link' => $this->EE->cookie_consent_lib->clear_cookies_link('ee')
		);

		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);

		return $this->return_data;

	}

	// --------------------------------------------------------------------

	/**
	 * Set the 'cookies_allowed' cookie
	 *
	 * @access	public
	 * @return	string
	 *
	 */
	public function set_cookies_allowed()
	{
		$this->EE->lang->loadfile('cookie_consent');
		$expires = 60*60*24*365;  // 1 year

		$this->EE->functions->set_cookie('cookies_allowed', 'y', $expires);

		$ret = ($this->EE->input->get('RET')) ? $this->EE->input->get('RET') : '';
		$return_link = $this->EE->functions->create_url($ret);

		// Send them a success message and redirect link
		$data = array(
			'title' 	=> lang('cookies_allowed'),
			'heading'	=> lang('cookies_allowed'),
			'content'	=> lang('cookies_allowed_description'),
			'redirect'	=> $return_link,
			'link'		=> array($return_link, lang('cookies_return_to_page')),
			'rate'		=> 3
		);

		$this->EE->output->show_message($data);		
	}

	// --------------------------------------------------------------------

	/**
	 * Clear cookies
	 *
	 * @access	public
	 * @return	string
	 *
	 */
	public function clear_ee_cookies()
	{
		$this->EE->lang->loadfile('cookie_consent');
		
		$all = ($this->EE->input->get('CLEAR') == 'all') ? TRUE : FALSE;
		$prefix = ( ! $this->EE->config->item('cookie_prefix')) ? 'exp_' : $this->EE->config->item('cookie_prefix').'_';
		$expire = time() - 86500;

		// Load cookie helper
		$this->EE->load->helper('cookie');
		$prefix = ( ! $this->EE->config->item('cookie_prefix')) ? 
			'exp_' : $this->EE->config->item('cookie_prefix').'_';
		$prefix_length = strlen($prefix);

		foreach($_COOKIE as $name => $value)
		{
			// Is it an EE cookie?
			// Use Functions method so cookie properties properly set
			if (strncmp($name, $prefix, $prefix_length) == 0)
			{
				$this->EE->functions->set_cookie(substr($name, $prefix_length));
			}
			elseif ($all)
			{
				delete_cookie($name); //setcookie($name, FALSE, $expire, '/');  works
			}
		}

		$ret = ($this->EE->input->get('RET')) ? $this->EE->input->get('RET') : '';
		$return_link = $this->EE->functions->create_url($ret);

		// Send them a success message and redirect link
		$data = array(
			'title' 	=> lang('cookies_deleted'),
			'heading'	=> lang('cookies_deleted'),
			'content'	=> lang('cookies_deleted_description'),
			'redirect'	=> $return_link,
			'link'		=> array($return_link, lang('cookies_return_to_page')),
			'rate'		=> 3
		);

		$this->EE->output->show_message($data);
	}
}

// END CLASS

/* End of file mod.cookie_consent.php */
/* Location: ./system/expressionengine/third_party/cookie_consent/mod.cookie_consent.php */