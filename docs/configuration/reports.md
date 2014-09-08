# Reports

With Hero, you can generate reports on the following site activity/data:

* Invoices
* Product Orders
* Subscriptions
* Expirations
* Cancellations
* Taxes Received
* Registrations
* Popular Content

Each report can be easily filtered by a number of criteria, such as *date*, *member*, or *product name* (depending on the report, of course).

Reports, like all data in the control panel, can be *exported as Comma Separated Value (CSV) files* with the click of a button ("Export").  These files are ready to be imported into Microsoft Excel and similar applications.

Here's an example of a CSV formatted report file of new subscriptions that illustrates the amount of data you have to make your business decisions with, or import into other applications:

```
id,user_id,user_username,user_first_name,user_last_name,user_email,gateway_id,date_created,amount,interval,start_date,end_date,last_charge_date,next_charge_date,cancel_date,number_occurrences,active,renewing_subscription_id,updating_subscription_id,card_last_four,plan_id,renew_link,cancel_link,update_cc_link,is_recurring,is_active,is_renewed,is_updated,plan_id,plan_type,plan_name,plan_amount,plan_interval
"23268","1001","admin","Brock","Ferguson","brock@example.com","11","10-Dec-2010 05:05am","5.65","30","10-Dec-2010 05:05am","09-Dec-2012 12:00am","10-Dec-2010 12:00am","09-Jan-2011 12:00am","n/a","24","1","0","0","0","1000","http://www.example.com/billing/subscriptions/renew/23268","http://www.example.com/users/cancel/23268","0","1","1","0","0","1000","paid","1 Month","9.99","30"
"23267","1065","mike","Mike","Chipper","mike@example.com","11","10-Dec-2010 03:30am","5.65","30","10-Dec-2010 03:30am","09-Dec-2012 12:00am","10-Dec-2010 12:00am","09-Jan-2011 12:00am","n/a","24","1","0","0","0","1000","http://www.example.com/billing/subscriptions/renew/23267","http://www.example.com/users/cancel/23267","0","1","1","0","0","1000","paid","1 Month","9.99","30"
"23266","1043","paul","Paul","Skiwosel","paul@example.com","11","9-Nov-2010 09:00pm","9.99","30","9-Nov-2010 09:00pm","11-Nov-2010 12:00am","10-Nov-2010 12:00am","0","11-Nov-2010 06:00am","1","0","0","0","0","1000","http://www.example.com/billing/subscriptions/renew/23266","http://www.example.com/users/cancel/23266","0","0","0","0","0","1000","paid","1 Month","9.99","30"
```

Of course, all of this data is available in much more pleasing fashion in the control panel.