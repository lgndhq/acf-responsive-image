ACF Responsive Image Plugin
========

This plugin adds a field for developer-configurable responsive images. By selecting the new "Responsive Image" option, you can specify a set of image sizes and their srcset tags to be generated when an image is selected for that field. This allows you to store image sizes alongside the fields that require them without needing to clutter up your functions file with extraneous image sizes that aren't necessary to generate for every upload.

# Use

`<img src="<?php echo get_field('my_field')['src']; ?>" srcset="<?php echo get_field('my_field')['srcset']; ?>">`
