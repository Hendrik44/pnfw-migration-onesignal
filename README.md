# PNFW Migration OneSignal #
**Contributors:**      JG-Bits UG (haftungsbeschränkt) / Hendrik Jürst  
**Donate link:**       https://www.jg-bits.de  
**Tags:**  
**Requires at least:** 4.4  
**Tested up to:**      4.7.2 
**Stable tag:**        0.1.0  
**License:**           MIT  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

Easy migrate your Push-Users from Pushnotifications for WordPress-Plugin to OneSingal using register/unregister-Route and redirect to OneSingal

## Installation ##


### Manual Installation ###

1. Upload the entire `/pnfw-migration-onesignal` directory to the `/wp-content/plugins/` directory.
2. Edit your OneSingal App-ID in `pnfw-migration-onesignal.php` and `$oneSingal_app_id` to your App-ID
3. Deactivate "Pushnotifications for WordPress-Plugin"
4. Activate PNFW Migration OneSignal through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##
### Does this plugin support WP-Multisite and different blog-ids?
Yes, this plugin add a user-tag named "blog_id" and the current blog-id as value. With a custom segment and an if condition you will be able to identify where the device is registered and send push notifications based on this condition.

### Filter migrated Users?
Yes, this plugins add a user-tag named "pnfw_migration" and the value "true". Creating a custom segment with if-condition and you will be able to see all migrated users.

## Screenshots ##


## Changelog ##

### 0.1.0 ###
* First release

## Upgrade Notice ##

### 0.1.0 ###
First Release
