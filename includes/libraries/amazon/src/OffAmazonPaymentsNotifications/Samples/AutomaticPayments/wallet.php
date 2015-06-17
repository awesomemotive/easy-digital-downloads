<!-- 
/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */
 -->
<!DOCTYPE html>
<?php
    require_once realpath ( dirname ( __FILE__ ) . "/../.config.inc.php" );
    require_once ("OffAmazonPaymentsService/Client.php");
    $client = new OffAmazonPaymentsService_Client ();
    $merchantValues = $client->getMerchantValues ();
?>

<html>
    <head>
        <title>Wallet page</title>
        <style>
            #AmazonWalletWidget, #AmazonConsentWidget {width: 400px; height: 228px;}
        </style>
        <script type="text/javascript">
                window.onAmazonLoginReady = function () {
                    amazon.Login.setClientId('<?php print $merchantValues->getClientId(); ?>');
                };
      	</script>
        <script type="text/javascript"
    	   src=<?php print "'" . $merchantValues->getWidgetUrl() . "'"; ?>>
    	</script>
    </head>
    <body>
    	<div id="AmazonWalletWidget"></div>
    	<div id="AmazonConsentWidget"></div>
    	<div id="SessionInformation"></div>
    
    	<script type='text/javascript'>
            function getParamFromQueryString(name, url) {
                var regexString = "[\\?&]" + name + "=([^&#]*)";
                var regex = new RegExp(regexString);
                var results = regex.exec(url);
    
                var result = null;
    
                if (results != null && results.length >= 2 && results[1] != null ) {
                    var result = results[1].replace("?" + name);
                }
    
                return result;
            }
    
            var url = window.location.href;
            var session = getParamFromQueryString("session", url);
            var access_token = getParamFromQueryString("access_token", url);
    
            if (session == null && access_token == null) {
                alert("Missing query string parameters from request, verify that session & access_token are present.");
            } else {
                document.getElementById("SessionInformation").innerHTML = "Billing Agreement Number: " + session + "<p> Access Token : " + access_token;
            }
    
            new OffAmazonPayments.Widgets.Wallet({
                sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?>,
                amazonBillingAgreementId: session,
                displayMode: 'Edit',
                design : {
                    designMode : 'responsive'
                },
                onPaymentSelect: function (billingAgreement) {
    				// This is to trigger when a valid payment instrument is selected
                },
                onError: function (error) {
                    alert(error.getErrorCode() + ": " + error.getErrorMessage());
                }
            }).bind("AmazonWalletWidget");
    
            new OffAmazonPayments.Widgets.Consent({
            	  sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?>,
            	  // amazonBillingAgreementId obtained from createBillingAgreement
            	  amazonBillingAgreementId: session,
                  design : {
                    designMode : 'responsive'
                  },
                  onRender: function(billingAgreementConsentStatus){
            	    // Called after widget renders
            	       buyerBillingAgreementConsentStatus =
                	       billingAgreementConsentStatus.getConsentStatus();
            	    // getConsentStatus returns true or false
            	    // true – checkbox is selected
            	  },// false – checkbox is unselected - default
            	  onConsent: function(billingAgreementConsentStatus) {
                	  buyerBillingAgreementConsentStatus =
                    	  billingAgreementConsentStatus.getConsentStatus();
            	    // getConsentStatus returns true or false
            	    // true – checkbox is selected – buyer has consented
            	    // false – checkbox is unselected – buyer has not consented
            	    // Replace this code with the action that you want to perform
            	    // after the consent checkbox is selected/unselected.
            	   },
            	  onError: function(error) {
            	   }// your error handling code
            	}).bind("AmazonConsentWidget");
        </script>
    </body>
</html>
