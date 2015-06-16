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

<html>
<head>
<title>Address Consent Sample</title>
</head>
<body>
	<div>
		<h1>Address Consent Example</h1>
		<br />
		<p>This example shows the difference in GetOrderRefenceDetails
			response when using the AddressConsent token field</p>

		<p>Enter an order refererence for a order that is in the draft status,
			along with the associated access token from the buyer session</p>
	</div>

	<div>
		<form name="input" action="AddressConsentSampleResult.php">
			OrderReferenceId: <input type="text" name="orderReferenceId"/><br/><br/>
			Access Token: <input type="text" name="accessToken"/><br/><br/>
			<input type="submit" value="Submit"/>
		</form>
	</div>

</body>
</html>
