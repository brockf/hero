# Security

Hero is set apart from its competitors by its handling of ecommerce activity such as product sales, subscription sales, and downloadable product sales.  However, if you are using these features, it is very important that you secure your website properly.  This guide will cover some essential security procedures:

## SSL Certificates

An SSL certificate is a purchasable item that you install on your web server.  When enabled, it will allow users to visit `*https://*www.example.com` and have all transmissions between their computer and your web server securely encrypted.  This negates the risk of a malicious user intercepting sensitive information like credit card details.

Hero can require that users access your site via HTTPS secure connections in sensitive areas of your website, such as during the checkout process or when managing their account.  In order to have Hero force secure access, you simply need to go to *Configuration > Settings* in the control panel and toggle the "force_https" setting to "On".

> If you do not have an SSL certificate installed and users access the HTTPS secure version of your site in this manner, they will receive an error.

SSL certificates are not part of Hero.  They are something that your web host or system administrator must setup for you.  Or, if you are familiar with your web hosting control panel and have some experience in this area, you can likely purchase and install an SSL certificate yourself.

Recommended distributors of SSL certificates are:

* [SSLMatic RapidSSL Certificates](http://www.sslmatic.com)
* [GeoTrust](http://www.geotrust.com)
* [VeriSign](http://www.verisign.com)

## Secure Passwords

Your FTP server access and control panel should not have simple passwords.  If your website is on the internet, it is inevitable that malicious users **will** attempt to break into these areas of your website.

*Although credit card information is not stored locally and is thus not accessible via your control panel or FTP access*, the invader can do a lot of damage to your site (including deleting all files from it).

You can secure your site and business against malicious access attempts by using a password with the following characteristics:

* Avoid common passwords like "qwerty", "password", or "123456".
* Use a combination of punctuation, numbers, capitals, and lowercase letters.
* Do not share your password with anyone, or use it on other websites that you can't trust.