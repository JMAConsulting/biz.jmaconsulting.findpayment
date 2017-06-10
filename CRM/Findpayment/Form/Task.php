<?php

class CRM_Findpayment_Form_Task extends CRM_Core_Form {

  /**
   * The task being performed
   *
   * @var int
   */
  protected $_task;

  /**
   * The array that holds all the contact ids
   *
   * @var array
   */
  public $_paymentIDs;


  /**
   * This includes the submitted values of the search form
   */
  static protected $_searchFormValues;

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    self::preProcessCommon($this);
  }

  /**
   * Common pre-processing function.
   *
   * @param CRM_Core_Form $form
   * @param bool $useTable
   */
  public static function preProcessCommon(&$form, $useTable = FALSE) {
    $form->_paymentIDs = array();

    $values = $form->controller->exportValues($form->get('searchFormName'));

    if (isset($values['radio_ts']) && $values['radio_ts'] == 'ts_sel') {
      foreach ($values as $name => $value) {
        if (substr($name, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
          $form->_paymentIDs[] = substr($name, CRM_Core_Form::CB_PREFIX_LEN);
        }
      }
    }
    else {
      $returnProperties = array(
        'financial_trxn_id' => 1,
      );
      $query = new CRM_Contact_BAO_Query(
        $queryParams = $form->get('queryParams'),
        CRM_Findpayment_BAO_Query::selectorReturnProperties(),
        NULL, FALSE, FALSE
      );

      $query->_tables['civicrm_financial_trxn'] = $query->_whereTables['civicrm_financial_trxn'] = 1;
      $query->_distinctComponentClause = " civicrm_financial_trxn.id ";
      $query->_groupByComponentClause = " GROUP BY civicrm_financial_trxn.id ";

      $sort = "ORDER BY civicrm_financial_trxn.id desc ";
      $result = $query->searchQuery(0, 0, $sort,
      FALSE, FALSE,
      FALSE, FALSE,
      FALSE,
      " civicrm_financial_trxn.is_payment = 1 "
      );
      while ($result->fetch()) {
        $form->_paymentIDs[] = $result->id;
      }
    }

    //set the context for redirection for any task actions
    $session = CRM_Core_Session::singleton();
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $form);
    $urlParams = 'force=1';
    if (CRM_Utils_Rule::qfKey($qfKey)) {
      $urlParams .= "&qfKey=$qfKey";
    }
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/payment/search', $urlParams));
  }

  /**
   * Set default values for the form. Relationship that in edit/view action.
   *
   * The default values are retrieved from the database.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    return $defaults;
  }

  /**
   * Add the rules for form.
   */
  public function addRules() {
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addDefaultButtons(ts('Confirm Action'));
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
  }

  /**
   * Simple shell that derived classes can call to add form buttons.
   *
   * Allows customized title for the main Submit
   *
   * @param string $title
   *   Title of the main button.
   * @param string $nextType
   *   Button type for the form after processing.
   * @param string $backType
   * @param bool $submitOnce
   */
  public function addDefaultButtons($title, $nextType = 'next', $backType = 'back', $submitOnce = FALSE) {
    $this->addButtons(array(
        array(
          'type' => $nextType,
          'name' => $title,
          'isDefault' => TRUE,
        ),
        array(
          'type' => $backType,
          'name' => ts('Cancel'),
          'icon' => 'fa-times',
        ),
      )
    );
  }

}
