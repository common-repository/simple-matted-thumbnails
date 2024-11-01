<?php
/*
  Plugin Name: Simple Matted Thumbnails
  Plugin URI: http://devondev.com/simple-matted-thumbnails
  Description: Matts thumbnails when crop is not specified
  Version: 1.01
  Author: Peter Wooster - forked from letterbox thumbnails by Epam Systems (Ihar Peshkou)
  Author URI: http://devondev.com
*/

/*  Copyright (C) 2013 Devondev Inc.  (http://devondev.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class MattedThumbnails
{
    // Plugin initialization
    function MattedThumbnails()
    {
	add_action('admin_menu', array(&$this, 'add_admin_menu'));
	add_filter('wp_image_editors', array(&$this, 'gd_matted_editor'));
        add_action( 'admin_enqueue_scripts', array($this, 'matted_enqueue_color_picker' ));
    } // end MattedThumbnails constructor

    function matted_enqueue_color_picker( $hook_suffix ) {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'matted-color-picker', plugins_url('matted-color.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    } // end enqueue_color_picker
    
    function gd_matted_editor($editors)
    {
	if(!class_exists('WP_Image_Editor_GDMatted'))
            include_once 'class-wp-image-editor-gd-matted.php';
        
        if (!in_array('WP_Image_Editor_GDMatt', $editors))
	    array_unshift($editors, 'WP_Image_Editor_GD_Matted');
	return $editors;
    } // end gd_matted_editor

    function add_admin_menu()
    {
	add_options_page(__('Matted Thumbnails Settings', 'default'), __('Matted Thumbnails', 'Matted Thumbnails'), 'manage_options', 'matted_thumbnails.php', array(&$this, 'matted_settings_interface'));
    } // end add_admin_menu

    // build settings page
    function matted_settings_interface() {
        ?>
	<h2><?php _e('Matted Thumbnails Settings', 'default') ?></h2>

	<?php
	//add options	
	add_option('matted_thumbnails_color', 0xffffff);
        add_option('matted_thumbnails_sizes', array());		
        add_option('matted_thumbnails_matting_x', 0);		
        add_option('matted_thumbnails_matting_y', 0);		
        // make thumbnails and other intermediate sizes
	global $_wp_additional_image_sizes;

        $sizes = array();
        foreach ( get_intermediate_image_sizes() as $s ) {
            $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
            if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
                $sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
            else
                $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
            if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
                $sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
            else
                $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
            if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
                $sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
            else
                $sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
        }

        $sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes );
        
        $mattSizes = array();
        $i = 0;
        foreach ($sizes as $n => $s) {
            if ($s['width'] && $s['width'] < 9999 && $s['height'] && $s['height'] < 9999) {
                $mattSizes[$i++] = array('name' => $n, 'checked' => '');
            }
        }
        
        if (isset($_POST['matted_thumbnails_settings_submit_btn'])) {
            if (function_exists('current_user_can') && !current_user_can('manage_options'))die(_e('Hacker?', 'matted'));

            if (function_exists('check_admin_referer')) {
                check_admin_referer('matted_thumbnails_size_settings_form');
            }

            $enabledSizes = array();
            $ps = $_POST['matted_thumbnail_sizes'];
            if($ps)foreach ($ps as $id) {
                $mattSizes[$id]['checked'] = 'checked="yes"';
                $enabledSizes[$mattSizes[$id]['name']] = 'yes';
            }

	    $matted_thumbnails_color = self::hex2rgb($_POST['matted_thumbnails_color']);
            $matting_x = intval($_POST['matted_thumbnails_matting_x']);
            $matting_y = intval($_POST['matted_thumbnails_matting_y']);
	    update_option('matted_thumbnails_color', $matted_thumbnails_color);
            update_option('matted_thumbnails_sizes', $enabledSizes);
            update_option('matted_thumbnails_matting_x', $matting_x);
            update_option('matted_thumbnails_matting_y', $matting_y);
	}
	?>
	<form id="matted_thumbnails_size_settings" name="matted_thumbnails_size_settings_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?page=matted_thumbnails.php&amp;updated=true">
	    <?php
                if (function_exists('wp_nonce_field')) {
                    wp_nonce_field('matted_thumbnails_size_settings_form');
                }
                $color = self::rgb2hex(get_option('matted_thumbnails_color'));
                $matting_x = get_option('matted_thumbnails_matting_x');
                $matting_y = get_option('matted_thumbnails_matting_y');
                $enabledSizes = get_option('matted_thumbnails_sizes');
                foreach ($mattSizes as $id => $s) {
                        if(isset($enabledSizes[$s['name']]))$mattSizes[$id]['checked'] = 'checked="yes"';
                }
            ?>
            <style>
                .wp-color-result:hover,
                .wp-color-result{
                    background-color:#<?php echo $color ?>;
                }
            </style>
                
            <h3>Matt Colour</h3>
            <p>The background colour of the matt.</p> 
            <div id="matt_color" class="lt_element">
                <div class="lt_element_wrapper">
                    <input type="text" id="matted_thumbnails_color" name="matted_thumbnails_color" value="<?php echo $color ?>" class="matted-color-field" data-default-color="#ffffff" />
                </div>
            </div>

            <h3>Enabled image sizes</h3>
            <p>Images of the checked sizes will be matted instead of cropped or proportional.</p>
            <?php foreach($mattSizes as $id => $val) : ?> 
            <div class="lt_element">
               <div class="lt_element_wrapper">
                   <input type="checkbox" id="matted_thumbnail_sizes_<?php echo $id ?>" name="matted_thumbnail_sizes[]" value="<?php echo $id ?>" <?php echo $val['checked'] ?> >
                   <label class="lt_element_label"><?php echo $val['name'] ?></label>
               </div>
            </div>
            <?php endforeach ; ?>

            <h3>Extra matting to place around thumbnail</h3>
            <p>Percentage of thumbnail box to be reserved on each side of thumbnail</p>
            <div class="lt_element">
               <div class="lt_element_wrapper">
                   <label class="lt_element_label">Percent of Width</label>
                   <input type="text" id="matted_thumbnails_matting_x" name="matted_thumbnails_matting_x" value="<?php echo $matting_x?>">%
               </div>
            </div>
            <div class="lt_element">
               <div class="lt_element_wrapper">
                   <label class="lt_element_label">Percent Height</label>
                   <input type="text" id="matted_thumbnails_matting_y" name="matted_thumbnails_matting_y" value="<?php echo $matting_y?>">%
               </div>
            </div>

            <p><input type="submit" name="matted_thumbnails_settings_submit_btn" value="<?php _e('Save settings', 'default') ?>"></p>
	</form>
	<?php
    } // end matted_settings_interface

    /**
     * Convert a hexadecimal color code to its RGB equivalent 
     * the hex is a CSS style 3 or 6 character string
     *
     * @param string $hexStr (hexadecimal color value)
     * @param boolean $asInt return result as a single integer
     * @return [R,G,B] array, encoded integer or False if invalid hex color value
     */                                                                                                 
    static function hex2rgb($hex, $asInt = false) {
        $hex = preg_replace("/[^0-9A-Fa-f]/", '', $hex); // remove non hex values
        if (strlen($hex) == 3) { //if shorthand notation, need some string manipulations
            $rgb = str_split($hex);
            $hex = $rgb[0].$rgb[0].$rgb[1].$rgb[1].$rgb[2].$rgb[2];
        } else if (6 != strlen($hex)){ // pad with 0 if not 3 or 6 characters
            $hex = $hex.'000000';
            $hex = substr($hex, 0, 6);
        }
        $color = hexdec($hex);
        if ($asInt)return $color;
        
        else return self::int2rgb($color); // returns the R,G,B array
    } // end hex2rgb

    /**
     * convert [R,G,B] array or integer into hex string
     * 
     * @param type $rgb array of Red, Green and Blue values, or encoded integer
     * @return string of 6 hex digits or False if not valid input
     */
    static function rgb2hex ($rgb) {
        if(is_array($rgb)) {
            if(3 == count($rgb) 
                    && is_int($rgb[0]) && $rgb[0] >= 0 && $rgb[0] < 256
                    && is_int($rgb[1]) && $rgb[1] >= 0 && $rgb[1] < 256
                    && is_int($rgb[2]) && $rgb[2] >= 0 && $rgb[2] < 256
            ) {        
                $c = ($rgb[0]*65536)+($rgb[1]*256)+$rgb[2];
            } else return false;
        } else {
            if (is_int($rgb)) {
                $c = $rgb;
            } else return false;
        }
        return sprintf("%06X", $c);
    } // end rgb2hex
    
    static function int2rgb ($val) {
        if(false === $val)return $val;
        if (is_array($val) && 3 == count($val))return $val;
        if (!is_int($val)) return false;
        $rgb = array(0,0,0);
        $rgb[0] = 0xFF & ($val >> 16);
        $rgb[1] = 0xFF & ($val >> 8);
        $rgb[2] = 0xFF & $val;
        return $rgb;
    } // end int2rgb
}  // end class MattedThumbnails

// Start up this plugin
add_action('init', 'InitMattedThumbnails');

function InitMattedThumbnails()
{
    global $MattedThumbnails;
    $MattedThumbnails = new MattedThumbnails();
}
// php intentionally left open