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
<title>Provider Checkout Example</title>
</head>
<body>
<h1>Provider Checkout Example</h1>

<h2>This page will perform a simple checkout with provider credit that
		demonstrates using a tax calculations based on the buyer address to
		determine an order cost, then perform all transactions required to
		transfer the funds from buyer to seller, and seller to provider.
</h2>

<form name="input" action="ProviderCheckoutResult.php">
Order Reference Id: <input type="text" name="orderReferenceId"/><br/>
Order total: <input type="text" name="orderTotal"/><br/>
Shipping speed: 
<select name="shipping">
<option value="1">Overnight</option>
<option value="2">2-day shipping</option>
<option value="3">5-day shipping</option>
</select><br/>
Authorization Type: 
<select name ="authOption">
<option value ="1">Regular Authorization (Asynchronous Response) [Default]</option>
<option value ="2">Fast Authorization (Synchronous Response)</option>
</select><br/>
Provider Id: <input type="text" name="providerId"/><br/>
ProviderCredit Amount: <input type="text" name="providerCreditAmount"/><br/>
<input type="submit" value="Submit"/>
</form>
</body>
</html>