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
<title>Cancellation Example</title>
</head>
<body>
<h1>Cancellation Example</h1>

<h2>This page demonstrates a merchant use case where the order needs to be
cancelled before the first cancelOrderReference has been performed
<br/><br/>
This is done using the CancelOrderReference call to cancel to order,
and can be called prior to the first cancelOrderReference has been performed.
<br/><br/>
If a cancelOrderReference has been performed, then the merchant will need to close
the order reference if there is an outstanding total and perform a
refund on existing cancelOrderReferenced amounts if required.</h2>

<br/><br/>

<form name="input" action="CancellationResult.php">
Order Reference Id: <input type="text" name="orderReferenceId"/>
<br/><br/>
<input type="submit" value="Run Cancellation Sample"/>
</form>
</body>
</html>