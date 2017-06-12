
{strip}
  <table class="form-layout">
    <tr>
      <td>
        <label>{ts}Transaction Date{/ts}</label>
      </td>
    </tr>
    <tr>
      {include file="CRM/Core/DateRange.tpl" fieldName="financialtrxn_trxn_date" from='_low' to='_high'}
    </tr>
    <tr><td><div class="clear"></div></td></tr>
    <tr>
      <td>
        <div class="float-left">
          {$form.financialtrxn_currency.label} <br />
          {$form.financialtrxn_currency.html|crmAddClass:twenty}
        </div>
        <div class="float-left">
          <label>{ts}Payment Amount{/ts}</label> <br />
          {$form.financialtrxn_amount_low.label}
          {$form.financialtrxn_amount_low.html} &nbsp;&nbsp;
          {$form.financialtrxn_amount_high.label}
          {$form.financialtrxn_amount_high.html}
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
          {$form.financialtrxn_status_id.label}<br />
          {$form.financialtrxn_status_id.html}
        </div>
        <div class="float-left">
          {$form.financialtrxn_trxn_id.label}<br />
          {$form.financialtrxn_trxn_id.html}
        </div>
      </td>
    </tr>
    <tr><td><div class="clear"></div></td></tr>
    <tr>
      <td>
        <div class="float-left">
          {$form.financialtrxn_payment_instrument_id.label}<br />
          {$form.financialtrxn_payment_instrument_id.html|crmAddClass:twenty}
        </div>
        <div class="float-left" id="financialtrxn_check_number_wrapper">
          {$form.financialtrxn_check_number.label} <br />
          {$form.financialtrxn_check_number.html}
        </div>
        <div class="float-left" id="financialtrxn_card_type_id_wrapper">
          {$form.financialtrxn_card_type_id.label} <br />
          {$form.financialtrxn_card_type_id.html}
        </div>
        <div class="float-left" id="financialtrxn_pan_truncation_wrapper">
          {$form.financialtrxn_pan_truncation.label} <br />
          {$form.financialtrxn_pan_truncation.html}
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
