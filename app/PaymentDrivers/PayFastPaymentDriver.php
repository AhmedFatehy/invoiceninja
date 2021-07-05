<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\PaymentDrivers;

use App\Models\ClientGatewayToken;
use App\Models\GatewayType;
use App\Models\Payment;
use App\Models\PaymentHash;
use App\Models\SystemLog;
use App\PaymentDrivers\PayFast\CreditCard;
use App\Utils\Traits\MakesHash;
use \PayFastPayment;
use Illuminate\Http\Request;

class PayFastPaymentDriver extends BaseDriver
{
    use MakesHash;

    public $refundable = true; //does this gateway support refunds?

    public $token_billing = true; //does this gateway support token billing?

    public $can_authorise_credit_card = true; //does this gateway support authorizations?

    public $payfast; //initialized gateway

    public $payment_method; //initialized payment method

    public static $methods = [
        GatewayType::CREDIT_CARD => CreditCard::class, 
    ];

    const SYSTEM_LOG_TYPE = SystemLog::TYPE_PAYFAST; 



    //developer resources
    //https://sandbox.payfast.co.za/




    public function gatewayTypes(): array
    {
        $types = [];

            $types[] = GatewayType::CREDIT_CARD;

        return $types;
    }
    
    public function endpointUrl()
    {
        if($this->company_gateway->getConfigField('testMode'))
            return 'https://sandbox.payfast.co.za/eng/process';

        return 'https://www.payfast.co.za/eng/process';
    }

    public function init()
    {

        try{
            $this->payfast = new PayFastPayment(
                [
                    'merchantId' => $this->company_gateway->getConfigField('merchantId'),
                    'merchantKey' => $this->company_gateway->getConfigField('merchantKey'),
                    'passPhrase' => $this->company_gateway->getConfigField('passPhrase'),
                    'testMode' => $this->company_gateway->getConfigField('testMode')
                ]
            );
        } catch(Exception $e) {
            echo '##PAYFAST## There was an exception: '.$e->getMessage();
        }

        return $this;
    }

    public function setPaymentMethod($payment_method_id)
    {
        $class = self::$methods[$payment_method_id];
        $this->payment_method = new $class($this);
        return $this;
    }

    public function authorizeView(array $data)
    {
        return $this->payment_method->authorizeView($data); //this is your custom implementation from here
    }

    public function authorizeResponse($request)
    {
        return $this->payment_method->authorizeResponse($request);  //this is your custom implementation from here
    }

    public function processPaymentView(array $data)
    {
        return $this->payment_method->paymentView($data);  //this is your custom implementation from here
    }

    public function processPaymentResponse($request)
    {
        return $this->payment_method->paymentResponse($request); //this is your custom implementation from here
    }

    public function refund(Payment $payment, $amount, $return_client_response = false)
    {
        return $this->payment_method->yourRefundImplementationHere(); //this is your custom implementation from here
    }

    public function tokenBilling(ClientGatewayToken $cgt, PaymentHash $payment_hash)
    {
        return $this->payment_method->yourTokenBillingImplmentation(); //this is your custom implementation from here
    }

    public function generateSignature($data, $passPhrase = null) 
    {
        // Create parameter string
        $pfOutput = '';
        foreach( $data as $key => $val ) {
            if($val !== '') {
                $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        // Remove last ampersand
        $getString = substr( $pfOutput, 0, -1 );
        if( $passPhrase !== null ) {
            $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
        }
        return md5( $getString );
    }

    public function processWebhookRequest(Request $request, Payment $payment = null)
    {

        nlog($request->all());
        return response()->json([], 200);
        
    } 
}
