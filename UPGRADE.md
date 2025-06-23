# Upgrade Guide

## To 2.0.0

We have deleted the config `use_localization`.
Linkable displays the localization options if you have at least two locales installed on your project.
If the localization of your routes consists in sending a fourth parameter to the `route` function of Laravel, you have nothing else to do. 
Otherwise, you should use `setRouteCallback` as indicate in the documentation.
If you want to deactivate the location, change the config `disable_localization` to `true`.
