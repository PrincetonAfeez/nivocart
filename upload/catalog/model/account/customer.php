<?php
class ModelAccountCustomer extends Model {

	public function addCustomer($data) {
		if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . (isset($data['fax']) ? (int)$data['fax'] : 0) . "', gender = '" . (isset($data['gender']) ? (int)$data['gender'] : 0) . "', date_of_birth = '" . (isset($data['date_of_birth']) ? $data['date_of_birth'] : 0) . "', salt = '" . $this->db->escape($salt = substr(hash_rand('ripemd128'), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)$customer_group_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()");

		$customer_id = $this->db->getLastId();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', company = '" . $this->db->escape((string)$data['company']) . "', company_id = '" . $this->db->escape($data['company_id']) . "', tax_id = '" . $this->db->escape($data['tax_id']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape((string)$data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "'");

		$address_id = $this->db->getLastId();

		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");

		// Send new customer email
		$this->language->load('mail/customer');

		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

		$message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";

		if (!$customer_group_info['approval']) {
			$message .= $this->language->get('text_login') . "\n";
		} else {
			$message .= $this->language->get('text_approval') . "\n";
		}

		$message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $this->config->get('config_name');

		// HTML Mail
		$template = new Template();

		$template->data['title'] = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		$template->data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
		$template->data['store_name'] = $this->config->get('config_name');
		$template->data['store_url'] = $this->config->get('config_url');
		$template->data['message'] = nl2br($message);

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/register.tpl')) {
			$html = $template->fetch($this->config->get('config_template') . '/template/mail/register.tpl');
		} else {
			$html = $template->fetch('default/template/mail/register.tpl');
		}

		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');

		$mail->setTo($data['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($html);
		$mail->send();

		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail')) {
			$message = $this->language->get('text_signup') . "\n\n";
			$message .= $this->language->get('text_website') . " " . $this->config->get('config_name') . "\n";
			$message .= $this->language->get('text_firstname') . " " . $data['firstname'] . "\n";
			$message .= $this->language->get('text_lastname') . " " . $data['lastname'] . "\n";
			$message .= $this->language->get('text_customer_group') . " " . $customer_group_info['name'] . "\n";

			if ($data['company']) {
				$message .= $this->language->get('text_company') . " " . $data['company'] . "\n";
			}

			$message .= $this->language->get('text_email') . " " . $data['email'] . "\n";
			$message .= $this->language->get('text_telephone') . " " . $data['telephone'] . "\n";

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email_noreply'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_alert_emails'));

			foreach ($emails as $email) {
				if (utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}

	public function addDeletedCustomer($customer_id) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$orders = $this->getTotalCustomersOrders($customer_id);

			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_deleted SET customer_id = '" . (int)$customer_id . "', store_id = '" . (int)$customer_info['store_id'] . "', firstname = '" . $this->db->escape((string)$customer_info['firstname']) . "', lastname = '" . $this->db->escape((string)$customer_info['lastname']) . "', email = '" . $this->db->escape((string)$customer_info['email']) . "', orders = '" . (int)$orders . "', date_added = NOW()");

			// Send deleted customer email
			$this->language->load('mail/delete');

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

			$message = $this->language->get('text_sorry') . "\n\n";
			$message .= $this->language->get('text_services') . "\n\n";
			$message .= $this->language->get('text_thanks') . "\n";
			$message .= $this->config->get('config_name');

			// HTML Mail
			$template = new Template();

			$template->data['title'] = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$template->data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = $this->config->get('config_url');
			$template->data['message'] = nl2br($message);

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/delete.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/delete.tpl');
			} else {
				$html = $template->fetch('default/template/mail/delete.tpl');
			}

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');

			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($html);
			$mail->send();

			// Send to main admin email if new account email is enabled
			if ($this->config->get('config_account_mail')) {
				$message = $this->language->get('text_cancellation') . "\n\n";
				$message .= $this->language->get('text_website') . " " . $this->config->get('config_name') . "\n";
				$message .= $this->language->get('text_firstname') . " " . $customer_info['firstname'] . "\n";
				$message .= $this->language->get('text_lastname') . " " . $customer_info['lastname'] . "\n";

				if ($customer_info['company']) {
					$message .= $this->language->get('text_company') . " " . $customer_info['company'] . "\n";
				}

				$message .= $this->language->get('text_email') . " " . $customer_info['email'] . "\n";
				$message .= $this->language->get('text_telephone') . " " . $customer_info['telephone'] . "\n";

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');

				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email_noreply'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($this->language->get('text_customer'), ENT_QUOTES, 'UTF-8'));
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();

				// Send to additional alert emails if new account email is enabled
				$emails = explode(',', $this->config->get('config_alert_emails'));

				foreach ($emails as $email) {
					if (utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
						$mail->setTo($email);
						$mail->send();
					}
				}
			}
		}
	}

	public function deleteCustomer($customer_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function deleteCustomerWithOrders($customer_id) {
		$password = '';

		for ($i = 0; $i < 8; $i++) {
			$password .= chr(rand(97, 122));
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET salt = '" . $this->db->escape($salt = substr(hash_rand('ripemd128'), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', fax = '0', gender = '0', date_of_birth = '0', newsletter = '0' WHERE customer_id = '" . (int)$customer_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editCustomer($data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . (isset($data['fax']) ? (int)$data['fax'] : 0) . "', gender = '" . (isset($data['gender']) ? (int)$data['gender'] : 0) . "', date_of_birth = '" . (isset($data['date_of_birth']) ? $data['date_of_birth'] : 0) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editPassword($email, $password) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET salt = '" . $this->db->escape($salt = substr(hash_rand('ripemd128'), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function editNewsletter($newsletter) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function getCustomerByToken($token) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE token = '" . $this->db->escape($token) . "' AND token != ''");

		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET token = ''");

		return $query->row;
	}

	public function getCustomerUserAgent($customer_id) {
		$query = $this->db->query("SELECT DISTINCT co.user_agent AS user_agent FROM " . DB_PREFIX . "customer_online co LEFT JOIN " . DB_PREFIX . "customer c ON (co.ip = c.ip) WHERE c.customer_id = '" . (int)$customer_id . "' LIMIT 0,1");

		if ($query->row['user_agent']) {
			return $query->row['user_agent'];
		} else {
			return false;
		}
	}

	public function getCustomers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) ";

		$implode = array();

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_email']) && !is_null($data['filter_email'])) {
			$implode[] = "LCASE(c.email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
		}

		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id'])) {
			$implode[] = "cgd.customer_group_id = '" . $this->db->escape($data['filter_customer_group_id']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($implode)) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function checkCustomerPassword($password, $customer_id, $email) {
		$query = $this->db->query("SELECT CASE WHEN (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') THEN 0 ELSE 1 END AS result FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "' AND LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND status = '1' AND approved = '1'");

		if ($query->row['result']) {
			return $query->row['result'];
		} else {
			return false;
		}
	}

	public function getDeletedByCustomerId($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "customer_deleted WHERE customer_id = '" . (int)$customer_id . "'");

		if ($query->row['total'] == 0) {
			return false;
		} else {
			return true;
		}
	}

	public function getCustomerDateOfBirth($customer_id) {
		$query = $this->db->query("SELECT date_of_birth FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "'");

		if ($query->row['date_of_birth']) {
			return $query->row['date_of_birth'];
		} else {
			return false;
		}
	}

	public function getTotalCustomersOrders($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$customer_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}

	public function getIps($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	public function isBanIp($ip) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ban_ip WHERE ip = '" . $this->db->escape($ip) . "'");

		return $query->num_rows;
	}
}
