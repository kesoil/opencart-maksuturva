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
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->data['action'] = 'https://www.maksuturva.fi/NewPayment.pmt';
		
		function viitetarkiste($viite) {
			$viite = strval($viite);
			$paino = array(7, 3, 1);
			$summa = 0;
			for($i=strlen($viite)-1, $j=0; $i>=0; $i--,$j++){
				$summa += (int) $viite[$i] * (int) $paino[$j%3];
			}
			return (10-($summa%10))%10;
		}
		
		//Maksusanoman sisällön määrittelyversio.
		$this->data['pmt_version']		= '0003';
		//Suomen Maksuturva Oy:n Kauppiaalle tunnistamista varten antama tunnus.
		$this->data['pmt_sellerid']	= $this->config->get('maksuturva_sellerid');
		//Kauppiaan maksulle antama yksilöivä tunnus.
		$this->data['pmt_id']			= $order_info['order_id'];
		//Tilausnumero, jolla tilaus löytyy Kauppiaan järjestelmästä ja joka on ostajalla tiedossa.
		$this->data['pmt_orderid']		= $order_info['order_id'];
		//Viitenumero, jota Suomen Maksuturva Oy käyttää hyvittäessään rahat toimituksen jälkeen kauppiaalle.
		$this->data['pmt_reference']	= (1000000000)+$order_info['order_id'].viitetarkiste((1000000000)+$order_info['order_id']);
			
		//Tilauksen loppusumma toimituskuluineen. Summa tulee esittää aina kahden desimaalin tarkkuudella. Desimaalierottimena käytetään pilkkua esim. 94,80
		$this->data['pmt_amount']		= str_replace('.', ',', $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], FALSE));
		//Maksussa käytettävä valuutta. Aina EUR.
		$this->data['pmt_currency']	= 'EUR';

		//Osoite, mihin käyttäjän selain ohjataan onnistuneen maksun jälkeen.
		$this->data['pmt_okreturn']			= HTTP_SERVER.'/index.php?route=payment/maksuturva/callback';
		//Osoite, mihin käyttäjän selain ohjataan hänen valitessaan maksun peruutuksen.
		$this->data['pmt_cancelreturn'] 		= HTTP_SERVER.'/index.php';
		//Osoite, mihin käyttäjän selain ohjataan hänen valitessaan maksutavakseen esim. tilisiirron. 
		$this->data['pmt_delayedpayreturn'] = HTTP_SERVER.'/index.php?route=payment/maksuturva/callbackdelayed';
		
		//Laskutusosoitteen (ostajan) nimi.
		$this->data['pmt_buyername'] 			= $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
		//Laskutusosoitteen (ostajan) postiosoite (katuosoite tai postilokero).
		$this->data['pmt_buyeraddress']		= $order_info['payment_address_1'].' '.$order_info['payment_address_2'];
		//Laskutusosoitteen (ostajan) postinumero.
		$this->data['pmt_buyerpostalcode']	= $order_info['payment_postcode'];
		//Käyttäjän sähköpostiosoite.
		$this->data['pmt_buyeremail']	= $order_info['email'];

		$sellerkey = $this->config->get('maksuturva_sellerkey');
		$sellerkeyver = $this->config->get('maksuturva_sellerkeyver');
		
		if($this->config->get('maksuturva_test')=='1') {
			$this->data['pmt_sellerid'] = 'testikauppias';
			$sellerkey = '11223344556677889900';
			$sellerkeyver = '0';
		}
		
		$this->data['pmt_hashversion']	= 'MD5';
		$this->data['pmt_hash']				= md5($this->data['pmt_id'].'&'.$this->data['pmt_orderid'].'&'.$this->data['pmt_reference'].'&'.
							$this->data['pmt_amount'].'&'.$this->data['pmt_currency'].'&'.$sellerkey.'&');
		$this->data['pmt_keygeneration'] = $sellerkeyver;

		$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		
		$this->id = 'payment';

		$this->template = 'default/template/payment/maksuturva.tpl';

		$this->render();
	}
	
	public function callback() {
		
		$sellerkey = $this->config->get('maksuturva_sellerkey');
		$sellerkeyver = $this->config->get('maksuturva_sellerkeyver');
		if($this->config->get('maksuturva_test')=='1') {
			$this->data['pmt_sellerid'] = 'testikauppias';
			$sellerkey = '11223344556677889900';
			$sellerkeyver = '0';
		}
		
		$error = '';
		if ($this->request->get['pmt_version']!='0003') $error .= 'Vääräsanomaversio :'.$this->request->get['pmt_version'];
		if (!isset($this->request->get['pmt_id'])) $error .= 'Ei maksutunnusta.';
		if (!isset($this->request->get['pmt_reference'])) $error .= 'Ei viitettä.';
		if (!isset($this->request->get['pmt_amount'])) $error .= 'Ei maksumäärää.';
		if (!isset($this->request->get['pmt_hash'])) $error .= 'Ei Tarkastesummaa.';
		if ($this->request->get['pmt_currency']!='EUR') $error .= 'Väärä valuutta :'.$this->request->get['pmt_currency'];
		$sum=strtoupper(md5($this->request->get['pmt_id']."&"
			.$this->request->get['pmt_reference']."&"
			.$this->request->get['pmt_amount']."&"
			.$this->request->get['pmt_currency']."&"
			.$sellerkey."&"));
		if ($sum!=$this->request->get['pmt_hash']) $error .= 'Virheellinen tarkastesumma.';

		if ($error == '') {
			$this->language->load('payment/maksuturva');
		
			$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
		
			$this->data['charset'] = $this->language->get('charset');
			$this->data['language'] = $this->language->get('code');
			$this->data['direction'] = $this->language->get('direction');
		
			$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			
			$this->data['text_response'] = $this->language->get('text_response');
			$this->data['text_return'] = $this->language->get('text_success');
			$this->data['text_return_wait'] = sprintf($this->language->get('text_success_wait'), HTTPS_SERVER . 'index.php?route=checkout/success');
			
			$this->load->model('checkout/order');

			$this->model_checkout_order->confirm($this->request->get['pmt_id'], $this->config->get('maksuturva_order_status_id'));
	
			$message = '';
 
			$this->model_checkout_order->update($this->request->get['pmt_id'], $this->config->get('maksuturva_order_status_id'), $message, FALSE);
	
			$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/success';
				
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl';
			} else {
				$this->template = 'default/template/payment/maksuturva_return.tpl';
			}	
		
	  			$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));				
		} else {
			$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/cart';
			$this->data['text_response'] = $this->language->get('text_response');
			$this->data['text_return'] = $this->language->get('text_failure')."<br/>".$error;
			$this->data['text_return_wait'] = sprintf($this->language->get('text_failure_wait'), HTTPS_SERVER . 'index.php?route=checkout/cart');
	
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl';
			} else {
				$this->template = 'default/template/payment/maksuturva_return.tpl';
			}
			
			$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));					
		}
	}
	
	public function callbackdelayed() {

		$error = '';
		if (!isset($this->request->get['pmt_id'])) $error .= 'Ei maksutunnusta';

		if ($error=='') {
			$this->language->load('payment/maksuturva');
		
			$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
		
			$this->data['charset'] = $this->language->get('charset');
			$this->data['language'] = $this->language->get('code');
			$this->data['direction'] = $this->language->get('direction');
		
			$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			
			$this->data['text_response'] = $this->language->get('text_response');
			$this->data['text_return'] = $this->language->get('text_success');
			$this->data['text_return_wait'] = sprintf($this->language->get('text_success_wait'), HTTPS_SERVER . 'index.php?route=checkout/success');
			
			$this->data['text_failure'] = $this->language->get('text_failure');
			$this->data['text_failure_wait'] = $this->language->get('text_failure_wait');

			$this->load->model('checkout/order');

			$this->model_checkout_order->confirm($this->request->get['pmt_id'], $this->config->get('maksuturva_order_status_delayed_id'));
	
			$message = '';

 
			$this->model_checkout_order->update($this->request->get['pmt_id'], $this->config->get('maksuturva_order_status_delayed_id'), $message, FALSE);
	
			$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/success';
				
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl';
			} else {
				$this->template = 'default/template/payment/maksuturva_return.tpl';
			}	
		
	  			$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));				
		} else {
			$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/cart';
			$this->data['text_response_failed'] = $this->language->get('text_response');
			$this->data['text_return'] = $this->language->get('text_failure')."<br/>".$error;
			$this->data['text_return_wait'] = sprintf($this->language->get('text_failure_wait'), HTTPS_SERVER . 'index.php?route=checkout/cart');
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/maksuturva_return.tpl';
			} else {
				$this->template = 'default/template/payment/maksuturva_return.tpl';
			}
			
			$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));					
		}
	}
}
?>
