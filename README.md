# WoocommerceXeroStripe
This is a MU-Plugin for Wordpress using Woocommerce with the Woocommerce Xero Extension and Stripe

**Please note this is a work in progress and probably has a lot of bugs in it. The code is quick and dirty so feel free to add / change to it.**
### Features

  - Adds a Stripe Fee to the invoice send to Xero
  - NZ GST Support

### Installation

1. Edit the varible ```$xero_fees_account``` to specify your Xero Stripe Fee Account Code
2. Upload the .php file to ```wp-content/mu-plugins/``` (if the directory does not exist create it)
3. Test to see if it works (see debug below)

### Requirements

This MU-Plugin requires the following plugins to be active in Wordpress

| Plugin | Link |
| ------ | ------ |
| Woocommerce 3.0 or above | https://woocommerce.com |
| Woocommerce Xero (tested with 1.7.9) | https://woocommerce.com/products/xero/ |

### Bugs

[ ] Seems to have issues with just 1 product (add atleast two for now)

### Debug

If it does not seem to work ensure you can see the plugin under the 'Must Use' section

Logs are created in the ```wp-content/uploads/wc-logs``` the filename should start with **log** followed by a bunch of numbers with the extension of **.log**

### Development

Want to contribute? Great!

Feel free to post an issue or create a merge request. 
