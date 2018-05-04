<?php

class CRM_Findpayment_BAO_Query extends CRM_Contact_BAO_Query_Interface {

  function &getFields() {
    $fields = array();
    return $fields;
  }

 function select(&$query) {
    $returnProperties = array_keys(self::selectorReturnProperties());

    $contactFields = array('sort_name');
    foreach ($returnProperties as $fieldName) {
      if (!empty($query->_returnProperties[$fieldName])) {
        if (substr($fieldName, 0, 14) != 'financialtrxn_') {
          continue;
        }
        $columnName = str_replace('financialtrxn_', '', $fieldName);
        $fieldName = $columnName == 'id' ? $columnName : $fieldName;
        $query->_select[$fieldName] = "civicrm_financial_trxn.{$columnName} as $fieldName";
        $query->_element[$fieldName] = 1;
        $query->_tables['civicrm_contribution'] = 1;
        $query->_tables['civicrm_financial_trxn'] = 1;
      }
    }
  }

  function from($name, $mode, $side) {
  }

 function where(&$query) {
    foreach (array_keys($query->_params) as $id) {
      if (empty($query->_params[$id][0])) {
        continue;
      }
      if (substr($query->_params[$id][0], 0, 14) == 'financialtrxn_') {
        $this->whereClauseSingle($query->_params[$id], $query);
        unset($query->_params[$id]);
      }
    }
  }

  function whereClauseSingle(&$values, &$query) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    $qillTitles = array(
      'invoice_number' => ts('Invoice Number'),
      'financialtrxn_id' => ts('Internal ID'),
      'financialtrxn_trxn_id' => ts('Transaction ID'),
      'financialtrxn_currency' => ts('Currency'),
      'financialtrxn_payment_instrument_id' => ts('Payment Method'),
      'financialtrxn_status_id' => ts('Contribution Status(s)'),
      'financialtrxn_check_number' => ts('Check Number'),
      'financialtrxn_card_type_id' => ts('Credit Card Type'),
      'financialtrxn_pan_truncation' => ts('Credit Card Number'),
    );
    switch ($name) {
      case 'financialtrxn_trxn_date':
      case 'financialtrxn_trxn_date_low':
      case 'financialtrxn_trxn_date_high':
        // process to / from date
        $query->dateQueryBuilder($values,
          'civicrm_financial_trxn',
          'financialtrxn_trxn_date',
          'trxn_date',
          'Transaction Date',
          'Y-m-d H:i:s'
        );
        return;

      case 'financialtrxn_amount_low':
      case 'financialtrxn_amount_high':
        // process min/max amount
        $query->_tables['civicrm_financial_trxn']  = $query->_whereTables['civicrm_financial_trxn'] = 1;
        $query->_tables['civicrm_contribution']  = $query->_whereTables['civicrm_contribution'] = 1;
        $query->numberRangeBuilder($values,
          'civicrm_financial_trxn', 'financialtrxn_amount',
          'total_amount', 'Payment Amount',
          NULL
        );
        return;

      case 'financialtrxn_id':
      case 'financialtrxn_trxn_id':
      case 'financialtrxn_currency':
      case 'financialtrxn_payment_instrument_id':
      case 'financialtrxn_status_id':
      case 'financialtrxn_check_number':
      case 'financialtrxn_card_type_id':
      case 'financialtrxn_pan_truncation':
        $dbName = str_replace('financialtrxn_', '', $name);
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

  /**
   * Add all the elements shared between contribute search and advnaced search.
   *
   * @param CRM_Core_Form $form
   */
  public static function buildSearchForm(&$form) {
    $form->add('hidden', 'hidden_financial_trxn', 1);

    CRM_Core_Form_Date::buildDateRange($form, 'financialtrxn_trxn_date', 1, '_low', '_high', ts('From'), FALSE, FALSE);

    $form->add('text', 'financialtrxn_amount_low', ts('From'), array('size' => 8, 'maxlength' => 8));
    $form->addRule('financialtrxn_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');

    $form->add('text', 'financialtrxn_amount_high', ts('To'), array('size' => 8, 'maxlength' => 8));
    $form->addRule('financialtrxn_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

    $form->add('text', 'contribution_id', ts('Invoice ID'), array('size' => 6, 'maxlength' => 8));

    $form->add('text', 'invoice_number', ts('Invoice Number'), array('size' => 6));

    $form->add('text', 'financialtrxn_trxn_id', ts('Transaction ID'), array('size' => 6, 'maxlength' => 8));

    $form->add('select', 'financialtrxn_payment_instrument_id', ts('Payment Method'),
      CRM_Contribute_PseudoConstant::paymentInstrument(),
      FALSE, array('class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => '- any -')
    );

    $form->add('select', 'financialtrxn_currency',
      ts('Currency'),
      CRM_Core_OptionGroup::values('currencies_enabled'),
      FALSE, array('class' => 'crm-select2', 'placeholder' => '- select -')
    );

    $form->add('select', 'financialtrxn_status_id',
      ts('Contribution Status'), CRM_Core_PseudoConstant::get('CRM_Contribute_DAO_Contribution', 'contribution_status_id'),
      FALSE, array('class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => '- any -')
    );

    foreach (array(
      'financialtrxn_card_type_id',
      'financialtrxn_check_number',
      'financialtrxn_pan_truncation',
    ) as $fieldName) {
      $columnName = str_replace('financialtrxn_', '', $fieldName);
      $attributes = array(
        'entity' => 'FinancialTrxn',
        'name' => $columnName,
        'action' => 'get',
      );
      if ($columnName == 'card_type_id') {
        $attributes['label'] = ts('Card Type');
      }
      if ($columnName == 'pan_truncation') {
        $attributes['label'] = ts('Card Number');
      }
      $form->addField($fieldName, $attributes);
    }

    $form->addEntityRef('contribution_batch_id', ts('Batch Name'), array('entity' => 'Batch'));
  }

  public function registerAdvancedSearchPane(&$panes) {
    $panes['Payments'] = 'financial_trxn';
  }

  public function getPanesMapper(&$panes) {
    $panes['Payments'] = 'civicrm_financial_trxn';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    self::buildSearchForm($form);
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
     $paneTemplatePathArray['financial_trxn'] = 'CRM/Findpayment/Form/Search/Criteria.tpl';
  }

  /**
   * Get the list of fields required to populate the selector.
   *
   * The default return properties array returns far too many fields for 'everyday use. Every field you add to this array
   * kills a small kitten so add carefully.
   */
   public static function selectorReturnProperties() {
     $properties = array(
       'sort_name' => 1,
       'invoice_number' => 1,
       'contribution_id' => 1,
       'financialtrxn_id' => 1,
       'financialtrxn_trxn_date' => 1,
       'financialtrxn_total_amount' => 1,
       'financialtrxn_currency' => 1,
       'financialtrxn_trxn_id' => 1,
       'financialtrxn_status_id' => 1,
       'financialtrxn_payment_instrument_id' => 1,
       'financialtrxn_card_type_id' => 1,
       'financialtrxn_check_number' => 1,
       'financialtrxn_pan_truncation' => 1,
     );

     return $properties;
   }

}
