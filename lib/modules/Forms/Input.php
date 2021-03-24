<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Module/Forms/Input.php
 *  @brief	Form field and attribute bulider
 */
namespace PubCabin\Modules\Forms;

class Input {
	
	/**
	 *  Supported input types
	 *  @var array
	 */
	protected static $input_types = [
		'text', 'password', 'textarea', 'search', 'select', 
		'radio', 'checkbox', 'number', 'range', 'datetime-local', 
		'file', 'submit', 'hidden', 'captcha'
	];
	
	/**
	 *  Common autocomplete attributes
	 *  @var array
	 */
	protected static $autocomplete	= [
		'on', 'off', 'name', 'given-name', , 'family-name', 
		'honorific-prefix', 'honorific-suffix', 'additional-name', 
		'nickname', 'email', 'username', 'new-password', 
		'current-password', 'one-time-code', 'postal-code', 
		'street-address', 'address-line1', 'address-line2', 
		'address-line3', 'address-level1', 'address-level2', 
		'address-level3', 'address-level4', 'country', 'country-name', 
		'organization', 'organization-title', 'language', 
		'cc-name', 'cc-number', 'cc-csc', 'cc-type', 
		'cc-given-name', 'cc-family-name', 'cc-additional-name', 
		'cc-exp', 'c-exp-month', 'cc-exp-year', 
		'transaction-currency', 'transaction-amount', 
		'bday', 'bday-day', 'bday-month', 'bday-year', 'sex',
		'tel', 'tel-country-code', 'tel-national', 'tel-area-code', 
		'tel-local', 'tel-extension', 'impp', 'url', 'photo'
	];
	
	/**
	 *  Common templates
	 *  @var array
	 */
	protected static $templates	= [
		
		// Input field without description or label
		'tpl_input_nd'			=> <<<HTML
{input_field_before}<input id="{id}" name="{name}" type="{type}" 
	placeholder="{placeholder}" class="{input_classes}" 
	{required}{extra}>{input_field_after}
HTML
,
		
		// Combined input field with label and description
		'tpl_input_field'		=> <<<HTML
{input_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
HTML
,
		
		// Combined input field with label and without description
		'tpl_input_field_nd'		=> <<<HTML
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}
HTML
,
		
		// Multiline text block content input
		'tpl_input_textarea'		=> <<<HTML
{input_before}{input_multiline_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<textarea id="{id}" name="{name}" rows="{rows} cols="{cols}" 
	placeholder="{placeholder}" aria-describedby="{id}-desc"
	 class="{input_classes}" {required}{extra}>{value}</textarea>{input_field_after}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
{input_multiline_after}{input_after}
HTML
,
		
		/**
		 *  User input form building blocks
		 */
		
		// Select box option
		'tpl_input_select_opt'		=> <<<HTML
<option value="{value}" {selected}>{text}</option>
HTML
,
		
		// Select dropdown
		'tpl_input_select'		=> <<<HTML
{input_before}{input_select_before}{label_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
<select id="{id}" name="{name}" aria-describedby="{id}-desc"
	class="{input_classes}" {required}{extra}>
	{unselect_option}{options}</select>
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}
{input_select_after}{input_after}
HTML
,
		
		// Unselected dropdown option
		'tpl_input_unselect'		=> <<<HTML
<option value="">--</option>
HTML
,
		
		// File upload input
		'tpl_input_file'		=> <<<HTML
{input_before}{input_upload_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<input id="{id}" name="{name}" type="file" 
	placeholder="{placeholder}" class="{input_classes}" 
	aria-describedby="{id}-desc" {required}{extra}>{input_field_after}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
{input_upload_after}{input_after}
HTML
,
		
		// Upload input no description
		'tpl_input_file_nd'		=> <<<HTML
{input_before}{input_upload_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<input id="{id}" name="{name}" type="file" 
	placeholder="{placeholder}" aria-describedby="{id}-desc" 
	class="{input_classes}" 
	{required}{extra}>{input_field_after}{input_upload_after}{input_after}
HTML
,
		
		// Form submission
		'tpl_input_submit'		=> <<<HTML
{input_before}{input_submit_before}<input type="submit" id="{id}" 
	name="{name}" value="{value}" class="{submit_classes}" 
	{extra}>{input_submit_after}{input_after}
HTML
,
		// Generic button
		'tpl_input_button'		=> <<<HTML
{input_before}{input_button_before}<input type="button" id="{id}" 
	name="{name}" value="{value}" class="{button_classes}" 
	{extra}>{input_button_after}{input_after}
HTML;
	
	
	
	
	public function validate( array $params ) : bool {
		// TODO Form field validation
		return true;
	}
	
	/**
	 *  Template selector based on input type
	 *  
	 *  @params array	$params		Input placeholders
	 */
	protected function buildField( array $params ) {
		// No description?
		if ( !isset( $params['description'] ) ) {
			
			// No label either?
			if ( !isset( $params['label'] ) ) {
				
				// Template with no label or description
				return 
				\strtr( 
					static::$templates['tpl_input_nd'], 
					$params
				);
			}
			
			// Template with label only
			return 
			\strtr( 
				static::$templates['tpl_input_field_nd'], 
				$params
			);
		}
		
		// Full field template with label and description
		return 
		\strtr( 
			static::$templates['tpl_input_field'], 
			$params
		);
	}
	
	/**
	 *  Input field template helper
	 *  
	 *  @param string	$params		Placeholder replacements
	 */
	public function create( array $params ) : string {
		
		// Default type is text
		if ( !isset( $params['type'] ) ) {
			$params['type'] = 'text';
		}
		
		switch ( $params['type'] ) {
			case 'text':
			case 'password':
			case 'search':
			case 'radio':
			case 'checkbox':
			case 'number':
			case 'range':
			case 'datetime-local':
				return $this->buildField( $params );
			
			// Captcha is basically text
			case 'captcha':
				$params['type']	= 'text';
				return $this->buildField( $params );
			
			// Textarea is a special type
			case 'textarea':
				return 
				\strtr( 
					static::$templates['tpl_input_textarea'], 
					$params
				);
			
			case 'file':
				return 
				\strtr( 
					static::$templates['tpl_input_file'], 
					$params
				);
			
			case 'file_nd':
				return 
				\strtr( 
					static::$templates['tpl_input_file_nd'], 
					$params
				);
			
			case 'submit':
				return 
				\strtr( 
					static::$templates['tpl_input_submit'], 
					$params
				);
				
			case 'button':
				return 
				\strtr( 
					static::$templates['tpl_input_button'], 
					$params
				);
			
			case 'select':
				
				// TODO Select box builder
				return '';
			
			// Generic type without labels or descriptions
			default:
				return 
				\strtr( 
					static::$templates['tpl_input_nd'], 
					$params
				);
		}
	}
	
	
}



