<!-- 
/* *****************************************************************************
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
    <title>Automatic Payment Simple Checkout Example</title>
</head>
<body>
	<h1>Automatic Payment Simple Checkout Example</h1>

	<h2>This page will demonstrates a typical use case of automatic
		payment. With a buyer consented billing agreement, the cost of each
		payment is calculated based on the buyer selected address. And then
		three payments are performed on this billing agreement.</h2>

	<form name="input" action="AutomaticPaymentsSimpleCheckoutResult.php">
		Buyer Consented Billing Agreement Id: 
		<input type="text" name="billingAgreementId" /><br /> 
		Shipping speed: 
		<select name="shipping">
			<option value="1">Overnight</option>
			<option value="2">2-day shipping</option>
			<option value="3">5-day shipping</option>
		</select><br /> 
		Amount of Each Payment: 
		<input type="text"name="paymentAmount" /><br /> 
		3 payments of above amount will be performed in this example.<br /> 
		<input type="submit" value="Submit" />
	</form>
</body>
</html>