<?php

/**
 * This class provides the functionality to export selected payment records.
 */
class CRM_Findpayment_Form_Task_Export extends CRM_Findpayment_Form_Task {

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    parent::preprocess();
    $this->setTitle(ts('Export Payments'));
  }

  /**
   * Build the form object.
   *
   * It consists of
   *    - displaying the QILL (query in local language)
   *    - displaying elements for saving the search
   */
  public function buildQuickForm() {
    //
    // just need to add a javascript to popup the window for printing
    //
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Export Payments'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'back',
          'name' => ts('Done'),
        ),
      )
    );
  }

  public function export(&$query) {
    $headers = array(
      ts('Contact Name'),
      ts('Contact ID'),
      ts('Financial Trxn ID/Internal ID'),
      ts('Transaction Date'),
      ts('Payment Amount'),
      ts('Payment Method'),
      ts('Transaction ID (Unsplit)'),
      ts('Transaction Status'),
      ts('Contribution Status'),
    );
    $payment = [];

    foreach ($query->fetchAll() as $row) {
      $row = (object) $row;
      $paidByLabel = CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $row->financialtrxn_payment_instrument_id);
      if (!empty($row->financialtrxn_card_type_id)) {
        $paidByLabel .= sprintf(" (%s %s)",
          CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'card_type_id', $row->financialtrxn_card_type_id),
          ($row->financialtrxn_pan_truncation) ? $row->financialtrxn_pan_truncation : ''
        );
      }
      elseif (!empty($row->financialtrxn_check_number)) {
        $paidByLabel .= sprintf(" (#%s)", $row->financialtrxn_check_number);
      }

      $payment[] = array(
        $row->sort_name,
        $row->contact_id,
        $row->id,
        $row->financialtrxn_trxn_date,
        CRM_Utils_Money::format($row->financialtrxn_total_amount, $row->financialtrxn_currency),
        $paidByLabel,
        $row->financialtrxn_trxn_id,
        CRM_Core_PseudoConstant::getLabel('CRM_Financial_DAO_FinancialTrxn', 'status_id', $row->financialtrxn_status_id),
        CRM_Core_PseudoConstant::getLabel('CRM_Contribute_BAO_Contribution', 'contribution_status_id', $row->financialtrxn_status_id),
      );
    }

    return [$headers, $payment];
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $config = CRM_Core_Config::singleton();

    list($headers, $payments) = $this->export($this->_queryResult);

    $csv = '"' . implode("\"$config->fieldSeparator\"",
        $headers
      ) . "\"\r\n";
    foreach ($payments as $payment) {
      $csv .= '"' . implode("\"$config->fieldSeparator\"",
          $payment
      ) . "\"\r\n";
    }

    $fileName = 'Export_Payments_' . date('YmdHis') . '.csv';
    CRM_Utils_System::setHttpHeader('Content-Type', 'text/csv');
    //Force a download and name the file using the current timestamp.
    CRM_Utils_System::setHttpHeader('Content-Disposition', 'attachment; filename=' . $fileName);
    echo $csv;
    CRM_Utils_System::civiExit();
  }

}
