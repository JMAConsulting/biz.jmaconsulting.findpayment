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

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {

    $sql = "
    SELECT cft.*, cc.sort_name, contri.contact_id, contri.contribution_status_id
    FROM civicrm_financial_trxn cft
    LEFT JOIN civicrm_entity_financial_trxn ceft ON ceft.financial_trxn_id = cft.id AND ceft.entity_table = 'civicrm_contribution'
    LEFT JOIN civicrm_contribution contri ON contri.id = ceft.entity_id
    LEFT JOIN civicrm_contact cc ON cc.id = contri.contact_id
    WHERE cft.id IN (%s)
    ORDER BY cft.id DESC
    ";
    $result = CRM_Core_DAO::executeQuery(sprintf($sql, implode(', ', $this->_paymentIDs)));

    $config = CRM_Core_Config::singleton();
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
    $csv = '"' . implode("\"$config->fieldSeparator\"",
        $headers
      ) . "\"\r\n";
    while($result->fetch()) {
      $paidByLabel = CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $result->payment_instrument_id);
      if (!empty($result->card_type_id)) {
        $paidByLabel .= sprintf(" (%s %s)",
          CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'card_type_id', $result->card_type_id),
          ($result->pan_truncation) ? $result->pan_truncation : ''
        );
      }
      elseif (!empty($result->check_number)) {
        $paidByLabel .= sprintf(" (#%s)", $result->check_number);
      }

      $payment = array(
        $result->sort_name,
        $result->contact_id,
        $result->id,
        $result->trxn_date,
        CRM_Utils_Money::format($result->total_amount, $result->currency),
        $paidByLabel,
        $result->trxn_id,
        CRM_Core_PseudoConstant::getLabel('CRM_Financial_DAO_FinancialTrxn', 'status_id', $result->status_id),
        CRM_Core_PseudoConstant::getLabel('CRM_Contribute_BAO_Contribution', 'contribution_status_id', $result->contribution_status_id),
      );
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
