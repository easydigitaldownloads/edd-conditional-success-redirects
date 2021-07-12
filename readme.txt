=== Easy Digital Downloads - Conditional Success Redirects ===

Allows per-product confirmation pages on successful purchases.

== Changelog ==

= 1.1.7 - July 12, 2021 =
* Refactor: Update plugin author name to "Sandhills Development, LLC."
* Dev: Compatibility with EDD 3.0.

= 1.1.6 =
* Fix: Redirect loop if EDD core success page URL matches the redirect URL

= 1.1.5 =
* Fix: Redirect loop on purchase confirmation for non PayPal Express transactions

= 1.1.4 =
* Fix: Redirect breaks PayPal Express purchase confirmation when using Recurring Payments

= 1.1.3 =
* Fix: Redirect not working when download purchased via PayPal buy now buttons

= 1.1.2 =
* Fix: Compatibility issue with EDD Recurring Payments
* Fix: Various typos
* Fix: Redirects weren't able to be deleted since the v1.1.1 update

= 1.1.1 =
* Fix: XSS vulnerability in query args

= 1.1 =
* New: edd_csr_redirect filter for creating custom redirects
* New: Support for the PayPal Express (PayPal Pro/Express extension) which requires customers to confirm their payment after arriving back from PayPal
* New: Activation script to check for the existence of Easy Digital Downloads
* New: Redirects can now be edited by clicking on the download titles
* New: Better internationalization function to allow easier translations
* Fix: Bulk delete redirects

= 1.0.3 =
* Fix: Incorrect parameters passed to EDD_License

= 1.0.2 =

* Fix: Removed out-dated EDD_License_Handler class

= 1.0.1 =

* New: Added support for off-site payment gateways such as PayPal that return the customer to your website after a successful purchase.

= 1.0 =

* First release.
