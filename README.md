# Webbhuset GeoIP

## Installation
This module uses the MaxMind GeoLite 2 country database through their API  [GeoIP2 PHP API](https://github.com/maxmind/GeoIP2-php) located on GitHub, and of course the Magento hackaton installer.

Add the module to your composer file

    "require": {
        "magento-hackathon/magento-composer-installer": "*",
        "webbhuset/geoip": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Cotya/magento-composer-installer"
        },
        {
            "type":"vcs",
            "url":"https://github.com/webbhuset/Geoip.git"
        }
    ],

Then simply update composer by running the update command.

    composer update

## Getting started
First you need to sync the geoip library by going to **System > Configuration > Webbhuset  > GeoIP > General**, and then click the "**Synchronize**" button which downloads the country database to var/geoip/.

Then under **System > Configuration > Webbhuset > Geoip > General** you have four settings:

### Enable Geoip Redirect
Enable/Disable redirect of customer to the first store which the country is allowed in.
If no match is found see Fallback store below.
### Lock user to store
Allows you to lock customers to the store matching their country. With this enabled they will not be able to switch to a different store
and will always be redirected. If it is turned off, you will only be redirected the first time you enter the site (once per session) but is still able to switch store manually.
### Fallback store for not allowed countries
If the customers country does not match any stores allowed countries the customer will be redirected to this store.
### Don't redirect
The controllers/Actions that we don't want to redirect on.

## Configure redirect
The module looks at the *"Default country"* and *"Allowed Countries"* settings under **System > Configuration > General**. You need to set which countries are allowed on each store. Default Country has priority, so if your country is the *Default country* on some store, that store will be selected. If no default country was matched, it looks at *Allowed countries* and selects the first store you are allowed in and redirects to it.

  [1]: https://github.com/tim-bezhashvyly/Sandfox_GeoIP
