# Upgrade Guide

## To 2.0.0

### Localization

We have deleted the config `use_localization` in `laravel-linkable.php`.
Linkable displays the localization options if you have at least two locales installed on your project.
If the localization of your routes consists in sending a fourth parameter to the `route` function of Laravel, you have nothing else to do. 
Otherwise, you should use `setRouteCallback` as indicate in the documentation.
If you want to deactivate the location, change the config `disable_localization` to `true`.

### Trait configuration

A new option `$optionSearch` has been added to the `LinkableConfig`. If `$optionLabel` is a colomn name, you have nothing to do. If `$optionLabel` is a function, you have to define the `$optionSearch` parameter as an array of searchable colomn names.
