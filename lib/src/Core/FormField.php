<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/FormField.php
 *  @brief	Custom user input field and formatting
 */

namespace PubCabin\Core;

class FormField extends \PubCabin\Entity {
	
	/**
	 *  Form unique identifier
	 *  @var int
	 */
	public $form_id;
	
	/**
	 *  Form description
	 *  @var string
	 */
	public $form_title;
	
	/**
	 *  Form input submission name
	 *  @var string
	 */
	public $name;
	
	/**
	 *  Language interpreted title text
	 *  @var string
	 */
	public $title;
	
	/**
	 *  Localized string label
	 *  @var string
	 */
	public $label;
	
	/**
	 *  Localized special formatting instructions E.G. "required"
	 *  @var string
	 */
	public $special;
	
	/**
	 *  Localized long instructions including any links to help
	 *  @var string
	 */
	public $description;
		
	/**
	 *  Input sanitation and formatting handler
	 *  @var string
	 */
	public $filter;
	
	/**
	 *  Rendering style template
	 *  @var int
	 */
	public $style_id;
	
	/**
	 *  Rendering template
	 *  @var int
	 */
	public $template_id;
	
	/**
	 *  HTML New content creation template, including input type
	 *  @var string
	 */
	 public $create_template;
	 
	 /**
	  *  HTML Existing content editing template
	  *  @var string
	  */
	 public $edit_template;
	 
	 /**
	  *  Field data formatted view template
	  *  @var string
	  */
	 public $view_template;
	 
	 /**
	  *  Style placeholders
	  */
	public $style_label;
	public $template_label;
	public $template_render;
	
	/**
	 *  Language placeholders
	 */
	public $lang_label;
	public $lang_display;
	public $lang_iso;
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'field_name':
				$this->name = $value;
				break;
				
			default:
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'field_name':
				return $this->name ?? null;
		}
		
		return parent::__get( $name );
	}
	
	public function save( \PubCabin\Data $data ) : bool {
		$params	= [
			':name'		=> $this->name,
			':form'		=> $this->form_id,
			':filter'	=> $this->filter,
			':style'	=> $this->style_id,
			':template'	=> $this->template_id,
			':crt'		=> $this->create_template,
			':edt'		=> $this->edt_template,
			':vwt'		=> $this->view_template,
			':settings'	=> 
				\PubCabin\Util::encode( $this->settings )
		]
		
		if ( empty( $this->id ) ) {
			$sql = 
			"INSERT INTO form_fields( name, form_id, filter, 
				style_id, template_id, create_template, 
				edit_template, view_template, settings )
			VALUES ( :name, :form, :filter, :style, :template, 
			:crt, :edt, :vwt, :settings );";
			
			$this->id = 
			$data->setInsert( $sql, $params, static::MAIN_DATA );
			
			return empty( $this->id ) ? false : true;
		}
		
		$sql = 
		"UPDATE form_fields SET field_name = :name, 
			form_id = :form, filter = :filter, 
			style_id = :style, template_id = :template, 
			create_template = :crt, edit_template = :edt, 
			view_template = :vwt, settings = :settings 
			WHERE id = :id;";
		
		$params[':id'] => $this->id;
		return 
		$data->setUpdate( $sql, $params, static::MAIN_DATA );
	}
	
}
