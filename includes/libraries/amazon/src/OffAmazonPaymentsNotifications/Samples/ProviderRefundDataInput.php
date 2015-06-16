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
<title>Provider Refund Example</title>
</head>
<body>
<h1>Provider Refund Example</h1>
<br/><br/>
<h2>This page demonstrates the scenario where merchant needs 
to perform a refund on a previously captured amount 
for a closed order reference, and then reverse the credit from provider</h2>
<br/><br/>
<form name="input" action="ProviderRefundResult.php">
Order Reference Id: <input type="text" name="orderReferenceId"/><br/><br/>
Amazon Capture Id: <input type="text" name="amazonCaptureId"/><br/><br/>
Refund Amount: <input type="text" name="refundAmount"/><br/><br/>
Provider Id:<input type="text" name="providerId"/><br/><br/>
ProviderCreditReversal Amount <input type="text" name="creditReversalAmount"/><br/><br/>
<br/><br/>
<input type="submit" value="Run Sample"/>
</form>
</body>
</html>