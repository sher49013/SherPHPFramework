<?php
/**
 * Login
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */

$output = array();

$email = arrayGet($_POST, 'email', '');
$password = arrayGet($_POST, 'password', '');

if (!empty($email) && !empty($password)) {
    $hashedPassword = securityHashPassword($password);
    $userData = db_get_row("SELECT * FROM user WHERE email='" . trim($email) . "' AND password='" . trim($hashedPassword) . "'");

    if (empty($userData)) {
        $output = array(
            'success' => false,
            'error_message' => "Wrong Credentials"
        );
    } else {
        $_SESSION['user_data']['id'] = (int)$userData['id'];
        $_SESSION['user_data']['logged_in'] = true;
        $_SESSION['i18']['language'] = 'en_us';

        $output = array(
            'success' => true,
            'message' => 'Login success',
            'id' => (int)$userData['id']
        );
    }
} else {
    $output = array(
        'success'       => false,
        'error_message' => "Required data missing!"
    );
}


echo json_encode($output);