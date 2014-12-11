# Webbhuset GeoIP

## Installation
The module depends on the geoip library by [Sandfox][1] located on GitHub, and of cource the Magento hackaton installer.

Add the module to your composer file

    "require": {
        "magento-hackathon/magento-composer-installer": "*",
        "webbhuset/geoip": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/magento-hackathon/magento-composer-installer"
        },
        {
            "type":"vcs",
            "url":"https://github.com/webbhuset/Geoip.git"
        }
    ],

Then simply update composer by running the update command command.

    composer update

## Getting started
Fisrt you need to sync the geoip library by going to **System > Configuration > General > GeoIP Database Downloaded**, and then click the "**Synchronize**" button.

Then under **System > Configuration > Webbhuset > Geoip > General** you have two settings. The first one is simply to enble geoip redirect. The second one is for locking your country location to the store selected by the geoip module. If that is turned on you wont be able to switch to a different store. You will always be redirected. If it is turned off, you will only be redirected the first time you enter the site (per session). After that you can switch to whatever store you want.

### Configure redirect
The module looks at the *"Default country"* and *"Allowed Countries"* settings under **System > Configuration > General**. So you need to set wich countries are allowed on each store. Default Country has priority, so if your country is *Default country* on some store, that store will be selected. If no default country was matched, it looks at *Allowed countries* and selects the first store you are allowed in and redirects to that.

  [1]: https://github.com/tim-bezhashvyly/Sandfox_GeoIP
