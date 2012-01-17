<form action="<?php echo $action; ?>" method="post" id="payment">
  <input type="hidden" name="pmt_version" value="<?php echo $pmt_version; ?>" />
  <input type="hidden" name="pmt_sellerid" value="<?php echo $pmt_sellerid; ?>" />
  <input type="hidden" name="pmt_id" value="<?php echo $pmt_id; ?>" />
  <input type="hidden" name="pmt_orderid" value="<?php echo $pmt_orderid; ?>" />
  <input type="hidden" name="pmt_reference" value="<?php echo $pmt_reference; ?>" />
  <input type="hidden" name="pmt_amount" value="<?php echo $pmt_amount; ?>" />
  <input type="hidden" name="pmt_currency" value="<?php echo $pmt_currency; ?>" />
  <input type="hidden" name="pmt_okreturn" value="<?php echo $pmt_okreturn; ?>" />
  <input type="hidden" name="pmt_cancelreturn" value="<?php echo $pmt_cancelreturn; ?>" />
  <input type="hidden" name="pmt_delayedpayreturn" value="<?php echo $pmt_delayedpayreturn; ?>" />
  <input type="hidden" name="pmt_buyername" value="<?php echo $pmt_buyername; ?>" />
  <input type="hidden" name="pmt_buyeraddress" value="<?php echo $pmt_buyeraddress; ?>" />
  <input type="hidden" name="pmt_buyerpostalcode" value="<?php echo $pmt_buyerpostalcode; ?>" />
  <input type="hidden" name="pmt_buyeremail" value="<?php echo $pmt_buyeremail; ?>" />
  <input type="hidden" name="pmt_hashversion" value="<?php echo $pmt_hashversion; ?>" />
  <input type="hidden" name="pmt_hash" value="<?php echo $pmt_hash; ?>" />
  <input type="hidden" name="pmt_keygeneration" value="<?php echo $pmt_keygeneration; ?>" />
</form>
  <div class="buttons">
    <div class="right"><a onclick="$('#payment').submit();" class="button"><span><?php echo $button_confirm; ?></span></a></div>
  </div>
