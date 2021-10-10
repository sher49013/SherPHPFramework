<?php
/**
 * delete user
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */
$id = db_escape_string($_POST['id']);
db_execute("DELETE FROM user WHERE id = $id");

$output = array(
    'message' => 'Successfully delete',
    'success' => true
);

echo json_encode($output);