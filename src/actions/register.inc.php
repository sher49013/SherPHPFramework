<?php
/**
 * Register
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */

$output = array();

$email = arrayGet($_POST, 'email', '');
$userName = arrayGet($_POST, 'user_name', '');
$password = arrayGet($_POST, 'password', '');

if (!empty($email) && !empty($userName) && !empty($password)) {
    $count = db_get_one("SELECT COUNT(*) FROM user WHERE email= '$email'");
    if((int)$count > 0) {
        $output = array(
            'success'       => false,
            'error_message' => "email already exists!"
        );
    } else {
        // insert account
        $query = "INSERT INTO user SET user_name = '$userName',
                    email = '$email',
                    password = '".securityHashPassword($password)."',
                    created_at = '".time()."'";
        db_execute($query);
        $id = db_insert_id();
        $output = array(
            'message' => 'Successfully registered, Now you can login.',
            'success' => true,
            'id' => $id
        );
    }
} else {
    $output = array(
        'success'       => false,
        'error_message' => "Required data missing!"
    );
}

echo json_encode($output);