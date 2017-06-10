<?php

require_once 'findpayment.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function findpayment_civicrm_config(&$config) {
  _findpayment_civix_civicrm_config($config);
}

function findpayment_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Contact') {
    $queryObjects[] = new CRM_Findpayment_BAO_Query();
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function findpayment_civicrm_xmlMenu(&$files) {
  _findpayment_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function findpayment_civicrm_install() {
  _findpayment_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function findpayment_civicrm_uninstall() {
  _findpayment_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function findpayment_civicrm_enable() {
  _findpayment_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function findpayment_civicrm_disable() {
  _findpayment_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function findpayment_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _findpayment_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function findpayment_civicrm_managed(&$entities) {
  _findpayment_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function findpayment_civicrm_caseTypes(&$caseTypes) {
  _findpayment_civix_civicrm_caseTypes($caseTypes);
}

function findpayment_civicrm_buildForm($formName, &$form) {
  // hide form buttons of 'View' Contribution' page when accessed via 'Find Payment'
  //   identified by url argument contect=payment
  if ($formName == 'CRM_Contribute_Form_ContributionView' && CRM_Utils_Array::value('context', $_GET) == 'payment') {
    CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.findpayment', 'js/hide_form_buttons.js');
  }
}
/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function findpayment_civicrm_angularModules(&$angularModules) {
_findpayment_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function findpayment_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _findpayment_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function findpayment_civicrm_navigationMenu(&$menu) {
  // get the id of Search Menu
  $searchMenuID = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Search...', 'id', 'name');

  // skip adding menu if there is no administer menu
  if ($searchMenuID) {
    // get the maximum key under adminster menu
    $maxKey = max( array_keys($menu[$searchMenuID]['child']));
    $menu[$searchMenuID]['child'][$maxKey+1] =  array (
      'attributes' => array (
        'label'      => 'Find Payments',
        'name'       => 'Find Payments',
        'url'        => 'civicrm/payment/search',
        'permission' => 'administer CiviCRM, access CiviContribute',
        'operator'   => NULL,
        'separator'  => TRUE,
        'parentID'   => $searchMenuID,
        'navID'      => $maxKey+1,
        'active'     => 1
      )
    );
  }
}
