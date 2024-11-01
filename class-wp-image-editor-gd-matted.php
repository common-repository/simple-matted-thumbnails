<?php

/**
 * Matted thumbnail Image Editor (GD) 
 * extends WP_Image_Editor_GD in wp-includes
 * overrides the _resize and multi_resize methods 
 */

class WP_Image_Editor_GD_Matted extends WP_Image_Editor_GD {
    /**
     * Resizes current image.
     * Wraps _resize, since _resize returns a GD Resource.
     *
     * @since 3.5.0
     * @access public
     *
     * @param int $max_w
     * @param int $max_h
     * @param boolean $crop
     * @return boolean|WP_Error
     */

    protected function _resize($max_w, $max_h, $crop = false) {
// we don't touch crop, default, or if disabled
        if($crop || $max_w == 9999 || $max_h == 9999) {
            return parent::_resize($max_w, $max_h, $crop);
        }

        $mat_x = get_option('matted_thumbnails_matting_x', 0);
        $mat_y = get_option('matted_thumbnails_matting_y', 0);

        $mx = (int)(.01 * $mat_x * $max_w);
        $my = (int)(.01 * $mat_y * $max_h);
        
        $new_w = $max_w - (2*$mx);
        $new_h = $max_h - (2*$my);

        if($new_w < 1 || $new_h < 1) {
            return new WP_Error('error_getting_dimensions', __('Matting percent too large'), $this->file);
        }
        
        $dims = $this->new_dimensions($this->size['width'], $this->size['height'], $new_w, $new_h);
        if (!$dims) {
            return new WP_Error('error_getting_dimensions', __('Could not calculate resized image dimensions'), $this->file);
        }
        list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
        
        $color = get_option('matted_thumbnails_color', 0xffffff);
        $rgb = MattedThumbnails::int2rgb($color);
        
        $resized = wp_imagecreatetruecolor($max_w, $max_h);
        $colour = imagecolorallocate($resized, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($resized, 0, 0, $colour);

        //Calculate where the image should start so its centered
        if ($dst_w == $new_w) {
            $dst_x = 0;
        } else {
            $dst_x = round(($new_w - $dst_w) / 2);
        }
        if ($dst_h == $new_h) {
            $dst_y = 0;
        } else {
            $dst_y = round(($new_h - $dst_h) / 2);
        }
        $dst_x += $mx;
        $dst_y += $my;
        
        imagecopyresampled($resized, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        if (is_resource($resized)) {
            $this->update_size($max_w, $max_h);
            return $resized;
        }
        return new WP_Error('image_resize_error', __('Image resize failed.'), $this->file);
    } // end _resize
    
    function new_dimensions($orig_w, $orig_h, $dest_w, $dest_h) {
	if ($orig_w <= 0 || $orig_h <= 0)
		return false;
	// at least one of dest_w or dest_h must be specific
	if ($dest_w <= 0 && $dest_h <= 0)
		return false;

	// plugins can use this to provide custom resize dimensions
	$output = apply_filters( 'matted_resize_dimensions', null, $orig_w, $orig_h, $dest_w, $dest_h, false );
	if ( null !== $output )return $output;
	// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
	$crop_w = $orig_w;
	$crop_h = $orig_h;

	$s_x = 0;
	$s_y = 0;

	list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
    } // end new_dimensions
    
    	/**
	 * Resize multiple images from a single source.
         * overrides version in core. Uses either core image editor or matted
         * depending on the matted_thumnail_sizes setting
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param array $sizes {
	 *     An array of image size arrays. Default sizes are 'small', 'medium', 'large'.
	 *
	 *     @type array $size {
	 *         @type int  $width  Image width.
	 *         @type int  $height Image height.
	 *         @type bool $crop   Optional. Whether to crop the image. Default false.
	 *     }
	 * }
	 * @return array An array of resized images metadata by size.
	 */
	public function multi_resize( $sizes ) {
		
	
		$metadata = array();
		$orig_size = $this->size;
		
		$mattedSizes = get_option('matted_thumbnails_sizes');
		
		foreach ( $sizes as $size => $size_data ) {
			if ( ! ( isset( $size_data['width'] ) && isset( $size_data['height'] ) ) )
				continue;

			if ( ! isset( $size_data['crop'] ) )
				$size_data['crop'] = false;
			
			if(isset($mattedSizes[$size])) {	
				$image = $this->_resize( $size_data['width'], $size_data['height'], false );
			} else {
				$image = parent::_resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
			}
			
			if( ! is_wp_error( $image ) ) {
				$resized = $this->_save( $image );

				imagedestroy( $image );

				if ( ! is_wp_error( $resized ) && $resized ) {
					unset( $resized['path'] );
					$metadata[$size] = $resized;
				}
			}

			$this->size = $orig_size;
		}

		return $metadata;
	} // end multi_resize
} // end class WP_Image_Editor_GD_Matted
