# Woocommerce Xero Stripe Fees
This is a Plugin for Wordpress using **Woocommerce** with the **Woocommerce Xero** Extension and **Stripe**

It Adds a stripe fee to your Xero invoices / payments for easier reconciliation 

**Please note this is a work in progress and probably has a lot of bugs in it. The code is quick and dirty so feel free to add / change to it.**
### Features

  - Adds a Stripe Fee to the invoice send to Xero
  - Per product Xero Account Codes (including variations)
  - Removes Stripe Fee from payments send to Xero
  - Multiple Stripe Countries supported
  
### Installation

1. Download the latest release and add the plugin using the Wordpress upload function
2. Activate the plugin in Wordpress
3. Add the Xero 'Stripe Fee' account code in ```Woocommerce > Xero``` options page (at the bottom)
4. Test to see if it works (see debug below)

### Requirements

This Plugin requires the following plugins to be active in Wordpress

| Plugin | Link |
| ------ | ------ |
| Woocommerce 3.6 or above | https://woocommerce.com |
| Woocommerce Xero (tested with 1.7.33) | https://woocommerce.com/products/xero/ |

### Bugs

Please create a new issue if you are able to reproduce your bug

### Troubleshooting

Having issues with invoices being send to Xero? 
1. Deactivate this plugin 
2. Try a test order and see if that gets send to Xero
3. If you get an error its something unrelated to this plugin
4. If it works please enable the debug option and try another test order
5. Submit your woocommerce xero logs as an issue on github

### Debug

Enable debug under ```Woocommerce > Xero```

Logs are created in the ```wp-content/uploads/wc-logs``` the filename should start with **xero** followed by a bunch of numbers with the extension of **.log**

### Development

Want to contribute? Great!

Feel free to post an issue or create a pull request. 


### Security

Found a security issue, please email hello@itchef.nz instead of opening an issue

### Are you finding this useful?

Using this plugin or some of the code? Feeling generous and want to say thanks? 
You can <a href='https://ko-fi.com/A6552UEK' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi2.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>