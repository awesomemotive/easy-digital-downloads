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
    <title>Login page</title>
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
       <div id="AmazonPayButton"></div>
       <script type='text/javascript'>
                OffAmazonPayments.Button("AmazonPayButton", "<?php print $merchantValues->getMerchantId(); ?>", {
                    type: "Pay",
                    authorization: function() {
                        var path = location.pathname.replace(/[^\/]+.php/, "address.php");
                        amazon.Login.authorize({scope: "profile payments:widget payments:shipping_address"}, "https://" + location.host + path);       
                    },    
                    onError: function(error) {
                        alert(error.getErrorCode() + ": " + error.getErrorMessage());
                    }
                });
       </script>
       <p>Sign in with a test buyer account to redirect to the address widget page</p>
    </body>
</html>
