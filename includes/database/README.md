# Custom Tables #

### The schema for each custom table is described below

## The Adjustments Table:
This table stores things that can be used to adjust an order. This includes things like discount codes and tax rates. Note that these things do not represent an adjustment that was used for a specific order. To see a record of those, look at the order_adjustments table.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| parent  | This column does not currently serve any purpose, but might be used in the future. |
| name | The name of the adjustment. For discount codes, this is the name of the discount. For tax rates, this is the name of the country for which the tax rate applies. All tax rates in a country share the same value in this column. For example, all tax rates in Canada will use "CA" here. |
| code | For discount codes, this is the value which users can enter upon checkout. For tax rates, this column is blank. |
| status | A string which indicates the status of the adjustment. For tax rates this will be "active" or "inactive". For discount codes, this will be "active" even if the discount has expired, and this is intentional. |
| type | The type of adjustment this is. For discounts this is "discount". For tax rates this is "tax_rate". |
| scope  | A value which defines how the adjustment can be used. For discount codes the value is always "global". For tax rates, the values can be "country", or "region". |
| amount_type  | The type of amount this is, either "percent" or "flat", with flat being a monetary value. |
| amount  | The amount of the adjustment, stored as a decimal and representing either a percent or flat amount. |
| description  | For tax rates, this is the "state" or "province". For example, for Ontario (the province in Canada), this value will be "ON". For discount codes this column is not currently used. |
| max_uses  | An integer indicating the maximum number of times this adjustment can be used. |
| use_count  | An integer indicating the number of times this adjustment has been used. |
| once_per_customer | A boolean value in the form of 1 (for true) and 0 (for false). If 1, this adjustment should only be used once per customer. If 0, there is no limit to the number of times it can be used. |
| min_charge_amount | For discount codes, this is the minimum amount required in the cart prior to this adjustment being applied. For tax rates this is typically not applicable. |
| start_date | For discount codes, this is the date when a discount code will begin to work. This is not applicable to tax rates. |
| end_date | For discount codes, this is the date when a discount code will cease to work. This is not applicable to tax rates. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Adjustment Meta Table:
This table stores various and custom/extra information about an adjustment.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_adjustment_id  | The id of the adjustment to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Customer Addresses Table:
This table stores the addresses of customers.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| customer_id  | The id of the customer to which this address belongs. This id corresponds to the id column in the Customers table. |
| name  | The name of the person connected to this physical address. |
| type  | The type of address this row represents. Typical values are "billing" and "shipping". |
| status | This currently does not serve any purpose, but might in the future. |
| address | The first line of a physical address. |
| address2 | The second line of a physical address. |
| city | The city of a physical address. |
| region | A 2 letter representation of the region/state/province. For example, in the US, this is the "State". In Canada, this is the "Province". |
| postal_code | The postal code for a physical address. It accepts any string. |
| country | The 2 letter representation of a country for a physical address. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Customer Email Addresses Table:
This table stores the email addresses of customers.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| customer_id  | The id of the customer to which this email address belongs. This id corresponds to the id column in the Customers table. |
| type  | A string representing the priority (or importance) of this email address. Typical values are "primary" and "secondary". |
| status | This does not serve any purpose at this time, but might in the future. |
| email | An email address, which is connected to a customer. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Customer Meta Table:
This table stores various and custom/extra information about a customer.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_customer_id  | The id of the customer to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Customers Table:
This table stores customers. Customers are people who have made a purchase in your store.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. This is also the id of the customer. |
| user_id  | The id of the WordPress user which is linked to this customer. The same real-world person owns both, the WP user, and the EDD customer.  |
| email  | The primary email address of this customer. This value will typically match whatever the "primary" email is in the customer emails table in the "type" column. |
| name | This is the customer's name, and includes their first and last name together in a single string. |
| status | Currently, this stores the word "active" for all customers, until such time as functionality for "inactive" customers gets added to EDD. |
| purchase_value | This is the total amount of money this customer has paid. |
| purchase_count | This is the total number of purchases this customer has initiated. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Log Meta Table:
This table stores various and custom/extra information about a log.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_log_id  | The id of the log to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Logs Table:
This table stores general-purpose logs, which are typically records of events happening, like a payment being completed or refunded. Note that logs are intended to be "created by a machine" as opposed to "created by a human". If you are writing code that automatically logs something, make it a log in this table. If you are writing code that creates a UI which allows a human being to write a note, store it in the notes table.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. |
| object_id  | The id of the thing to which this log relates. For example, the id of the "order" or the "discount code". |
| object_type  | This describes the type of thing this log is for. For example, "order" indicates this log is for an order. |
| user_id | This is the ID of the WordPress user who created this log. |
| type | This column indicates the type of log this is. For example, the word "refund" would indicate that this log is about a refund taking place. |
| title | This is the title of the log. Typically this is a short sentence describing what the log is about. |
| content | This is a longer description of the log. Typically this will be a sentence or paragraph describing the event which took place. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Logs API Requests Table:
Every time a request is made to the EDD REST API, that request is logged in this table.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. |
| user_id | This stores the ID of the WordPress user who created this log. |
| api_key | This stores the api key used to make this API request. |
| token | This stores the token used to make this API request. |
| version | This stores the version of the API for which this call was made. |
| request | This stores what the URL variables were when the request was made. |
| error | Errors that took place during the call. Defaults to be empty. |
| ip | The IP address of the machine which made the request. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Log API Request Meta Table:
This table stores various and custom/extra information about an API Request Log.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_logs_api_request_id  | The id of the api request log to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Logs File Downloads Table:
Every time a deliverable file is downloaded via EDD, it is logged in this table.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. |
| product_id | The ID of the EDD product whose file was downloaded. |
| file_id | The id of the file being downloaded. This ID comes from the files attached to an EDD product. |
| order_id | The ID of the order which is enabling this download to take place.  |
| price_id | The variable price ID which was purchased, and which enabled this download to take place. 0 if the product is not variably-priced. |
| customer_id | The ID of the customer who downloaded this file. |
| ip | The IP address of the machine which made the request to download the file. |
| user_agent | The name/user-agent of the browser which was used to download the file. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Log File Download Meta Table:
This table stores various and custom/extra information about a file download log.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_logs_file_download_id  | The id of the file download log to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Note Meta Table:
This table stores various and custom/extra information about a note.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_note_id  | The id of the note to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Notes Table:
This table is for storing notes created by human beings, as opposed to notes/logs/data created automatically by code or automatic code events happening. Note that logs are intended to be "created by a machine" as opposed to "created by a human". If you are writing code that automatically logs something, make it a log in the logs table. If you are writing code that creates a UI which allows a human being to write a note, store it in the notes table here.

This table's data is intended to be mutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. |
| object_id  | The id of the thing to which this note relates. For example, the id of the "order" for which this note was created. |
| object_type  | This describes the type of thing this note is for. For example, "order" indicates this note is for/about an order. |
| user_id | This is the ID of the WordPress user who created this note. |
| content | This is the main/unique content of the note, the note itself. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Order Addresses Table:
When a user completes a purchase/order and enters their address on the checkout page, that address is stored in a new row here. This allows the address attached to the order to remain what it was at the time of purchase, regardless of whether the customer changes their address in the future. This is because the address attached to an order should remain unchanged forever. These addresses should be considered immutable. Even if a customer has 2 orders and uses the exact same address for each order, a new row will be created here, unique to that order, despite possibly being identical to a previous row.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. |
| order_id | The id of the order to which this address is attached. |
| name  | The name of the person attached to this physical address. |
| type  | The type of address this row represents. Typical values are "billing" and "shipping". |
| address | The first line of a physical address. |
| address2 | The second line of a physical address. |
| city | The city of a physical address. |
| region | A 2 letter representation of the region/state/province. For example, in the US, this is the "State". In Canada, this is the "Province".  |
| postal_code | The postal code for a physical address. It accepts any string. |
| country | The 2 letter representation of a country for a physical address. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Order Adjustment Meta Table:
 This table stores various and custom/extra information about an order adjustment.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_order_adjustment_id  | The id of the adjustment to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Order Adjustments Table:
This table stores things that adjusted the total amount of a specific order, or the amount of an item within an order. This includes things like discount codes and tax rates.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| parent | The ID of another order adjustment which is considered to be the parent of this adjustment. This is used for adjustments attached to refunds. The parent references the ID of the original order adjustment that was refunded. |
| object_id | The ID of the row that this row adjusted the amount/cost of. This is typically an order (in the orders table) or an order_item (in the order_items table). The type of object is indicated in the object_type column. |
| object_type | This typically indicates the EDD custom table that the object_id value can be found within, and to which row within that table this row relates. For example, the orders table (indicated by the word "order") or the order_items table (indicated by the word "order_item"). |
| type_id | This value indicates the row ID in the adjustments table from which this order adjustment originated. For example, if this value is "25", go to the adjustments table and look at the row with the ID "25" to see the corresponding adjustment. |
| type | A string which indicates the type of adjustment this is. Typically this is something like "fee", "tax_rate", or "discount". |
| type_key | The fees API allows for customizing the array key value for a given fee. This can be a string or numeric. This "fee ID" is stored as the type_key, as it represents the fee's key in the 2.x array. |
| description  | A description of the order adjustment. |
| subtotal  | If the amount type for this row is a percentage, the value in this column is intentionally unused. Otherwise, it stores the monetary amount of this adjustment before tax. For example, if you have a $10 shipping fee with a 10% tax rate, $10 is stored in this column. |
| tax  | If the object_type for this row is a percentage, the value in this column is intentionally unused. Otherwise, it stores the monetary amount of the tax on this adjustment. For example, if you have a $10 shipping fee, the tax on the $10 is stored in this column. |
| total  | Like the subtotal and tax columns, this column stores a monetary amount sometimes, and at others stores a percentage rate. To determine the type of amount being stored, percentage vs flat amount, trace the row back to the adjustments table using the type_id value from this table, and check the amount_type column's value from the adjustments table. For example, if you have a $10 shipping fee with a 10% tax rate, $11 is stored in this column. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Order Item Meta Table:
This table stores various and custom/extra information about an order item.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_order_item_id  | The id of the order item to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Order Items Table:
This table stores items (or "products", also known as "downloads" in EDD) that were part of an order. It also stores various data about the items, like the tax that was on the item,.

One way to think about this is that a "for-sale thing" is called a "product" when in an un-purchased state, and called an "item" when in a purchased state.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| parent | The ID of another order item which is considered to be the parent of this item. This is used for order items attached to refunds. The parent references the ID of the original order item that was refunded. |
| order_id | The ID of the order to which this item belongs. |
| product_id | The ID of the product which was purchased. |
| product_name | This is what the name of the product was at the time of this purchase. |
| price_id | This is the ID of the variable price which was purchased. |
| price_name | This is what the name of the variable price was at the time of this purchase. |
| cart_index  | This is the position at which this item was sitting in the cart when this order took place, starting at 0 for the first position. |
| type | This indicates the type of product that this item is. In its current form, all things sold in EDD have the type of "download", and thus does not currently have any functional relevance. This is here to enable possible future changes only. |
| status | This indicates the status of this item in regards to purchase completion. Typical values include (but are not limited to) "completed", and "refunded". When set to "inherit", it will inherit the status of the order to which it belongs. When set to anything other than "inherit" it will override the status of the order, but only for this item. |
| quantity | A single item in the cart can have a quantity. This indicates that quantity. Through this column's data, a single order_item can actually represent multiple items, and the values in the subtotal and total columns reflect that quantity. |
| amount | This is what the unadjusted price of this item was at the time of purchase. It does not include tax, discounts, fees, or any other price adjusters. |
| subtotal  | This is the cost of the line item in the cart including quantity, but not including tax, discounts, fees, or any other price adjusters. |
| discount  | This column stores the portion of the discount from the total that applied directly to this item. |
| tax  | This column stores the portion of tax from the total that applied directly to this item. |
| total  | This contains the total cost of this item, including quantity, taxes, item-specific discounts (but not cart-wide discounts). *Note that this amount does not include any fees in the cart that are specific to this item. For example, a shipping fee that exists because of this item is not included in the total found in this column. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Order Transactions Table:
Where a transaction represents an actual exchange of money, this table stores all of the transactions that were part of an order. Some orders will contain multiple transactions. For example, many payment gateways (for example: Stripe and PayPal) do not allow the purchasing of multiple recurring-enabled items in a single transaction. This is because 1 item could be monthly, and another could yearly. Each item will create a different transaction on the customer's credit card, and will show up separately on the customer's credit card statement. This table helps you to keep track of which transactions were part of which order.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments.  |
| object_id | The ID of the row to which this transaction belongs. Typically this will be the ID of an order in the orders table. |
| object_type | The table that the object_id value can be found within, and to which row within that table this row relates. For example, the orders table  is indicated by the word "order", and is typically the value that will be found here. |
| transaction_id | The ID of the transaction, which originates from the payment gateway. |
| gateway | The name of the payment gateway where this transaction took place. |
| status | The status of this transaction. For example, if complete, the status will be set to "complete". |
| total | The total amount of this transaction. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |

## The Order Meta Table:
This table stores various and custom/extra information about an order.

This table's data is intended to be immutable.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| meta_id | The unique id of the row, which auto increments.  |
| edd_order_id  | The id of the order item to which this row relates. |
| meta_key | The reference key (like a variable name) of the data in question. |
| meta_value | The value. This can be anything needed as its purpose is for anything extra. |

## The Orders Table:
This table stores orders (called "payments" prior to EDD 3.0). It also stores various data about the order, like the customer ID, the email entered by the customer at checkout, the IP address of the machine where checkout was completed, and more. See table below for full breakdown of each column.

This table's data is intended to be immutable. However, some column data is also intended to be mutable. The user_id customer_id, and email column values will change if an order is re-assigned to a new customer.

| Table Column  | Table Column's Description |
| ------------- | ------------- |
| id | The unique id of the row, which auto increments. This also serves as the id of the order itself. |
| parent | The ID of another order which is considered to be the parent of this order. This is used in scenarios like refund orders, which are automatically generated when a refund takes place. Refund orders use this column to refer to the original order where the item being refunded was originally purchased. Another scenario where this is used is for renewal payments done through the EDD Recurring Payments extension. Each renewal payment will use this column to indicate which order was the one where the customer originally initiated the subscription. |
| order_number | This column serves several different purposes: <br><br> 1. By default, it will be blank for every order (except "refund" orders). <br><br> 2. If the order in question is a "refund" order, this will contain a string in this format: "ORIGINAL_ORDER_NUMBER-R-THE_NUMBER_OF_REFUNDS_IN_THAT_ORDER". So if it is the 2nd refund from order #1, it will be "1-R-2".  <br><br> 3. If you have "Sequential Order Numbers" enabled in your EDD settings, this column will be populated by the value determined by your settings for that.<br><br> 4. If the order in question is a refund for a "Sequentially Ordered" order, the format is the same as for "Non-Sequentially Ordered" orders, but it is important to note that the ORIGINAL_ORDER_NUMBER value will be the value from the "id" column of the original order, not the "order_number" column. <br><br> 5. Extensions may modify the way this column works. For example, the "Advanced Sequential Order Numbers" extension for EDD will put its own value in this column, overriding the values from EDD core. |
| status | This column has 2 purposes:<br><br> 1) It identifies the financial/accounting status of the order. <br>    a) If the transaction(s) for the order have completed successfully, this value here will be "complete". <br>    b) If the transaction(s) for the order have not yet completed successfully, this value here will be "pending". <br>    c) If the transaction(s) for the order have not completed successfully and it has been 7 days, this value here will be "abandoned". <br>    d) If the transaction(s) for the order failed at the payment gateway (for example, insufficient funds), this will be set to "failed". <br>    e) If this order has been partially refunded, the status of the order currently remains set to "complete". <br>    f) If all of the items in this order have been refunded, this value will be "refunded". <br><br>2) It identifies if the order is in the trash. If the order has been put in the trash, the financial status is no longer stored here, but gets moved to the order_meta table with the key "pre_trash_status". The value in this column will then be "trash". |
| type | The type of order this is. Typical values are "sale" or "refund". |
| user_id | The ID of the user currently attached to this order. Note that this column is mutable and will change if the user attached to the EDD customer changes, or if the customer attached to an order changes. |
| customer_id | The ID of the customer currently attached to this order. Note that this column is mutable and will change if the customer attached to the EDD order changes, or if the user attached to a customer changes. |
| email | The email address currently attached to this order. Note that this column is mutable and will change if the customer attached to the EDD order changes, or if the customer's email is updated. |
| ip | The IP address of the machine on which this order was completed. |
| gateway | A string representing the payment gateway which was used to complete the payments on this order. |
| mode | This stores whether the order was done in test mode or live mode. |
| currency | The 3 letter currency code which this order used/will-use.  |
| payment_key | A unique key representing this payment. This key is generated by combining a few different values about this order, like the email address, the date, an optional auth key which can be defined as a constant, and a unique id generated by the uniqid function in PHP. See class-edd-payment.php for the full breakdown of how this is generated. |
| subtotal | This is the amount of the items in the cart added together. It does not include any taxes or discounts. Note: Fees are considered to be both line items and adjustments. In relation to the orders table, fees are treated as line items, and are thus included in the subtotal. But note that they are the only "adjustments" that are included in the subtotal, as other adjustments are not included in the subtotal. |
| discount | This is the total amount of discount(s) that were applied to the order. |
| tax | This is the total amount of the tax that was applied to the order. |
| total | This is the total amount of the order. |
| date_created | The date this row was created. |
| date_modified | The date this row was last modified. |
| uuid | A unique identifying string representing this row. |
