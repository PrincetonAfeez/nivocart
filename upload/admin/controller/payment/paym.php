<?php
class ControllerPaymentPaym extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('payment/paym');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paym', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('payment/paym', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');

		$this->data['entry_mobile'] = $this->language->get('entry_mobile');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_total_max'] = $this->language->get('entry_total_max');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['help_total'] = $this->language->get('help_total');
		$this->data['help_total_max'] = $this->language->get('help_total_max');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (isset($this->error['mobile_' . $language['language_id']])) {
				$this->data['error_mobile_' . $language['language_id']] = $this->error['mobile_' . $language['language_id']];
			} else {
				$this->data['error_mobile_' . $language['language_id']] = '';
			}
		}

		if (isset($this->error['total_max'])) {
			$this->data['error_total_max'] = $this->error['total_max'];
		} else {
			$this->data['error_total_max'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paym', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/paym', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');

		foreach ($languages as $language) {
			if (isset($this->request->post['paym_mobile_' . $language['language_id']])) {
				$this->data['paym_mobile_' . $language['language_id']] = $this->request->post['paym_mobile_' . $language['language_id']];
			} else {
				$this->data['paym_mobile_' . $language['language_id']] = $this->config->get('paym_mobile_' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		if (isset($this->request->post['paym_total'])) {
			$this->data['paym_total'] = $this->request->post['paym_total'];
		} else {
			$this->data['paym_total'] = $this->config->get('paym_total');
		}

		if (isset($this->request->post['paym_total_max'])) {
			$this->data['paym_total_max'] = $this->request->post['paym_total_max'];
		} else {
			$this->data['paym_total_max'] = $this->config->get('paym_total_max');
		}

		if (isset($this->request->post['paym_order_status_id'])) {
			$this->data['paym_order_status_id'] = $this->request->post['paym_order_status_id'];
		} else {
			$this->data['paym_order_status_id'] = $this->config->get('paym_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['paym_geo_zone_id'])) {
			$this->data['paym_geo_zone_id'] = $this->request->post['paym_geo_zone_id'];
		} else {
			$this->data['paym_geo_zone_id'] = $this->config->get('paym_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['paym_status'])) {
			$this->data['paym_status'] = $this->request->post['paym_status'];
		} else {
			$this->data['paym_status'] = $this->config->get('paym_status');
		}

		if (isset($this->request->post['paym_sort_order'])) {
			$this->data['paym_sort_order'] = $this->request->post['paym_sort_order'];
		} else {
			$this->data['paym_sort_order'] = $this->config->get('paym_sort_order');
		}

		$this->template = 'payment/paym.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paym')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (empty($this->request->post['paym_mobile_' . $language['language_id']])) {
				$this->error['mobile_' .  $language['language_id']] = $this->language->get('error_mobile');
			}
		}

		if (!isset($this->request->post['paym_total_max']) || ($this->request->post['paym_total_max'] > 250)) {
			$this->error['total_max'] = $this->language->get('error_total_max');
		}

		return empty($this->error);
	}
}
