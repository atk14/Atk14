<?php
/**
 * Provides access to uploaded image
 *
 * @package Atk14
 * @subpackage Forms
 */
class ImageField extends FileField{
	
	var $width;
	var $height;
	var $max_width;
	var $max_height;
	var $min_width;
	var $min_height;
	var $file_formats;

	function __construct($options = array()){
		$options = array_merge(array(
			"width" => null,
			"height" => null,
			"max_width" => null,
			"max_height" => null,
			"min_width" => null,
			"min_height" => null,
			"file_formats" => array(), // array("jpeg","png","git","tiff")
		),$options);
		parent::__construct($options);

		$this->update_messages(array(
			'not_image' => _('Ensure this file is image (it is %mime_type%).'),

			'width' => _('Ensure this image is %width_required% pixels wide (it is %width%).'),
			'height' => _('Ensure this image is %height_required% pixels high (it is %height%).'),

			'max_width' => _('Ensure this image is at most %max% pixels wide (it is %width%).'),
			'max_height' => _('Ensure this image is at most %max% pixels high (it is %height%).'),
			'min_width' => _('Ensure this image is at least %min% pixels wide (it is %width%).'),
			'min_height' => _('Ensure this image is at least %min% pixels high (it is %height%).'),

			'file_formats' => _('Ensure this image is in a required format (it is %mime_type%).'),
		));
		$this->width = $options["width"];
		$this->height = $options["height"];
		$this->max_width = $options["max_width"];
		$this->max_height = $options["max_height"];
		$this->min_width = $options["min_width"];
		$this->min_height = $options["min_height"];
		$this->file_formats = $options["file_formats"];
	}
	function clean($value){
		list($err,$value) = parent::clean($value);
		if(isset($err)){ return array($err,null); }
		if(!isset($value)){ return array(null,null); }

		// --

		if(!$value->isImage()){ return array(
				strtr($this->messages['not_image'],array("%mime_type%" => h($value->getMimeType()))),
				null);
		}

		// --
		
		if($this->file_formats){
			list(,$file_format) = explode('/',$value->getMimeType());
			if(!in_array($file_format,$this->file_formats)){
				return array(
					strtr($this->messages['file_formats'],array("%mime_type%" => h($value->getMimeType()))),
					null
				);
			}
		}

		// --

		if($this->width && $value->getImageWidth()!=$this->width){
			return array(
				strtr($this->messages['width'],array("%width_required%" => $this->width, "%width%" => $value->getImageWidth())),
				null,
			);
		}

		if($this->height && $value->getImageHeight()!=$this->height){
			return array(
				strtr($this->messages['height'],array("%height_required%" => $this->height, "%height%" => $value->getImageHeight())),
				null,
			);
		}

		// ---

		if($this->max_width && $value->getImageWidth()>$this->max_width){
			return array(
				strtr($this->messages['max_width'],array("%max%" => $this->max_width, "%width%" => $value->getImageWidth())),
				null,
			);
		}

		if($this->max_height && $value->getImageHeight()>$this->max_height){
			return array(
				strtr($this->messages['max_height'],array("%max%" => $this->max_height, "%height%" => $value->getImageHeight())),
				null,
			);
		}

		// ---

		if($this->min_width && $value->getImageWidth()<$this->min_width){
			return array(
				strtr($this->messages['min_width'],array("%min%" => $this->min_width, "%width%" => $value->getImageWidth())),
				null,
			);
		}

		if($this->min_height && $value->getImageHeight()<$this->min_height){
			return array(
				strtr($this->messages['min_height'],array("%min%" => $this->min_height, "%height%" => $value->getImageHeight())),
				null,
			);
		}

		return array(null,$value);
	}
}
