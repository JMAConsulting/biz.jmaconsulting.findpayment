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
class CRM_Findpayment_Form_ExportTest extends CRM_Findpayment_BaseTest {

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

  public function testPaymentExport() {
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

    $queryParams  = ['financialtrxn_payment_instrument_id' => [1]];
    $queryParams = CRM_Contact_BAO_Query::convertFormValues($queryParams);

    $query = new CRM_Contact_BAO_Query(
      $queryParams,
      CRM_Findpayment_BAO_Query::selectorReturnProperties(),
      NULL, FALSE, FALSE
    );
    $query->_tables['civicrm_financial_trxn'] = $query->_whereTables['civicrm_financial_trxn'] = 1;
    $query->_distinctComponentClause = " civicrm_financial_trxn.id ";
    $query->_groupByComponentClause = " GROUP BY civicrm_financial_trxn.id ";
    $sort = "civicrm_financial_trxn.id desc ";
    $additionalWhereClauses = [" civicrm_financial_trxn.is_payment = 1 "];
    $query = $query->searchQuery(0, 0, $sort,
      FALSE, FALSE,
      FALSE, FALSE,
      FALSE,
      implode(" AND ", $additionalWhereClauses)
    );

    $form = new CRM_Findpayment_Form_Task_Export();
    list($headers, $payments) = $form->export($query);

    $expectedResult = [
      'headers' => [
        ts('Contact Name'),
        ts('Contact ID'),
        ts('Financial Trxn ID/Internal ID'),
        ts('Transaction Date'),
        ts('Payment Amount'),
        ts('Payment Method'),
        ts('Transaction ID (Unsplit)'),
        ts('Transaction Status'),
        ts('Contribution Status'),
      ],
      'payments' => [
        [
          $payments[0][0],
          $payments[0][1],
          $payments[0][2],
          '2014-07-24 00:00:00',
          '$ 30.00',
          'Credit Card',
          '',
          'Completed',
          'Completed',
        ],
        [
          'Lopez, Jose',
          $payments[1][1],
          $payments[1][2],
          '2013-07-24 00:00:00',
          '$ 70.00',
          'Credit Card',
          '',
          'Completed',
          'Completed',
        ],
      ]
    ];
    $this->assertEquals(count($expectedResult['payments']), count($payments));
    $this->assertEquals($expectedResult['headers'], $headers);
    $this->assertEquals($expectedResult['payments'], $payments);
  }

}
