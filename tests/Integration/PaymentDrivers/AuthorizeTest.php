<?php

namespace Tests\Integration\PaymentDrivers;

use Tests\TestCase;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerPaymentProfileType;
use net\authorize\api\contract\v1\CustomerProfilePaymentType;
use net\authorize\api\contract\v1\CustomerProfileType;
use net\authorize\api\contract\v1\GetCustomerProfileIdsRequest;
use net\authorize\api\contract\v1\GetCustomerProfileRequest;
use net\authorize\api\contract\v1\GetMerchantDetailsRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\contract\v1\PaymentProfileType;
use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\controller\CreateCustomerPaymentProfileController;
use net\authorize\api\controller\CreateCustomerProfileController;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\GetCustomerProfileController;
use net\authorize\api\controller\GetCustomerProfileIdsController;
use net\authorize\api\controller\GetMerchantDetailsController;

/**
 * @test
 */
class AuthorizeTest extends TestCase
{

    public $customer_profile_id = 1512191314;

    public $customer_payment_profile = 1512219932;

    public function setUp() :void
    {
        parent::setUp();

        if (! config('ninja.testvars.authorize')) {
            $this->markTestSkipped('authorize.net not configured');
        }
        
    }

    public function testUnpackingVars()
    {
        $vars = json_decode(config('ninja.testvars.authorize'));

        $this->assertTrue(property_exists($vars, 'apiLoginId'));
    }

    public function testCreatePublicClientKey()
    {
        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);

        $request = new AnetAPI\GetMerchantDetailsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);

        $controller = new GetMerchantDetailsController($request);

        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

        $this->assertNotNull($response->getPublicClientKey());
    }

    public function testProfileIdList()
    {

        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Get all existing customer profile ID's
        $request = new GetCustomerProfileIdsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $controller = new GetCustomerProfileIdsController($request);
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
        {
            info("GetCustomerProfileId's SUCCESS: " . "\n");
            info(print_r($response->getIds(),1));
         }
        else
        {
            info("GetCustomerProfileId's ERROR :  Invalid response\n");
            $errorMessages = $response->getMessages()->getMessage();
            info("Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n");
        }
        
        $this->assertNotNull($response);
        
    }

    public function testCreateProfile()
    {

        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);

        // Create the Bill To info for new payment type
        $billTo = new CustomerAddressType();
        $billTo->setFirstName("Ellen");
        $billTo->setLastName("Johnson");
        $billTo->setCompany("Souveniropolis");
        $billTo->setAddress("14 Main Street");
        $billTo->setCity("Pecan Springs");
        $billTo->setState("TX");
        $billTo->setZip("44628");
        $billTo->setCountry("USA");
        $billTo->setPhoneNumber("888-888-8888");
        $billTo->setfaxNumber("999-999-9999");

        // Create a customer shipping address
        $customerShippingAddress = new CustomerAddressType();
        $customerShippingAddress->setFirstName("James");
        $customerShippingAddress->setLastName("White");
        $customerShippingAddress->setCompany("Addresses R Us");
        $customerShippingAddress->setAddress(rand() . " North Spring Street");
        $customerShippingAddress->setCity("Toms River");
        $customerShippingAddress->setState("NJ");
        $customerShippingAddress->setZip("08753");
        $customerShippingAddress->setCountry("USA");
        $customerShippingAddress->setPhoneNumber("888-888-8888");
        $customerShippingAddress->setFaxNumber("999-999-9999");

        // Create an array of any shipping addresses
        $shippingProfiles[] = $customerShippingAddress;
        $refId = 'ref' . time();
        $email = "test@gmail.com";

        // // Create a new CustomerPaymentProfile object
        // $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        // $paymentProfile->setCustomerType('individual');
        // $paymentProfile->setBillTo($billTo);
        // $paymentProfile->setPayment($paymentCreditCard);
        // $paymentProfiles[] = $paymentProfile;


        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new CustomerProfileType();
        $customerProfile->setDescription("Customer 2 Test PHP");
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($email);
        //$customerProfile->setpaymentProfiles($paymentProfiles);
        $customerProfile->setShipToList($shippingProfiles);


        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setProfile($customerProfile);

        // Create the controller and get the response
        $controller = new CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
      
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            info("Succesfully created customer profile : " . $response->getCustomerProfileId() . "\n");
            $paymentProfiles = $response->getCustomerPaymentProfileIdList();
            info(print_r($paymentProfiles,1));
            

        } else {
            info("ERROR :  Invalid response\n");
            $errorMessages = $response->getMessages()->getMessage();
            info("Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n");
        }
        
        $this->assertNotNull($response);

    }

    public function testGetCustomerProfileId()
    {

        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);

          $request = new GetCustomerProfileRequest();
          $request->setMerchantAuthentication($merchantAuthentication);
          $request->setCustomerProfileId($this->customer_profile_id);
          $controller = new GetCustomerProfileController($request);
          $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

          if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        
            info("got profile");
            info(print_r($response->getProfile(),1));


        } else {
            info("ERROR :  Invalid response\n");
        }
        
        $this->assertNotNull($response);
    }


    public function testCreateCustomerPaymentProfile()
    {
        info("test create customer payment profile");

        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);
    
        // Set the transaction's refId
        $refId = 'ref' . time();

        // Set credit card information for payment profile
        $creditCard = new CreditCardType();
        $creditCard->setCardNumber("4007000000027");
        $creditCard->setExpirationDate("2038-12");
        $creditCard->setCardCode("142");
        $paymentCreditCard = new PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billto = new CustomerAddressType();
        $billto->setFirstName("Ellen");
        $billto->setLastName("Johnson");
        $billto->setCompany("Souveniropolis");
        $billto->setAddress("14 Main Street");
        $billto->setCity("Pecan Springs");
        $billto->setState("TX");
        $billto->setZip("44628");
        $billto->setCountry("USA");
        $billto->setPhoneNumber("999-999-9999");
        $billto->setfaxNumber("999-999-9999");

        // Create a new Customer Payment Profile object
        $paymentprofile = new CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setBillTo($billto);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);

        $paymentprofiles[] = $paymentprofile;

        // Assemble the complete transaction request
        $paymentprofilerequest = new CreateCustomerPaymentProfileRequest();
        $paymentprofilerequest->setMerchantAuthentication($merchantAuthentication);

        // Add an existing profile id to the request
        $paymentprofilerequest->setCustomerProfileId($this->customer_profile_id);
        $paymentprofilerequest->setPaymentProfile($paymentprofile);
        $paymentprofilerequest->setValidationMode("liveMode");

        // Create the controller and get the response
        $controller = new CreateCustomerPaymentProfileController($paymentprofilerequest);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            info("Create Customer Payment Profile SUCCESS: " . $response->getCustomerPaymentProfileId() . "\n");
        } else {
            info("Create Customer Payment Profile: ERROR Invalid response\n");
            $errorMessages = $response->getMessages()->getMessage();
            info("Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n");

        }

        $this->assertNotNull($response);
    }

    function testChargeCustomerProfile()
    {
        error_reporting (E_ALL & ~E_DEPRECATED);

        $vars = json_decode(config('ninja.testvars.authorize'));

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($vars->apiLoginId);
        $merchantAuthentication->setTransactionKey($vars->transactionKey);
        
        // Set the transaction's refId
        $refId = 'ref' . time();

        $profileToCharge = new CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($this->customer_profile_id);
        $paymentProfile = new PaymentProfileType();
        $paymentProfile->setPaymentProfileId($this->customer_payment_profile);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new TransactionRequestType();
        $transactionRequestType->setTransactionType( "authCaptureTransaction"); 
        $transactionRequestType->setAmount(400);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest( $transactionRequestType);
        $controller = new CreateTransactionController($request);
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if ($response != null)
        {
          if($response->getMessages()->getResultCode() == "Ok")
          {
            $tresponse = $response->getTransactionResponse();
            
              if ($tresponse != null && $tresponse->getMessages() != null)   
            {
              info( " Transaction Response code : " . $tresponse->getResponseCode() . "\n");
              info( "Charge Customer Profile APPROVED  :" . "\n");
              info( " Charge Customer Profile AUTH CODE : " . $tresponse->getAuthCode() . "\n");
              info( " Charge Customer Profile TRANS ID  : " . $tresponse->getTransId() . "\n");
              info( " Code : " . $tresponse->getMessages()[0]->getCode() . "\n"); 
              info( " Description : " . $tresponse->getMessages()[0]->getDescription() . "\n");
            }
            else
            {
              info( "Transaction Failed \n");
              if($tresponse->getErrors() != null)
              {
                info( " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n");
                info( " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n");            
              }
            }
          }
          else
          {
            info( "Transaction Failed \n");
            $tresponse = $response->getTransactionResponse();
            if($tresponse != null && $tresponse->getErrors() != null)
            {
              info( " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n");
              info( " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n");                      
            }
            else
            {
              info( " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n");
              info( " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n");
            }
          }
        }
        else
        {
          info(  "No response returned \n");
        }

        $this->assertNotNull($response);
      }

}
