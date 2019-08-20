#  Magento 2 PayPal Instalments

PayPal Instalments is a solution where PayPal offers your customers the possibility to pay in instalments as individual payment option on the payment selection page. This option is perfect for higher priced items.

Customers are able to choose between several payment plans when they reach a requested minimum value.

No matter which payment plan the customer chooses to pay, it is always a single PayPal transaction for merchant, including all resulting advantages like Seller Protection and easy refund.

## Installation

To install the Magento 2 PayPal Instalments extension please add our repository to your Magento _composer.json_.

    {
        "repositories": [
                {
                    "url": "git@github.com:i-ways/magento2-paypal-instalments.git",
                    "type": "git"
                }
            ]
    }

After you added our repository you need to require our module.

There are to possibilities:

1. Run the command _composer require iways/module-pay-pal-instalments_
2. Add it manually to your _composer.json_


    "require": {
           "iways/module-pay-pal-instalments": "~1.0"
    }

## Enable our module in Magento

To enable our module via Magento 2 CLI go to your Magento root and run:

    bin/magento module:enable --clear-static-content Iways_PayPalInstalments


To initialize the Database updates you must run following command afterwards:

    bin/magento setup:upgrade

The Magento 2 PayPal Instalments module should now be installed and ready to use.

## Issues
Please use our Servicedesk at: https://support.i-ways.net/hc/de
