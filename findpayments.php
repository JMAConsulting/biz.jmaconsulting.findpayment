<?php

require_once 'findpayments.civix.php';

const PAYMENT_MODE = 20;
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
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function findpayments_civicrm_caseTypes(&$caseTypes) {
  _findpayments_civix_civicrm_caseTypes($caseTypes);
}

function findpayments_civicrm_preProcess($formName, &$form) {
  if ('CRM_Contact_Form_Search_Advanced' == $formName) {
    CRM_Contact_Form_Search::$_modeValues[PAYMENT_MODE] = array(
      'selectorName' => 'CRM_Findpayment_Selector_Search',
      'selectorLabel' => ts('Payments'),
      'taskFile' => 'CRM/common/searchResultTasks.tpl',
      'taskContext' => NULL,
      'resultFile' => 'CRM/Findpayment/Form/Selector.tpl',
      'resultContext' => NULL,
      'taskClassName' => 'CRM_Findpayment_Task',
    );
    if ($form->getVar('_componentMode') == PAYMENT_MODE) {
      $modeValue = CRM_Contact_Form_Search::getModeValue(PAYMENT_MODE);
      $form->assign($modeValue);
      $form->setVar('_modeValue', $modeValue);
      CRM_Contact_Form_Search::$_selectorName = $modeValue['selectorName'];
      $form->set('selectorName', $modeValue['selectorName']);
      $submittedParams = $form->getVar('_params');
      $selector = new $modeValue['selectorName'](
         $submittedParams,
         $form->_action,
         NULL, FALSE, NULL,
         "search", "advanced"
      );
      $selector->setKey($form->controller->_key);
      $controller = new CRM_Contact_Selector_Controller($selector,
        $form->get(CRM_Utils_Pager::PAGE_ID),
        NULL,
        CRM_Core_Action::VIEW,
        $form,
        CRM_Core_Selector_Controller::TRANSFER
      );
      $controller->setEmbedded(TRUE);
      $controller->moveFromSessionToTemplate();
    }
  }
}

function findpayments_civicrm_searchTasks($context, &$tasks) {
  if ($context == 'contact') {
    $tasks[CRM_Findpayment_Task::EXPORT_PAYMENTS] = array(
      'title' => ts('Export Payments'),
      'class' => 'CRM_Findpayment_Form_Task_Export',
    );
  }
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
      'label' => ts('Find Payments', array('domain' => 'biz.jmaconsulting.findpayments')),
      'name' => 'find_payments',
      'url' => 'civicrm/payment/search?reset=1',
      'permission' => 'administer CiviCRM,access CiviContribute',
      'operator' => 'AND',
      'separator' => ($parentName == 'Contributions') ? FALSE : TRUE,
    ));
  }
}
