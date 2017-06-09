<?php

class CRM_Findpayment_BAO_Advanced_Query extends CRM_Contact_BAO_Query_Interface {

  function &getFields() {
    $fields = array();
    return $fields;
  }


  function select(&$query) {
    CRM_Findpayment_BAO_Query::select($query);
  }

  function from($name, $mode, $side) {
  }

  function where(&$query) {
    // hackish fix as $query->_params doesn't have the submitted values
    if (!empty($_POST)) {
      $query->_formValues = $_POST;
      $query->_params = CRM_Contact_BAO_Query::convertFormValues($query->_formValues);
    }

    foreach (array_keys($query->_params) as $id) {
      if (empty($query->_params[$id][0])) {
        continue;
      }
      if (substr($query->_params[$id][0], 0, 15) == 'financial_trxn_') {
        $this->whereClauseSingle($query->_params[$id], $query);
        unset($query->_params[$id]);
      }
    }
  }

  function whereClauseSingle(&$values, &$query) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    $qillTitles = array(
      'financial_trxn_trxn_id' => ts('Transaction ID'),
      'financial_trxn_currency' => ts('Currency'),
      'financial_trxn_payment_instrument_id' => ts('Payment Method'),
      'financial_trxn_status_id' => ts('Contribution Status(s)'),
      'financial_trxn_check_number' => ts('Check Number'),
      'financial_trxn_card_type_id' => ts('Credit Card Type'),
      'financial_trxn_pan_truncation' => ts('Pan Truncation'),
    );
    switch ($name) {
      case 'financial_trxn_trxn_date':
      case 'financial_trxn_trxn_date_low':
      case 'financial_trxn_trxn_date_high':
        // process to / from date
        self::dateQueryBuilder($query, $values,
          'civicrm_financial_trxn', 'financial_trxn_trxn_date', 'trxn_date', 'Transaction Date'
        );
        return;

      case 'financial_trxn_amount_low':
      case 'financial_trxn_amount_high':
        // process min/max amount
        $query->numberRangeBuilder($values,
          'civicrm_financial_trxn', 'financial_trxn_amount',
          'total_amount', 'Payment Amount',
          NULL
        );
        return;

      case 'financial_trxn_trxn_id':
      case 'financial_trxn_currency':
      case 'financial_trxn_payment_instrument_id':
      case 'financial_trxn_status_id':
      case 'financial_trxn_check_number':
      case 'financial_trxn_card_type_id':
      case 'financial_trxn_pan_truncation':
        $dbName = str_replace('financial_trxn_', '', $name);
        $dataType = "String";
        if (in_array($dbName, array('payment_instrument_id', 'status_id', 'card_type_id'))) {
          $dataType = 'Integer';
        }
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_financial_trxn.{$dbName}", $op, $value, $dataType);
        $query->_tables['civicrm_financial_trxn']  = $query->_whereTables['civicrm_financial_trxn'] = 1;
        $query->_tables['civicrm_contribution']  = $query->_whereTables['civicrm_contribution'] = 1;
        list($op, $value) = CRM_Contact_BAO_Query::buildQillForFieldValue('CRM_Financial_DAO_FinancialTrxn', $dbName, $value, $op);
        $query->_qill[$grouping][] = ts('%1 %2 %3', array(1 => $qillTitles[$name], 2 => $op, 3 => $value));
        return;
    }
  }

  public function registerAdvancedSearchPane(&$panes) {
      $panes['Payments'] = 'financial_trxn';
  }

  public function getPanesMapper(&$panes) {
    $panes['Payments'] = 'civicrm_financial_trxn';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    CRM_Findpayment_BAO_Query::buildSearchForm($form);
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
     $paneTemplatePathArray['financial_trxn'] = 'CRM/Findpayment/Form/Search/Criteria.tpl';
  }

}
