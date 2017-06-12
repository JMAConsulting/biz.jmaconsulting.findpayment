<?php

require_once 'findpayments.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function findpayments_civicrm_config(&$config) {
  _findpayments_civix_civicrm_config($config);
}

function findpayments_civicrm_queryObjects(&$queryObjects, $type) {
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
function findpayments_civicrm_xmlMenu(&$files) {
  _findpayments_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function findpayments_civicrm_install() {
  _findpayments_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function findpayments_civicrm_uninstall() {
  _findpayments_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function findpayments_civicrm_enable() {
  _findpayments_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function findpayments_civicrm_disable() {
  _findpayments_civix_civicrm_disable();
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
function findpayments_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _findpayments_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function findpayments_civicrm_managed(&$entities) {
  _findpayments_civix_civicrm_managed($entities);
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
function findpayments_civicrm_caseTypes(&$caseTypes) {
  _findpayments_civix_civicrm_caseTypes($caseTypes);
}

function findpayments_civicrm_buildForm($formName, &$form) {
  // hide form buttons of 'View' Contribution' page when accessed via 'Find Payment'
  //   identified by url argument contect=payment
  if ($formName == 'CRM_Contribute_Form_ContributionView' && CRM_Utils_Array::value('context', $_GET) == 'payment') {
    CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.findpayments', 'js/hide_form_buttons.js');
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
function findpayments_civicrm_angularModules(&$angularModules) {
_findpayments_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function findpayments_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _findpayments_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function findpayments_civicrm_navigationMenu(&$menu) {
  foreach (array('Contributions', 'Search...') as $parentName) {
    _findpayments_civix_insert_navigation_menu($menu, $parentName, array(
      'label' => ts('Find Payments', array('domain' => 'biz.jmaconsulting.findpayment')),
      'name' => 'find_payments',
      'url' => 'civicrm/payment/search?reset=1',
      'permission' => 'administer CiviCRM,access CiviContribute',
      'operator' => 'AND',
      'separator' => ($parentName == 'Contributions') ? FALSE : TRUE,
    ));
  }
}
