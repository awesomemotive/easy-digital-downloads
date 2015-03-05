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
<title>Reverse Provider Credit Example</title>
</head>
<body>
<h1>Reverse Provider Credit Example</h1>

<h2>This page will perform a reverse provider credit that demonstrates 
transaction required to transfer the funds from provider to
seller.</h2>

<form name="input" action="ReverseProviderCreditResult.php">
Amazon Provider Credit Id:<input type="text" name="providerCreditId"/><br/>
Credit Reversal Amount: <input type="text" name="creditReversalAmount"/><br/>
<input type="submit" value="Submit"/>
</form>
</body>
</html>