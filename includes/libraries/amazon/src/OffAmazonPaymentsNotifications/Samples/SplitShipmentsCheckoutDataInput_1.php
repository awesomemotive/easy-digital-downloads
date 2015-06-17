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
<title>Split Shipments Checkout Example</title>
</head>
<body>
<h1>Split Shipments Checkout Example</h1>

<h2>  
 <br/> This demonstrates a merchant use case where the order needs to be
 <br/> furfilled in multiple shipments, or where a single item is shipped
 <br/> in multiple shipments over time.
 </h2>

<form name="input" action="SplitShipmentsCheckoutDataInput_2.php" method="post">
Order Reference Id: <input type="text" name="orderReferenceId"/><br/><br/>
# of Shipments <font size="2"> (To make this sample simple, we have limited # of shipments to 3)</font>: 
<select name="totalShipments">
<option value="1" onselect="alert('selected');">1</option>
<option value="2">2</option>
<option value="3">3</option>
</select><br/><br/><br/>
<input type="submit" value="Submit"/>
</form>
</body>
</html>