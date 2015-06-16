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
<?php 
    require_once realpath(dirname(__FILE__) . "/../.config.inc.php");
    require_once("OffAmazonPaymentsService/Client.php");
    $client = new OffAmazonPaymentsService_Client();
    $merchantValues = $client->getMerchantValues();
 ?>
 
<!DOCTYPE html>
<html>
    <head>
        <title>Address page</title>
        <style>
            #AmazonAddressWidget {width: 400px; height: 228px;}
        </style>
        <script type="text/javascript">
            window.onAmazonLoginReady = function () {
                amazon.Login.setClientId('<?php print $merchantValues->getClientId(); ?>');
            };
        </script>
        <script type="text/javascript" src=<?php print "'" . $merchantValues->getWidgetUrl() . "'"; ?> ></script>
    </head>
    <body>
        <div id="AmazonAddressWidget"></div>
 
        <p>Click <a id="WalletLink" href="">here</a> to go to the 
        wallet page once you have completed the signin</p>
        
        <script type='text/javascript' >
            function getParamFromQueryString(name, url) {
                var regexString = "[\\?&]" + name + "=([^&#]*)";
                var regex = new RegExp(regexString);
                var results = regex.exec(url);
    
                var result = null;
    
                if (results != null && results.length >= 2 && results[1] != null) {
                    var result = results[1].replace("?" + name);
                }
    
                return result;
            }
    
            var url = window.location.href;
            var session = getParamFromQueryString("session", url);
            var access_token = getParamFromQueryString("access_token", url);

            if (session == null && access_token == null) {
                alert("Missing query string parameters from request, verify that session & access_token are present.");
            }
            
            new OffAmazonPayments.Widgets.AddressBook({
                sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?>,
                displayMode: 'Edit',
                agreementType: 'BillingAgreement',
                onBillingAgreementCreate: function(billingAgreement) {
                	session = billingAgreement.getAmazonBillingAgreementId();
                	document.getElementById("WalletLink").href = 'wallet.php' + 
                    '?session=' + session + '&access_token=' + access_token; 	
                },
                design : {
                    designMode : 'responsive'
                },
                onAddressSelect: function(billingAgreement) { 
              		//This is to trigger when a vaid shipping address is selected
                },
                onError: function(error) {
                    alert(error.getErrorCode() + ": " + error.getErrorMessage());
                }
            }).bind("AmazonAddressWidget");
        </script>
    </body>
</html>
