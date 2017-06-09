{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}

        {strip}
          <table class="form-layout">
            <tr>
              <td>
                  <label>{ts}Transaction Date{/ts}</label>
              </td>
            </tr>
            <tr>
              {include file="CRM/Core/DateRange.tpl" fieldName="financial_trxn_trxn_date" from='_low' to='_high'}
            </tr>
            <tr><td><div class="clear"></div></td></tr>
            <tr>
              <td>
                <div class="float-left">
                  {$form.financial_trxn_currency.label} <br />
                  {$form.financial_trxn_currency.html|crmAddClass:twenty}
                </div>
                <div class="float-left">
                <label>{ts}Payment Amount{/ts}</label> <br />
                {$form.financial_trxn_amount_low.label}
                {$form.financial_trxn_amount_low.html} &nbsp;&nbsp;
                {$form.financial_trxn_amount_high.label}
                {$form.financial_trxn_amount_high.html}
              </div>
              </td>
            </tr>
            <tr><td><div class="clear"></div></td></tr>
            <tr>
              <td>
                <div class="float-left">
                  {$form.contribution_id.label}<br />
                  {$form.contribution_id.html}
                </div>
                <div class="float-left">
                  {$form.financial_trxn_status_id.label}<br />
                  {$form.financial_trxn_status_id.html}
                </div>
                <div class="float-left">
                  {$form.financial_trxn_trxn_id.label}<br />
                  {$form.financial_trxn_trxn_id.html}
                </div>
              </td>
            <tr>
            <tr><td><div class="clear"></div></td></tr>
            <tr>
              <td>
                <div class="float-left">
                  {$form.financial_trxn_payment_instrument_id.label}<br />
                  {$form.financial_trxn_payment_instrument_id.html|crmAddClass:twenty}
                </div>
                <div class="float-left" id="financial_trxn_check_number_wrapper">
                  {$form.financial_trxn_check_number.label} <br />
                  {$form.financial_trxn_check_number.html}
                </div>
                <div class="float-left" id="financial_trxn_card_type_id_wrapper">
                  {$form.financial_trxn_card_type_id.label} <br />
                  {$form.financial_trxn_card_type_id.html}
                </div>
                <div class="float-left" id="financial_trxn_pan_truncation_wrapper">
                  {$form.financial_trxn_pan_truncation.label} <br />
                  {$form.financial_trxn_pan_truncation.html}
                </div>
              </td>
            </tr>
            <tr>
              {if $form.contribution_batch_id.html }
                <td>
                  {$form.contribution_batch_id.label}<br />
                  {$form.contribution_batch_id.html}
                </td>
              {/if}
            </tr>
            <tr>
               <td colspan="2">{$form.buttons.html}</td>
            </tr>
            </table>
        {/strip}

