=== Simple Matted Thumbnails ===
Tags: thumbnails, size, crop, mat
Contributors:pkwooster
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.01
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a "Matted" format for thumbnails in addition to the cropped and proportional ones provided by default.

== Description ==

WordPress has two formats for thumbnails, they are either proportional or cropped.  Proportional thumbnails don't have a consistent size and
cropped thumbnails lose portions of the image.  Neither of these options are optimal for images such as e-commerce product images that are 
arranged in a grid or any other layout where exact size is required. 

This plugin adds a third format, "Matted".  A matted thumbnail is the exact size requested like a cropped thumbnail.  
The difference is that instead of taking away part of the image, a mat, similar to the physical cardboard mats used with photographs, is placed around it. 
Images may be reduced in size but are never enlarged.  The image is centered in the thumbnail box with the mat color as the background for the box.

== Installation ==

1. Upload the `simple-matted-thumbnails` folder to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in the WordPress Admin

== Frequently Asked Questions ==

= Where are the Settings =
Login as admin and go to the settings. Click the `Matted Thumbnails` link.  You can change the colour of the mat and which 
thumbnail sizes are to be matted.

= How do set I the mat's background colour =
The background colour can be set using the settings menu. The colour picker is the standard WordPress colour picker.  If using an old version of WordPress
or with JavaScript disabled, you can set a hexadecimal RGB value similar to those used in CSS.

= How do I apply this to specific thumbnail sizes =
You can select the sizes by checking their name in the settings.

Matting is only applied to those sizes selected.  Selecting a thumbnail size will override the crop setting. Thumbnail sizes that include the default 
height or width of 9999 can not be matted.

= What is the extra matting =
This is space in the thumbnail box that is reserved for matting in addition to any needed to accommodate the image.  The extra matting is placed on either both
top and bottom or both left and right sides.

= How do I rebuild the thumbnails =
After changing thumbnail settings, any new images will get the new thumbnail sizes and format, but existing images will not.  There are several plugins that allow 
the administrator to regenerate the thumbnails.  Two popular plugins are "Regenerate Thumbnails" and "Force Regenerate Thumbnails".  Note that using either of 
these will destroy existing thumbnails, so a backup is recommended.

= What happens if I add a new thumbnail size =
This plugin will not apply to new sizes unless you select them in the admin settings.

== Changelog ==

= Version 1.01 =

Fixed bug in admin that produces error message when debug enabled

= Version 1.0 =

Plugin forked from the letterbox-thumbnails plugin. 

Control added to select which thumbnail sizes to mat. The colour picker uses the WordPress Iris based colour picker introduced in WP 3.5 

== Upgrade Notice ==
This is the first release, no upgrade available