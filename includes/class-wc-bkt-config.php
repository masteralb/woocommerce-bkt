<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Bkt_Config
{
	public static function bkt_response_codes($code = false)
	{
		$response_codes = array(
			'00' => __('Approved', 'woocommerce-bkt'),
			'01' => __('Refer to card issuer', 'woocommerce-bkt'),
			'02' => __('Refer to card issuer (Special Conditio,n)', 'woocommerce-bkt'),
			'03' => __('Invalid mercha,nt or service provider', 'woocommerce-bkt'),
			'04' => __('Pick-up card', 'woocommerce-bkt'),
			'05' => __('Not Approved', 'woocommerce-bkt'),
			'06' => __('File Update Error', 'woocommerce-bkt'),
			'07' => __('Pick up card (Special Condition)', 'woocommerce-bkt'),
			'08' => __('Examine Identity', 'woocommerce-bkt'),
			'09' => __('Try again', 'woocommerce-bkt'),
			'11' => __('Approved (VIP)', 'woocommerce-bkt'),
			'12' => __('Invalid Transaction', 'woocommerce-bkt'),
			'13' => __('Invalid amount', 'woocommerce-bkt'),
			'14' => __('Invalid Account Number', 'woocommerce-bkt'),
			'15' => __('No Such Issuer', 'woocommerce-bkt'),
			'25' => __('Unable to locate record on file', 'woocommerce-bkt'),
			'28' => __('Original is Denied', 'woocommerce-bkt'),
			'29' => __('Original not found', 'woocommerce-bkt'),
			'30' => __('Format error (switch generated)', 'woocommerce-bkt'),
			'33' => __('Expired card, pick-up', 'woocommerce-bkt'),
			'36' => __('Restricted card, pick-up', 'woocommerce-bkt'),
			'38' => __('Allowable PIN tries ,exceeded', 'woocommerce-bkt'),
			'41' => __('Lost card, Pick-up', 'woocommerce-bkt'),
			'43' => __('Stolen card, pick-up,', 'woocommerce-bkt'),
			'51' => __('Insufficient funds', 'woocommerce-bkt'),
			'52' => __('No checking account', 'woocommerce-bkt'),
			'53' => __('No savings acc,ount', 'woocommerce-bkt'),
			'54' => __('Expired card', 'woocommerce-bkt'),
			'55' => __('Incorrect PIN', 'woocommerce-bkt'),
			'56' => __('Unsupported Card', 'woocommerce-bkt'),
			'57' => __('Transaction not permitted to card', 'woocommerce-bkt'),
			'58' => __('POS doesn\'t have permission for this transaction', 'woocommerce-bkt'),
			'61' => __('Exceeds withdrawal amount limit', 'woocommerce-bkt'),
			'62' => __('Restricted card', 'woocommerce-bkt'),
			'63' =>	__('Security violation', 'woocommerce-bkt'),
			'65' =>	__('Cash Limit Exceeded', 'woocommerce-bkt'),
			'75' =>	__('Allowable number of PIN tries exceed', 'woocommerce-bkt'),
			'76' =>	__('Key synchronization error', 'woocommerce-bkt'),
			'77' =>	__('Decline of Request, No script', 'woocommerce-bkt'),
			'78' =>	__('Unsafe PIN', 'woocommerce-bkt'),
			'79' =>	__('ARQC failed', 'woocommerce-bkt'),
			'81' =>	__('Device Version Incompatible', 'woocommerce-bkt'),
			'85' =>	__('Approval of request (for PIN man)', 'woocommerce-bkt'),
			'91' =>	__('Issuer or switch is inoperative', 'woocommerce-bkt'),
			'92' =>	__('Financial institution unknown', 'woocommerce-bkt'),
			'95' =>	__('POS Negotiation Error', 'woocommerce-bkt'),
			'96' =>	__('System malfunction', 'woocommerce-bkt'),
			'98' =>	__('Duplicate Reversal', 'woocommerce-bkt')
		);

		return ($code && array_key_exists($code, $response_codes)) ? $response_codes[$code] : $response_codes;
	}

	public static function application_error_codes($code = false)
	{
		$error_codes = array(
			'M001' => __('Bonus amount can not be greater than order amount', 'woocommerce-bkt'),
			'M002' => __('Currency Code Is Invalid', 'woocommerce-bkt'),
			'M003' => __('Currency Code Is Missing', 'woocommerce-bkt'),
			'M004' => __('Zero or empty amount or wrong.', 'woocommerce-bkt'),
			'M005' => __('Cvv2 must be represent but it is empty now.', 'woocommerce-bkt'),
			'M006' => __('Expiry Date Is Missing Or Wrong Length Must be 4 characters.', 'woocommerce-bkt'),
			'M007' => __('Fail Url Is Missing', 'woocommerce-bkt'),
			'M008' => __('Pan Is Missing Or Invalid Length Of Pan.Must be 13 >= Pan <= 19', 'woocommerce-bkt'),
			'M009' => __('Password match not succeed please confirm your password: {0}', 'woocommerce-bkt'),
			'M010' => __('Ok Url Is Missing', 'woocommerce-bkt'),
			'M011' => __('Pareq could not prepared.', 'woocommerce-bkt'),
			'M012' => __('Purchase Amount Is Missing Or Wrong Length', 'woocommerce-bkt'),
			'M013' => __('Mbr Id is missing.', 'woocommerce-bkt'),
			'M014' => __('Unknown Secure Type', 'woocommerce-bkt'),
			'M015' => __('Unknown Txn Type', 'woocommerce-bkt'),
			'M016' => __('User Code Is Missing', 'woocommerce-bkt'),
			'M017' => __('User Pass Is Missing', 'woocommerce-bkt'),
			'M018' => __('Wrong Cvv2 {0}', 'woocommerce-bkt'),
			'M019' => __('Purchase amount must be 0,01-200.000', 'woocommerce-bkt'),
			'M020' => __('Can not make hire-purchase to using full bonus transactions.', 'woocommerce-bkt'),
			'M021' => __('Invalid securetype for this transaction type', 'woocommerce-bkt'),
			'M022' => __('Original transaction order id can not be emtpy for this transaction type', 'woocommerce-bkt'),
			'M023' => __('Original transaction order id must not be sent for this transaction type', 'woocommerce-bkt'),
			'M024' => __('Merchant Id Is Missing', 'woocommerce-bkt'),
			'M025' => __('{0} column wrong length Request : {1} Actual : {2}', 'woocommerce-bkt'),
			'M026' => __('Wrong secure type for this txn type', 'woocommerce-bkt'),
			'M027' => __('Record not found which you\'re looking for', 'woocommerce-bkt'),
			'M028' => __('Order id can not be emtpy for this transaction type', 'woocommerce-bkt'),
			'M030' => __('HASH mismatch', 'woocommerce-bkt'),
			'M038' => __('OkURL must be maximum 200 characters', 'woocommerce-bkt'),
			'M041' => __('Invalid card number', 'woocommerce-bkt'),
			'M042' => __('Plugin Not Found', 'woocommerce-bkt'),
			'M043' => __('Request form can not be empty', 'woocommerce-bkt'),
			'M044' => __('Error during Mpi Post', 'woocommerce-bkt'),
			'M045' => __('Max Amount Error', 'woocommerce-bkt'),
			'M046' => __('Error during report request', 'woocommerce-bkt'),
			'M047' => __('HASH mismatch', 'woocommerce-bkt'),
			'V001' => __('Merchant Find Error', 'woocommerce-bkt'),
			'V002' => __('Could not connect to DS or Resolve Error {0}', 'woocommerce-bkt'),
			'V003' => __('System Closed', 'woocommerce-bkt'),
			'V004' => __('User could not be verified', 'woocommerce-bkt'),
			'V005' => __('Unknown Card Type', 'woocommerce-bkt'),
			'V006' => __('User is not permitted for this transaction type', 'woocommerce-bkt'),
			'V007' => __('Terminal is not active', 'woocommerce-bkt'),
			'V008' => __('Merchant could not be found', 'woocommerce-bkt'),
			'V009' => __('Merchant is not active', 'woocommerce-bkt'),
			'V010' => __('Terminal is not authorized for this type of transaction', 'woocommerce-bkt'),
			'V011' => __('Transaction type is not permitted for this terminal', 'woocommerce-bkt'),
			'V012' => __('Pareq Error {0}', 'woocommerce-bkt'),
		);

		return ($code && array_key_exists($code, $error_codes)) ? $error_codes[$code] : $error_codes;
	}
}
