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
        if (in_array($fieldName, $contactFields)) {
          $query->_select[$fieldName] = "contact_a.{$fieldName} as $fieldName";
          $query->_element[$fieldName] = 1;
          continue;
        }
        elseif ($fieldName == 'contribution_id') {
          $query->_select[$fieldName] = "civicrm_contribution.id as $fieldName";
          $query->_element[$fieldName] = 1;
          $query->_tables['civicrm_contribution'] = 1;
          continue;
        }
        $columnName = str_replace('financialtrxn_', '', $fieldName);
        $query->_select[$fieldName] = "civicrm_financial_trxn.{$columnName} as $fieldName";
        $query->_element[$fieldName] = 1;
        $query->_tables['civicrm_contribution'] = 1;
        $query->_tables['civicrm_financial_trxn'] = 1;
      }
    }
  }

  function from($name, $mode, $side) {
    //return NULL;
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
      if (substr($query->_params[$id][0], 0, 15) == 'financialtrxn_') {
        self::whereClauseSingle($query->_params[$id], $query);
        //unset($query->_params[$id]);
      }
    }
  }

  function whereClauseSingle(&$values, &$query) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    $qillTitles = array(
      'financialtrxn_trxn_id' => ts('Transaction ID'),
      'financialtrxn_currency' => ts('Currency'),
      'financialtrxn_payment_instrument_id' => ts('Payment Method'),
      'financialtrxn_status_id' => ts('Contribution Status(s)'),
      'financialtrxn_check_number' => ts('Check Number'),
      'financialtrxn_card_type_id' => ts('Credit Card Type'),
      'financialtrxn_pan_truncation' => ts('Pan Truncation'),
    );
    switch ($name) {
      case 'financialtrxn_trxn_date':
      case 'financialtrxn_trxn_date_low':
      case 'financialtrxn_trxn_date_high':
        // process to / from date
        self::dateQueryBuilder($query, $values,
          'civicrm_financial_trxn', 'financialtrxn_trxn_date', 'trxn_date', 'Transaction Date'
        );
        return;

      case 'financialtrxn_amount_low':
      case 'financialtrxn_amount_high':
        // process min/max amount
        $query->numberRangeBuilder($values,
          'civicrm_financial_trxn', 'financialtrxn_amount',
          'total_amount', 'Payment Amount',
          NULL
        );
        return;

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
    CRM_Core_Form_Date::buildDateRange($form, 'financialtrxn_trxn_date', 1, '_low', '_high', ts('From'), FALSE, FALSE);

    $form->add('text', 'financialtrxn_amount_low', ts('From'), array('size' => 8, 'maxlength' => 8));
    $form->addRule('financialtrxn_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');

    $form->add('text', 'financialtrxn_amount_high', ts('To'), array('size' => 8, 'maxlength' => 8));
    $form->addRule('financialtrxn_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

    $form->add('text', 'contribution_id', ts('Invoice ID'), array('size' => 6, 'maxlength' => 8));

    $form->add('text', 'financialtrxn_trxn_id', ts('Transaction ID'), array('size' => 6, 'maxlength' => 8));

    foreach (array(
      'financialtrxn_currency' => 'Contribution',
      'financialtrxn_status_id' => 'Contribution',
      'financialtrxn_payment_instrument_id' => 'Contribution',
      'financialtrxn_card_type_id' => 'FinancialTrxn',
      'financialtrxn_check_number' => 'FinancialTrxn',
      'financialtrxn_pan_truncation' => 'FinancialTrxn',
    ) as $fieldName => $entity) {
      $columnName = str_replace('financialtrxn_', '', $fieldName);
      $columnName = ($columnName == 'status_id') ? 'contribution_status_id' : $columnName;
      $attributes = array(
        'entity' => $entity,
        'name' => $columnName,
        'action' => 'get',
      );
      if ($columnName == 'contribution_status_id') {
        $attributes['label'] = ts('Contribution Status');
      }
      elseif ($columnName == 'card_type_id') {
        $attributes['label'] = ts('Card Type');
      }
      elseif ($columnName == 'payment_instrument_id') {
        $attributes['label'] = ts('Payment Method');
      }
      $form->addField($fieldName, $attributes);
    }

    // Add batch select
    $batches = CRM_Contribute_PseudoConstant::batch();

    if (!empty($batches)) {
      $form->add('select', 'contribution_batch_id',
        ts('Batch Name'),
        array(
          '' => ts('- any -'),
          // CRM-19325
          'IS NULL' => ts('None'),
        ) + $batches,
        FALSE, array('class' => 'crm-select2')
      );
    }

    $form->assign('validCiviContribute', TRUE);
    $form->setDefaults(array('contribution_test' => 0));
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
   * Build query for a date field.
   *
   * @param array $values
   * @param string $tableName
   * @param string $fieldName
   * @param string $dbFieldName
   * @param string $fieldTitle
   * @param bool $appendTimeStamp
   */
  public static function dateQueryBuilder(
    &$query,
    &$values, $tableName, $fieldName,
    $dbFieldName, $fieldTitle,
    $appendTimeStamp = TRUE,
    $format = 'Y-m-d H:i:s'
  ) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    if ($name == "{$fieldName}_low" ||
      $name == "{$fieldName}_high"
    ) {

      $secondOP = $secondPhrase = $secondValue = $secondDate = $secondDateFormat = NULL;

      if ($name == $fieldName . '_low') {
        $firstOP = '>=';
        $firstPhrase = ts('greater than or equal to');
        $firstDate = CRM_Utils_Date::processDate($value, NULL, FALSE, $format);

        $secondValues = $query->getWhereValues("{$fieldName}_high", $grouping);
        if (!empty($secondValues) && $secondValues[2]) {
          $secondOP = '<=';
          $secondPhrase = ts('less than or equal to');
          $secondValue = $secondValues[2];

          if ($appendTimeStamp && strlen($secondValue) == 10) {
            $secondValue .= ' 23:59:59';
          }
          $secondDate = CRM_Utils_Date::processDate($secondValue, NULL, FALSE, $format);
        }
      }
      elseif ($name == $fieldName . '_high') {
        $firstOP = '<=';
        $firstPhrase = ts('less than or equal to');

        if ($appendTimeStamp && strlen($value) == 10) {
          $value .= ' 23:59:59';
        }
        $firstDate = CRM_Utils_Date::processDate($value, NULL, FALSE, $format);

        $secondValues = $query->getWhereValues("{$fieldName}_low", $grouping);
        if (!empty($secondValues) && $secondValues[2]) {
          $secondOP = '>=';
          $secondPhrase = ts('greater than or equal to');
          $secondValue = $secondValues[2];
          $secondDate = CRM_Utils_Date::processDate($secondValue, NULL, FALSE, $format);
        }
      }

      if (!$appendTimeStamp) {
        $firstDate = substr($firstDate, 0, 8);
      }
      $firstDateFormat = CRM_Utils_Date::customFormat($firstDate);

      if ($secondDate) {
        if (!$appendTimeStamp) {
          $secondDate = substr($secondDate, 0, 8);
        }
        $secondDateFormat = CRM_Utils_Date::customFormat($secondDate);
      }

      $query->_tables[$tableName] = $query->_whereTables[$tableName] = 1;
      if ($secondDate) {
        $query->_where[$grouping][] = "
  ( {$tableName}.{$dbFieldName} $firstOP '$firstDate' ) AND
  ( {$tableName}.{$dbFieldName} $secondOP '$secondDate' )
  ";
        $query->_qill[$grouping][] = "$fieldTitle - $firstPhrase \"$firstDateFormat\" " . ts('AND') . " $secondPhrase \"$secondDateFormat\"";
      }
      else {
        $query->_where[$grouping][] = "{$tableName}.{$dbFieldName} $firstOP '$firstDate'";
        $query->_qill[$grouping][] = "$fieldTitle - $firstPhrase \"$firstDateFormat\"";
      }
    }

    if ($name == $fieldName) {
      //In Get API, for operators other then '=' the $value is in array(op => value) format
      if (is_array($value) && !empty($value) && in_array(key($value), CRM_Core_DAO::acceptedSQLOperators(), TRUE)) {
        $op = key($value);
        $value = $value[$op];
      }

      $date = $format = NULL;
      if (strstr($op, 'IN')) {
        $format = array();
        foreach ($value as &$date) {
          $date = CRM_Utils_Date::processDate($date, NULL, FALSE, $format);
          if (!$appendTimeStamp) {
            $date = substr($date, 0, 8);
          }
          $format[] = CRM_Utils_Date::customFormat($date);
        }
        $date = "('" . implode("','", $value) . "')";
        $format = implode(', ', $format);
      }
      elseif ($value && (!strstr($op, 'NULL') && !strstr($op, 'EMPTY'))) {
        $date = CRM_Utils_Date::processDate($value, NULL, FALSE, $format);
        if (!$appendTimeStamp) {
          $date = substr($date, 0, 8);
        }
        $format = CRM_Utils_Date::customFormat($date);
        $date = "'$date'";
      }

      if ($date) {
        $query->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op $date";
      }
      else {
        $query->_where[$grouping][] = self::buildClause("{$tableName}.{$dbFieldName}", $op);
      }

      $query->_tables[$tableName] = $query->_whereTables[$tableName] = 1;

      $op = CRM_Utils_Array::value($op, CRM_Core_SelectValues::getSearchBuilderOperators(), $op);
      $query->_qill[$grouping][] = "$fieldTitle $op $format";
    }
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
