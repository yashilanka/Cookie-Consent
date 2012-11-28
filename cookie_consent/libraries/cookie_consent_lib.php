<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * ExpressionEngine Cookie Consent Module Library 
 *
 * @package		ExpressionEngine
 * @subpackage	Libraries
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */

class Cookie_consent_lib
{

	private $EE;
		
	/**
	  * Constructor
	 * @access	public
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}	
	
	// --------------------------------------------------------------------

	/**
	 * Create cookies allowed link
	 *
	 * @access	public
	 * @return	string URL with the AID for the set_cookies_allowed method
	 *
	 */
	public function cookies_allowed_link()
	{
		$link = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='
			.$this->EE->functions->fetch_action_id('Cookie_consent', 'set_cookies_allowed');

		$link .= AMP.'RET='.$this->EE->uri->uri_string();	
		
		return $link;		
	}

	// --------------------------------------------------------------------

	/**
	 * Create the 'clear cookies' link
	 *
	 * @access	public
	 * @param	string $clear_all 
	 * @return	string URL with the AID for the clear_ee_cookies method
	 *
	 */
	public function clear_cookies_link($clear_all = 'ee')
	{
		$link = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='
			.$this->EE->functions->fetch_action_id('Cookie_consent', 'clear_ee_cookies');

		$link .= AMP.'CLEAR='.$clear_all;			

		$link .= AMP.'RET='.$this->EE->uri->uri_string();	
		
		return $link;		
	}	
	
}

// END CLASS

/* End of file cookie_consent_lib.php */
/* Location: ./system/expressionengine/third_party/cookie_consent/libraries/cookie_consent_lib.php */