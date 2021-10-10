<?php
/**
 * Logout
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */
$output = array(
    'message' => 'Successfully logout',
    'success' => true);
$_SESSION = array();
@session_destroy();
doLogout();

echo json_encode($output);