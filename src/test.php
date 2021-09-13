
<?php
/**
 * Save Account record
 *
 * @package       ds
 * @subpackage    actions
 *
 * @copyright     GetMyInvoices
 */

include_once KERNEL_ROOT.'modules/core_data_objects/includes/cdo_utils.inc.php';

if (has_access('api_keys_managment')) {
    $output = array();
    $dataValid = true;

    $prim_uid = isset($_POST['prim_uid']) ? (int)$_POST['prim_uid'] : 0;
    $organisation = isset($_POST['organisation']) ? trim($_POST['organisation']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $street = isset($_POST['street']) ? trim($_POST['street']) : '';
    $zip = isset($_POST['zip']) ? trim($_POST['zip']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $language = isset($_POST['language']) ? (int)$_POST['language'] : 0;
    $timezone = isset($_POST['timezone']) ? trim($_POST['timezone']) : '';
    $vat_id = isset($_POST['vat_id']) ? trim($_POST['vat_id']) : 0;
    $invoice_email_recipient = isset($_POST['invoice_email_recipient']) ? trim($_POST['invoice_email_recipient']) : 0;
    $debitoor_id = isset($_POST['debitoor_id']) ? trim($_POST['debitoor_id']) : '';
    $overdue_invoices = isset($_POST['overdue_invoices']) ? (int)$_POST['overdue_invoices'] : 0;
    $billing_data_validated = isset($_POST['billing_data_validated']) ? (int)$_POST['billing_data_validated'] : 0;
    $auto_review = isset($_POST['auto_review']) ? (int)$_POST['auto_review'] : 0;
    $tracking = isset($_POST['tracking']) ? (int)$_POST['tracking'] : 0;
    $track_queue_accuracy = isset($_POST['track_queue_accuracy']) ? (int)$_POST['track_queue_accuracy'] : 0;
    $track_products_db = isset($_POST['track_products_db']) ? (int)$_POST['track_products_db'] : 0;
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
    $billing_corporation = isset($_POST['billing_corporation']) ? $_POST['billing_corporation'] : 'FDS';
    $billing_monthly_fee = isset($_POST['billing_monthly_fee']) ? (float)$_POST['billing_monthly_fee'] : 0;
    $billing_settings = isset($_POST['billing_settings']) ? $_POST['billing_settings'] : array();
    $immediate_review = isset($_POST['immediate_review']) ? (int)$_POST['immediate_review'] : 0;
    $tenant = isset($_POST['tenant']) ? $_POST['tenant'] : '';
    $autoreview_end_points = isset($_POST['autoreview_endpoints']) ? trim(implode(',', $_POST['autoreview_endpoints'])) : '';

    $corrections_expiration_days = isset($_POST['corrections_expiration_days']) ? (int)$_POST['corrections_expiration_days'] : 0;

    $automated_billing = isset($_POST['automated_billing']) ? (int)$_POST['automated_billing'] : 0;
    $log_requests_in_logger = isset($_POST['log_requests_in_logger']) ? (int)$_POST['log_requests_in_logger'] : 0;
    if ($automated_billing >= 1) {
        $log_requests_in_logger = 1;
    }
    $paid_corrections = isset($_POST['paid_corrections']) ? (int)$_POST['paid_corrections'] : 0;
    $provision_api = isset($_POST['provision_api']) ? (int)$_POST['provision_api'] : 0;
    $master_account_uid = isset($_POST['master_account_uid']) ? (int)$_POST['master_account_uid'] : 0;
    $master_pay = isset($_POST['master_pay']) ? (int)$_POST['master_pay'] : 0;

    $trailPackage = db_get_row("select * from core_package where name = 'trial'");
    $customPackage = db_get_row("select * from core_package where name = 'custom'");
    $packageList = db_get_all("select * from core_package");
    foreach ($packageList as $package) {
        $packages[$package['prim_uid']] = $package['name'];
    }

    if ($organisation == '' || $email == '' || $country == '' || ($prim_uid == 0 && $password == '')) {
        $output = array(
            'error'         => true,
            'error_message' => $GLOBALS['i18']['error']['data_missing']
        );
    } else {
        $exists = cdo_get_data_objects('account', array(),
            ' AND cdo.prim_uid != '.$prim_uid.' AND cdox.email="'.db_escape_string($email).'" ');
        if (!empty($exists)) {
            $dataValid = false;
            $output = array(
                'error'         => true,
                'error_message' => $GLOBALS['i18']['error']['email_exists']
            );
        }
        $exists = cdo_get_data_objects('account', array(),
            ' AND cdo.prim_uid != '.$prim_uid.' AND cdox.organisation="'.db_escape_string($organisation).'" ');
        if (!empty($exists)) {
            $dataValid = false;
            $output = array(
                'error'         => true,
                'error_message' => $GLOBALS['i18']['error']['organiztion_exists']
            );
        }


        if ($dataValid && $prim_uid > 0) {
            $blacklist = cdo_get_data_object($prim_uid, 0, 'account');

            if (empty($blacklist)) {
                $output = array(
                    'error'         => true,
                    'error_message' => $GLOBALS['i18']['error']['data_missing']
                );
            } else {


                if ($blacklist['current_package'] == $_POST['current_package'] && $blacklist['next_package'] == $_POST['next_package']) {
                    $current_package = $blacklist['current_package'];
                    $next_package = $blacklist['next_package'];
                } else {
                    /**
                     * if user change edit current package
                     * post next package not equal to trial
                     */
                    if ($blacklist['current_package'] != $_POST['current_package']) {
                        if ($blacklist['current_package'] == $trailPackage['prim_uid'] || $_POST['current_package'] == $customPackage['prim_uid'] || $blacklist['current_package'] == 0) {
                            if ($_POST['next_package'] == $trailPackage['prim_uid']) {
                                $output = array(
                                    'error'         => true,
                                    'error_message' => $GLOBALS['i18']['next_package_not_trail']
                                );
                                echo json_encode($output);
                                die();
                            }
                            $current_package = db_escape_string($_POST['current_package']);
                            $next_package = db_escape_string($_POST['next_package']);
                        } else {
                            $current_package = db_escape_string($blacklist['current_package']);
                            $next_package = db_escape_string($blacklist['next_package']);
                        }
                    }

                    /**
                     * if user change edit next package
                     * post next package not equal to trial
                     */
                    if ($blacklist['next_package'] != $_POST['next_package']) {
                        if ($_POST['next_package'] == $trailPackage['prim_uid']) {
                            $output = array(
                                'error'         => true,
                                'error_message' => $GLOBALS['i18']['next_package_not_trail']
                            );
                            echo json_encode($output);
                            die();
                        }

                        /**
                         * if current packgae equal to trial so change real time.
                         */
                        if ($blacklist['current_package'] == $trailPackage['prim_uid'] || $_POST['current_package'] == $customPackage['prim_uid'] || $blacklist['current_package'] == 0) {
                            if ($blacklist['current_package'] != $_POST['current_package']) {
                                $current_package = db_escape_string($_POST['current_package']);
                                $next_package = db_escape_string($_POST['next_package']);
                            } else {
                                $current_package = db_escape_string($_POST['next_package']);
                                $next_package = db_escape_string($_POST['next_package']);
                            }
                        } else {
                            $current_package = db_escape_string($blacklist['current_package']);
                            $next_package = db_escape_string($_POST['next_package']);
                        }
                    }
                }

                if ($current_package != $customPackage['prim_uid'] && isset($packages[$current_package])) {
                    $billing_settings['monthly'] = $GLOBALS['config']['cms']['package_prices'][$packages[$current_package]]['monthly'];
                    $billing_settings['monthly_document_review'] = $GLOBALS['config']['cms']['package_prices'][$packages[$current_package]]['monthly_document_review'];
                }

                if ($master_account_uid != 0 && $master_account_uid != null && $master_pay == 1) {
                    $current_package = 0;
                    $next_package = 0;
                    $billing_settings['monthly'] = '';
                    $billing_settings['monthly_document_review'] = '';
                }

                $custom_billing_settings = isset($_POST['custom_billing_settings']) ? $_POST['custom_billing_settings'] : array();
                $end_point = isset($custom_billing_settings['end_point']) ? $custom_billing_settings['end_point'] : array();
                $base_fee = isset($custom_billing_settings['base_fee']) ? $custom_billing_settings['base_fee'] : array();
                $fixed_fee = isset($custom_billing_settings['fixed_fee']) ? $custom_billing_settings['fixed_fee'] : array();
                $billing_settings_custom = isset($custom_billing_settings['billing_settings']) ? $custom_billing_settings['billing_settings'] : array();

                $custom_billing_settings_data = array();
                if ($current_package == $customPackage['prim_uid']) {
                    for ($i = 0; $i < count($custom_billing_settings['base_fee']); $i++) {
                        $custom_billing_settings_data[] = array(
                            'end_point'        => !empty(@$end_point[$i]) ? $end_point[$i] : '',
                            'base_fee'         => !empty(@$base_fee[$i]) ? $base_fee[$i] : '',
                            'fixed_fee'        => !empty(@$fixed_fee[$i]) ? $fixed_fee[$i] : '',
                            'billing_settings' => array(
                                'monthly'                 => !empty(@$billing_settings_custom['monthly'][$i]) ? @$billing_settings_custom['monthly'][$i] : '',
                                'monthly_document_review' => !empty(@$billing_settings_custom['monthly_document_review'][$i]) ? @$billing_settings_custom['monthly_document_review'][$i] : ''
                            )
                        );
                    }
                }

                $record = array(
                    'prim_uid'                    => $prim_uid,
                    'organisation'                => $organisation,
                    'email'                       => $email,
                    'first_name'                  => $first_name,
                    'last_name'                   => $last_name,
                    'street'                      => $street,
                    'zip'                         => $zip,
                    'city'                        => $city,
                    'country_id'                  => $country,
                    'account_language'            => $language,
                    'timezone'                    => $timezone,
                    'vat_id'                      => $vat_id,
                    'invoice_email_recipient'     => $invoice_email_recipient,
                    'overdue_invoices'            => $overdue_invoices,
                    'billing_data_validated'      => $billing_data_validated,
                    'tracking'                    => $tracking,
                    'track_queue_accuracy'        => $track_queue_accuracy,
                    'track_products_db'           => $track_products_db,
                    'auto_review'                 => $auto_review,
                    'active'                      => $active,
                    'billing_corporation'         => $billing_corporation,
                    'billing_monthly_fee'         => $billing_monthly_fee,
                    'billing_settings'            => json_encode($billing_settings),
                    'debitoor_id'                 => $debitoor_id,
                    'automated_billing'           => $automated_billing,
                    'log_requests_in_logger'      => $log_requests_in_logger,
                    'paid_corrections'            => $paid_corrections,
                    'corrections_expiration_days' => $corrections_expiration_days,
                    'current_package'             => $current_package,
                    'next_package'                => $next_package,
                    'provision_api'               => $provision_api,
                    'master_account_uid'          => $master_account_uid,
                    'master_pay'                  => $master_pay,
                    'immediate_review'            => $immediate_review,
                    'tenant'                      => $tenant,
                    'custom_billing_settings'     => json_encode($custom_billing_settings_data),
                    'autoreview_endpoints'        => $autoreview_end_points
                );
                if (!empty($password)) {
                    $record['password'] = $GLOBALS['auth']->encrypt_user_password((string)$password);
                }

                if (cdo_update('account', $record)) {

                    $log_desc = "Account record Updated (".$prim_uid.")";
                    add_log(ACCOUNTS, $log_desc);

                    change_live_correction($paid_corrections, $prim_uid);

                    $output = array(
                        'success' => true
                    );
                } else {
                    $output = array(
                        'error'         => true,
                        'error_message' => $GLOBALS['i18']['error']['invalid_action']
                    );
                }
            }
        }
        if ($dataValid && $prim_uid == 0) {
            $current_package = isset($_POST['current_package']) ? (int)$_POST['current_package'] : 0;
            $next_package = isset($_POST['next_package']) ? (int)$_POST['next_package'] : 0;
            if ($current_package != $trailPackage['prim_uid']) {
                if ($next_package == $trailPackage['prim_uid']) {
                    $output = array(
                        'error'         => true,
                        'error_message' => $GLOBALS['i18']['next_package_not_trail']
                    );
                    echo json_encode($output);
                    die();
                }
            }

            if ($current_package != $customPackage['prim_uid'] && isset($packages[$current_package])) {
                $billing_settings['monthly'] = $GLOBALS['config']['cms']['package_prices'][$packages[$current_package]]['monthly'];
                $billing_settings['monthly_document_review'] = $GLOBALS['config']['cms']['package_prices'][$packages[$current_package]]['monthly_document_review'];
            }


            $custom_billing_settings = isset($_POST['custom_billing_settings']) ? $_POST['custom_billing_settings'] : array();
            $end_point = isset($custom_billing_settings['end_point']) ? $custom_billing_settings['end_point'] : array();
            $base_fee = isset($custom_billing_settings['base_fee']) ? $custom_billing_settings['base_fee'] : array();
            $fixed_fee = isset($custom_billing_settings['fixed_fee']) ? $custom_billing_settings['fixed_fee'] : array();
            $billing_settings_custom = isset($custom_billing_settings['billing_settings']) ? $custom_billing_settings['billing_settings'] : array();

            $custom_billing_settings_data = array();
            if ($current_package == $customPackage['prim_uid']) {
                for ($i = 0; $i < count($custom_billing_settings['base_fee']); $i++) {
                    $custom_billing_settings_data[] = array(
                        'end_point'        => !empty(@$end_point[$i]) ? $end_point[$i] : '',
                        'base_fee'         => !empty(@$base_fee[$i]) ? $base_fee[$i] : '',
                        'fixed_fee'        => !empty(@$fixed_fee[$i]) ? $fixed_fee[$i] : '',
                        'billing_settings' => array(
                            'monthly'                 => !empty(@$billing_settings_custom['monthly'][$i]) ? @$billing_settings_custom['monthly'][$i] : '',
                            'monthly_document_review' => !empty(@$billing_settings_custom['monthly_document_review'][$i]) ? @$billing_settings_custom['monthly_document_review'][$i] : ''
                        )
                    );
                }
            }

            $record = array(
                'organisation'                => $organisation,
                'email'                       => $email,
                'password'                    => $password,
                'first_name'                  => $first_name,
                'last_name'                   => $last_name,
                'street'                      => $street,
                'zip'                         => $zip,
                'city'                        => $city,
                'country_id'                  => $country,
                'account_language'            => $language,
                'timezone'                    => $timezone,
                'vat_id'                      => $vat_id,
                'invoice_email_recipient'     => $invoice_email_recipient,
                'overdue_invoices'            => $overdue_invoices,
                'billing_data_validated'      => $billing_data_validated,
                'tracking'                    => $tracking,
                'track_queue_accuracy'        => $track_queue_accuracy,
                'track_products_db'           => $track_products_db,
                'auto_review'                 => $auto_review,
                'billing_corporation'         => $billing_corporation,
                'billing_monthly_fee'         => $billing_monthly_fee,
                'billing_settings'            => json_encode($billing_settings),
                'active'                      => $active,
                'debitoor_id'                 => $debitoor_id,
                'automated_billing'           => $automated_billing,
                'log_requests_in_logger'      => $log_requests_in_logger,
                'paid_corrections'            => $paid_corrections,
                'corrections_expiration_days' => $corrections_expiration_days,
                'current_package'             => $current_package,
                'next_package'                => $next_package,
                'provision_api'               => $provision_api,
                'master_account_uid'          => $master_account_uid,
                'master_pay'                  => $master_pay,
                'immediate_review'            => $immediate_review,
                'tenant'                      => $tenant,
                'custom_billing_settings'     => json_encode($custom_billing_settings_data),
                'autoreview_endpoints'        => $autoreview_end_points
            );
            if (!empty($password)) {
                $record['password'] = $GLOBALS['auth']->encrypt_user_password((string)$password);
            }

            $account_uid = cdo_insert('account', $record);
            if ($account_uid) {
                $log_desc = "Account record Added (".$account_uid.")";
                add_log(ACCOUNTS, $log_desc);

                $output = array(
                    'success'     => true,
                    'account_uid' => $account_uid
                );
            } else {
                $output = array(
                    'error'         => true,
                    'error_message' => $GLOBALS['i18']['error']['invalid_action']
                );
            }
        }
    }
} else {
    $output = array(
        'error'         => true,
        'error_message' => $GLOBALS['i18']['error']['permission_error']
    );
}


echo json_encode($output);