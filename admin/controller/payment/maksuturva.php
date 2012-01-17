<?php
/*
Copyright 2011  Jani Virta <jani.virta@iqit.fi>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License as
published by the Free Software Foundation; either version 2 of 
the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class ControllerPaymentMaksuturva extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/maksuturva');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
			
			$this->model_setting_setting->editSetting('maksuturva', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_successful'] = $this->language->get('text_successful');
		$this->data['text_declined'] = $this->language->get('text_declined');
		$this->data['text_off'] = $this->language->get('text_off');
		
		$this->data['entry_sellerid'] = $this->language->get('entry_sellerid');
		$this->data['entry_sellerkey'] = $this->language->get('entry_sellerkey');
		$this->data['entry_sellerkeyver'] = $this->language->get('entry_sellerkeyver');
		
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');	
		$this->data['entry_order_status_delayed'] = $this->language->get('entry_order_status_delayed');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['sellerid'])) {
			$this->data['error_sellerid'] = $this->error['sellerid'];
		} else {
			$this->data['error_sellerid'] = '';
		}

 		if (isset($this->error['sellerkey'])) {
			$this->data['error_sellerkey'] = $this->error['sellerkey'];
		} else {
			$this->data['error_sellerkey'] = '';
		}

 		if (isset($this->error['sellerkeyver'])) {
			$this->data['error_sellerkeyver'] = $this->error['sellerkeyver'];
		} else {
			$this->data['error_sellerkeyver'] = '';
		}

		$this->document->breadcrumbs = array();

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=payment/maksuturva&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/maksuturva&token=' . $this->session->data['token'];
		
		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		if (isset($this->request->post['maksuturva_sellerid'])) {
			$this->data['maksuturva_sellerid'] = $this->request->post['maksuturva_sellerid'];
		} else {
			$this->data['maksuturva_sellerid'] = $this->config->get('maksuturva_sellerid');
		}
		
		if (isset($this->request->post['maksuturva_sellerkey'])) {
			$this->data['maksuturva_sellerkey'] = $this->request->post['maksuturva_sellerkey'];
		} else {
			$this->data['maksuturva_sellerkey'] = $this->config->get('maksuturva_sellerkey');
		}
		
		if (isset($this->request->post['maksuturva_sellerkeyver'])) {
			$this->data['maksuturva_sellerkeyver'] = $this->request->post['maksuturva_sellerkeyver'];
		} else {
			$this->data['maksuturva_sellerkeyver'] = $this->config->get('maksuturva_sellerkeyver');
		}

		if (isset($this->request->post['maksuturva_test'])) {
			$this->data['maksuturva_test'] = $this->request->post['maksuturva_test'];
		} else {
			$this->data['maksuturva_test'] = $this->config->get('maksuturva_test');
		}
		
		if (isset($this->request->post['maksuturva_order_status_id'])) {
			$this->data['maksuturva_order_status_id'] = $this->request->post['maksuturva_order_status_id'];
		} else {
			$this->data['maksuturva_order_status_id'] = $this->config->get('maksuturva_order_status_id'); 
		}

		if (isset($this->request->post['maksuturva_order_status_delayed_id'])) {
			$this->data['maksuturva_order_status_delayed_id'] = $this->request->post['maksuturva_order_status_delayed_id'];
		} else {
			$this->data['maksuturva_order_status_delayed_id'] = $this->config->get('maksuturva_order_status_delayed_id'); 
		}

		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['maksuturva_geo_zone_id'])) {
			$this->data['maksuturva_geo_zone_id'] = $this->request->post['maksuturva_geo_zone_id'];
		} else {
			$this->data['maksuturva_geo_zone_id'] = $this->config->get('maksuturva_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['maksuturva_status'])) {
			$this->data['maksuturva_status'] = $this->request->post['maksuturva_status'];
		} else {
			$this->data['maksuturva_status'] = $this->config->get('maksuturva_status');
		}
		
		if (isset($this->request->post['maksuturva_sort_order'])) {
			$this->data['maksuturva_sort_order'] = $this->request->post['maksuturva_sort_order'];
		} else {
			$this->data['maksuturva_sort_order'] = $this->config->get('maksuturva_sort_order');
		}
		
		$this->id       = 'content';
		$this->template = 'payment/maksuturva.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/maksuturva')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['maksuturva_sellerid']) {
			$this->error['sellerid'] = $this->language->get('error_sellerid');
		}

		if (!$this->request->post['maksuturva_sellerkey']) {
			$this->error['sellerkey'] = $this->language->get('error_sellerkey');
		}

		if (!$this->request->post['maksuturva_sellerkeyver']) {
			$this->error['sellerkeyver'] = $this->language->get('error_sellerkeyver');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>
