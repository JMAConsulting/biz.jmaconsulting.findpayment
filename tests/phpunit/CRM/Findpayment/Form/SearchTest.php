<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../BaseTest.php';
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Findpayment_Form_SearchTest extends CRM_Findpayment_BaseTest {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testFilters() {
    $creditCardID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'payment_instrument_id', 'Credit Card');
    $completedStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
    $pendingStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
    $visa = CRM_Core_PseudoConstant::getKey('CRM_Core_BAO_FinancialTrxn', 'card_type_id', 'Visa');
    $contribution1 = $this->callAPISuccess('contribution', 'create', array(
      'contact_id' => $this->_contactID,
      'receive_date' => '2010-01-20',
      'total_amount' => 200.00,
      'financial_type_id' => CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'financial_type_id', 'Donation'),
      'non_deductible_amount' => 10.00,
      'fee_amount' => 0.00,
      'net_amount' => 100.00,
      'trxn_id' => 11212,
      'invoice_id' => 343242,
      'source' => 'SSF',
      'contribution_status_id' => $completedStatusID,
      'payment_instrument_id' => $creditCardID,
      'pan_truncation' => '1111',
      'card_type_id' => $visa,
    ));
    $contributionID1 = $contribution1['id'];

    $useCases = array(
      array(
        'form_value' => array('financialtrxn_payment_instrument_id' => $creditCardID),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Payment Method = Credit Card',
      ),
      array(
        'form_value' => array('financialtrxn_status_id' => array($completedStatusID)),
        'expected_count' => 2,
        'expected_contribution' => array($this->_contributionID, $contributionID1),
        'expected_qill' => 'Contribution Status(s) In Completed',
      ),
      /**
       * @todo the search query is not behaving well with pending contribution
      array(
        'form_value' => array('financialtrxn_status_id' => array($completedStatusID, $pendingStatusID)),
        'expected_count' => 3, //@todo this suppose to be 3
        'expected_contribution' => array($this->_contributionID, $contributionID1, $contributionID2),
        'expected_qill' => 'Contribution Status(s) In Completed',
      ),
      */
      array(
        'form_value' => array('financialtrxn_amount_low' => 101),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Payment Amount - greater than "101"',
      ),
      array(
        'form_value' => array('financialtrxn_currency' => 'USD'),
        'expected_count' => 2,
        'expected_contribution' => array($this->_contributionID, $contributionID1),
        'expected_qill' => 'Currency = US Dollar',
      ),
      array(
        'form_value' => array('financialtrxn_check_number' => 'X0121'),
        'expected_count' => 1,
        'expected_contribution' => array($this->_contributionID),
        'expected_qill' => 'Check Number = X0121',
      ),
      array(
        'form_value' => array(
          'financialtrxn_card_type_id' => array(
            $visa,
            CRM_Core_PseudoConstant::getKey('CRM_Core_BAO_FinancialTrxn', 'card_type_id', 'Amex')
          )
        ),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Credit Card Type In Visa, Amex',
      ),
      array(
        'form_value' => array('financialtrxn_pan_truncation' => '1111'),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Credit Card Number = 1111',
      ),
      array(
        'form_value' => array('contribution_id' => $contributionID1),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Contribution ID = ' . $contributionID1,
      ),
      array(
        'form_value' => array('financialtrxn_trxn_id' => '11212'),
        'expected_count' => 1,
        'expected_contribution' => array($contributionID1),
        'expected_qill' => 'Transaction ID = 11212',
      ),
    );

    foreach ($useCases as $case) {
      $fv = $case['form_value'];
      CRM_Contact_BAO_Query::processSpecialFormValue($fv, array('financialtrxn_status_id'));
      $query = new CRM_Contact_BAO_Query(CRM_Contact_BAO_Query::convertFormValues($fv));

      list($select, $from, $where, $having) = $query->query();

      // get and assert contribution count
      $contributions = CRM_Core_DAO::executeQuery(sprintf('SELECT DISTINCT civicrm_contribution.id %s %s AND civicrm_contribution.id IS NOT NULL', $from, $where))->fetchAll();
      foreach ($contributions as $key => $value) {
        $contributions[$key] = $value['id'];
      }
      $this->assertEquals($case['expected_count'], count($contributions));
      $this->assertEquals($case['expected_contribution'], $contributions);
      // get and assert qill string
      $qill = trim(implode($query->getOperator(), CRM_Utils_Array::value(0, $query->qill())));
      $this->assertEquals($case['expected_qill'], $qill);
    }
  }

  public function testBatchFilter() {
    $contactID1 = $this->createDummyContact();
    $batchTitle = CRM_Batch_BAO_Batch::generateBatchName();
    // create batch
    $batch = civicrm_api3('Batch', 'create', array(
      'created_id' => $this->_contactID,
      'created_date' => CRM_Utils_Date::processDate(date("Y-m-d"), date("H:i:s")),
      'status_id' => CRM_Core_Pseudoconstant::getKey('CRM_Batch_BAO_Batch', 'status_id', 'Data Entry'),
      'title' => $batchTitle,
      'item_count' => 2,
      'total' => 100,
      'type_id' => array_search('Contribution', CRM_Batch_BAO_Batch::buildOptions('type_id')),
    ));
    $batchID = $batch['id'];

    $batchEntry = array(
      'primary_profiles' => array(1 => NULL, 2 => NULL, 3 => NULL),
      'primary_contact_id' => array(
        1 => $this->_contactID,
        2 => $contactID1,
      ),
      'field' => array(
        1 => array(
          'financial_type' => 1,
          'total_amount' => 70,
          'receive_date' => '2013-07-24',
          'receive_date_time' => NULL,
          'payment_instrument' => 1,
          'check_number' => NULL,
          'contribution_status_id' => 1,
        ),
        2 => array(
          'financial_type' => 1,
          'total_amount' => 30,
          'receive_date' => '2014-07-24',
          'receive_date_time' => NULL,
          'payment_instrument' => 1,
          'check_number' => NULL,
          'contribution_status_id' => 1,
        ),
      ),
      'actualBatchTotal' => 100,
    );

    // process batch entries
    $form = new CRM_Batch_Form_Entry();
    $form->setBatchID($batchID);
    $form->testProcessContribution($batchEntry);

    $useCases = array(
      array(
        'form_value' => array('contribution_batch_id' => $batchID),
        'expected_count' => 2,
        'expected_qill' => 'Batch Name = ' . $batchTitle,
      ),
    );

    foreach ($useCases as $case) {
      $fv = $case['form_value'];
      //CRM_Contact_BAO_Query::processSpecialFormValue($fv, array('financialtrxn_status_id'));
      $query = new CRM_Contact_BAO_Query(CRM_Contact_BAO_Query::convertFormValues($fv));

      list($select, $from, $where, $having) = $query->query();

      // get and assert contribution count
      $contributions = CRM_Core_DAO::executeQuery(sprintf('SELECT DISTINCT civicrm_contribution.id %s %s AND civicrm_contribution.id IS NOT NULL', $from, $where))->fetchAll();
      foreach ($contributions as $key => $value) {
        $contributions[$key] = $value['id'];
      }
      $this->assertEquals($case['expected_count'], count($contributions));
      // get and assert qill string
      $qill = trim(implode($query->getOperator(), CRM_Utils_Array::value(0, $query->qill())));
      $this->assertEquals($case['expected_qill'], $qill);
    }
  }

}
