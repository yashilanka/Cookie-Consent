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
 * ExpressionEngine Cookie Check Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Cookie_consent_mcp {

	private $EE;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Module Index Page
	 *
	 * @access	public
	 * @return	string Parsed index view file
	 */	
	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cookie_consent_module_name'));

		$vars['show_lcb'] = 1;
		$vars['show_lcb'] = 0;
		
    	$this->EE->db->select('settings');
    	$this->EE->db->where('class', 'Cookie_consent_ext');
    	$query = $this->EE->db->get('extensions');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$settings = unserialize($row->settings);
			$vars['show_lcb'] = ($settings['show_cp_login_cb'] == 'y') ? 1 : 0;			
			$vars['auto_delete'] = ($settings['auto_delete_all'] == 'y') ? 1 : 0;	
		}
		
		return $this->EE->load->view('index', $vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Save the extension settings
	 *
	 * @access	public
	 * @return	void
	 */	
	public function save_ext_settings()
	{
		$settings['show_cp_login_cb'] = ($this->EE->input->post('show_cp_login_cb') == 'y') ? 'y' : 'n';
		$settings['auto_delete_all'] = ($this->EE->input->post('auto_delete_all') == 'y') ? 'y' : 'n';
				
    	$this->EE->db->where('class', 'Cookie_consent_ext');
    	$this->EE->db->update('extensions', array('settings' => serialize($settings)));

    	$this->EE->session->set_flashdata('message_success', lang('preferences_updated'));

        $this->EE->functions->redirect(
            BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cookie_consent'
    		);		
	
	}
}
// End Cookie_consent CP Class

/* End of file mcp.cookie_consent.php */
/* Location: ./system/expressionengine/third_party/cookie_consent/mcp.cookie_consent.php */