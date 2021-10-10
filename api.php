<?php
/**
 * API CALL
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */

include_once('configs/config.inc.php');

header('Pragma: no-cache');
header('Cache-Control: max-age=1, s-maxage=1, no-store, no-cache, post-check=0, pre-check=0, must-revalidate, proxy-revalidate');

$aValid = array('_');
$guestRoutes = [
    'register',
    'login',
];
if(ctype_alpha(str_replace($aValid, '', @$_REQUEST['action'])) && ((isset($_SESSION['user_data']['logged_in']) && $_SESSION['user_data']['logged_in']) || in_array($_REQUEST['action'], $guestRoutes))) {
    if(trim(@$_REQUEST['action']) != '') {
        $available_actions = io_search_directory('[(.*)\.inc\.php]', 'src/actions');
        foreach($available_actions as $idx => $item) {
            $available_actions[$idx] = basename($item);
        }

        if(in_array($_REQUEST['action'].'.inc.php', $available_actions)) {
            $action_file = 'src/actions/'.$_REQUEST['action'].'.inc.php';
            if(file_exists($action_file)) {
                $output = array();
                include_once($action_file);
                exit;
            } else {
                $output = array(
                    'Error'         => true,
                    'error_message' => 'Invalid action'
                );
            }
        } else {
            $output = array(
                'Error'         => true,
                'error_message' => 'Invalid action'
            );
        }
    } else {
        $output = array(
            'Error'         => true,
            'error_message' => 'Invalid action'
        );
    }
} else {
    $output = array(
        'Error'         => true,
        'error_message' => 'Session expired'
    );
}
echo json_encode($output);
?>