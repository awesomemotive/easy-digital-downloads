<?php 

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';

/**
 * AddressConsentSample shows how to use an  
 * access token in order to return additional information about the buyer
 * attached to a payment contract.  
 *
 * This example is for US based customers only.
 * 
 * Note that the token requires needs to be decoded before it can be passed
 * into the service call
 * 
 * The sample is run by passing in a draft order reference and the access token
 * assocaited with this order reference object..
 */
class AddressConsentSample
{
	private $_sellerId;
        private $_service;
        private $_amazonOrderReferenceId;
    
	/**
	 * Create a new instance of the Address consent sample
	 *
	 * @param OffAmazonPaymentsService_Client $service                 instance of the service
	 *                                                                 client
	 * @param string                          $amazonOrderReferenceId an order reference object in
	 *                                                                 draft state to use in
	 *                                                                 the example
	 *
	 * @return new PayWithAmazonAddressConsentSample
	 */
	public function __construct($service, $amazonOrderReferenceId)
	{
		$this->_service = $service;
		$this->_amazonOrderReferenceId = $amazonOrderReferenceId;
		$this->_sellerId = $this->_service->getMerchantValues()->getMerchantId();
	}
	
	/**
	 * Validate that the order reference is in the draft state
	 *
	 * @param OffAmazonPayments_Model_GetOrderReferenceDetailsResponse in an unverified state
	 *
	 * @return void
	 * @throws ErrorException if the state does not match the expected state
	 */
	public function validateOrderReferenceIsInACorrectState($getOrderReferenceDetailsResponse)
	{
		validateOrderReferenceIsInACorrectState(
			$getOrderReferenceDetailsResponse->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails(),
			"DRAFT"
		);
	}
	
	/**
	 * Use the order reference object to query the order information, including
	 * the current physical delivery address as selected by the buyer
	 *
	 * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse service response
	 */
	public function getOrderReferenceDetails($addressConsentToken = null)
	{
		$getOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
		$getOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
		$getOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
		
		if (is_null($addressConsentToken) == FALSE) {
			$decodedToken = urldecode($addressConsentToken);
			$getOrderReferenceDetailsRequest->setAddressConsentToken($decodedToken);
		}
	
		return $this->_service->getOrderReferenceDetails($getOrderReferenceDetailsRequest);
	}
}
?>
