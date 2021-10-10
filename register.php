<?php
/**
 * Login
 *
 * @package       SHER
 * @subpackage    actions
 *
 * @copyright     HAMAD ALI (ali sher)
 */

/**
 * Include configuration
 */
include_once('configs/config.inc.php');

if($_SESSION['user_data']['logged_in']) {
    network_redirect('dashboard.php');
    die();
}

/**
 * BEGIN - Include CSS
 */
include_once($GLOBALS['config']['cms']['design_path'].'base/header_login.inc.php');
/**
 * END - Include CSS
 */

/**
 * BEGIN - Include JS
 */
$GLOBALS['cms']['includeJS'][] = $GLOBALS['config']['cms']['design_path'].'js/register.js';

/**
 * END - Include JS
 */

/**
 * BEGIN - Business Logic
 */

/**
 * END - Business Logic
 */

/**
 * BEGIN - HTML Output
 */
?>
    <div class="wrapper fadeInDown">
        <div id="formContent">
            <!-- Tabs Titles -->

            <!-- Icon -->
            <div class="fadeIn first">
                Sher FrameWork
            </div>

            <!-- Register Form -->
            <form action="javascript:void(0);" method="post" id="register">
                <input type="text" id="email" class="fadeIn second" name="email" placeholder="email">
                <input type="text" id="user_name" class="fadeIn second" name="user_name" placeholder="user name">
                <input type="password" id="password" class="fadeIn third" name="password" placeholder="password">
                <input type="submit" id="register_submit" class="fadeIn fourth" value="Register"><br>
                <label id="error" class="error"></label>
            </form>

            <div id="formFooter">
                <a class="underlineHover" href="register.php">Register</a>
            </div>

        </div>
    </div>
<?php
/**
 * END - HTML Output
 */
include_once($GLOBALS['config']['cms']['design_path'].'base/footer_login.inc.php');
?>