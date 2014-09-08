# Payment Gateways

Hero includes many payment gateway libraries which will connect to your merchant account and allow you to process one-time and subscription payments securely and automatically online.

There are no setup fees for using these gateways with Hero, and setting them up in your control panel takes just seconds.  It is just a case of inputting your login/API credentials so that Hero can communicate with your gateway properly.

At present, the following payment gateways are supported:

* [PayPal Express Checkout](https://www.paypal.com/ca/mrb/pal=Q4XUN8HMLDQ2N) for PayPal Standard users
* [PayPal DirectPayment](https://www.paypal.com/ca/mrb/pal=Q4XUN8HMLDQ2N) for PayPal Pro users
* [Authorize.net](http://www.authorize.net/)
* [2Checkout](http://www.2checkout.com)
* [SagePay](https://support.protx.com/apply/default.aspx?PartnerID=D16D4B72-87D5-4E97-A743-B45078E146CB) for UK/European users
* [E-xact/VersaPay](http://ecommerce.versapay.com/) for Canadian users
* [Wirecard](http://www.wirecard.com/products/payment/credit-card-processing.html)
* [Pacnet](http://www.pacnetservices.com/index.php/apply/)
* [eWAY](https://www.eway.com.au/join/) for Australian users
* [FreshBooks](https://electricfunction.freshbooks.com/refer/www) for users who would prefer invoicing, not immediate billing
* An Offline/Cheque/Money Order gateway.

To configure any gateway, simply access *Configuration > Payment Gateways* in the control panel and select "Add New Gateway".  In this 1-step process, you will configure Hero to work with your gateway.  If a connection cannot be made, an error will be thrown.  *No other steps are necessary in order to integrate online payments with Hero*.

If you have multiple gateways, you can specify which gateway should be your *default* gateway.

## What are payment gateways?

If the following did not mean anything to you, you are likely new to the world of merchant accounts and payment gateways.  For the best introduction to payment gateways, visit [this Wikipedia article on Payment Gateways](http://en.wikipedia.org/wiki/Payment_gateway) or contact support to see which option is best for you.

All you need to know at this point is that it's easy to get setup taking online payments with Hero.  All of the difficult credit card processing, subscription monitoring (for expirations, missed payments, etc.) and security issues are taken care of automatically.  Even if you just have a [PayPal](https://www.paypal.com/ca/mrb/pal=Q4XUN8HMLDQ2N) account, you're halfway there!

## Is credit card information stored with Hero?

No.  Credit card information is *not* stored locally.  The local storage of credit card information is an unnecessary risk for any small- or medium-sized business and Hero mitigates this risk by using the native features of your payment gateway(s) to store all sensitive information.