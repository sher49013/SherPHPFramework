<?php
// Helper for phpmailer

use PHPMailer\PHPMailer\PHPMailer;

class class_email extends PHPMailer {	 

	// Extend send function of PHPMailer to catch all exceptions thrown by mailer
	public function Send() {
		try {
			$this->SMTPAutoTLS = true;
			$this->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			return parent::send();
		} catch (phpmailerException $e) {
			return $e->getMessage();
		}
	}
}
?>