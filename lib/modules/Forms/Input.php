<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Forms/Input.php
 *  @brief	Form field and attribute bulider
 */
namespace PubCabin\Modules\Forms;

class Input {
	
	/**
	 *  Default textarea columns
	 */
	const RENDER_MULTILINE_COLS	= 60;
	
	/**
	 *  Default textarea rows
	 */
	const RENDER_MULTILINE_ROWS	= 10;
	
	/**
	 *  Captcha string length
	 */
	const CAPTCHA_LENGTH		= 5;
	
	/** 
	 *  Default captcha hashing algorithm
	 */
	const CAPTCHA_HASH		= 'tiger160,4';
	
	/**
	 *  Event controller
	 *  @var \PubCabin\Controller
	 */
	protected $ctrl;
	
	/**
	 *  Render and output handler
	 *  @var \PubCabin\Render
	 */
	protected $render;
	
	/**
	 *  Supported input types
	 *  @var array
	 */
	protected static $input_types = [
		'text', 'password', 'textarea', 'search', 'select', 'email', 
		'radio', 'checkbox', 'number', 'range', 'datetime-local', 
		'file', 'submit', 'button', 'hidden', 'captcha', 'color', 
		'tel', 'url', 'wysiwyg', 'timezone'
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
	 *  Visual editor tools and options
	 *  @var array
	 */
	protected static $wysiwyg_tools = [
		"bold"		=> [],
		"italic"	=> [],
		"underline"	=> [],
		"unordered"	=> [],
		"ordered"	=> [],
		"heading"	=> [],
		"link"		=> [],
		"quote"		=> [],
		"code"		=> [],
		"image"		=> [],
		"undo"		=> [],
		"redo"		=> []
	];
	
	
	/**
	 *  Common templates
	 *  @var array
	 */
	protected static $templates	= [
		
		// Standard input field
		'tpl_input'			=> <<<HTML
{input_field_before}<input id="{id}" name="{name}" type="{type}" 
	placeholder="{placeholder}" class="{input_classes}" value="{value}" 
	aria-describedby="{id}-desc" {required}{extra}>{input_field_after}
HTML
,

		// Input field without description or label
		'tpl_input_nd_nl'		=> <<<HTML
{input_field_before}<input id="{id}" name="{name}" type="{type}" 
	placeholder="{placeholder}" class="{input_classes}" 
	value="{value}" {required}{extra}>{input_field_after}
HTML
,
		
		// Input field without description
		'tpl_input_nd'			=> <<<HTML
{input_field_before}<input id="{id}" name="{name}" type="{type}" 
	placeholder="{placeholder}" class="{input_classes}" 
	value="{value}" {required}{extra}>{input_field_after}
HTML
,
		
		// Combined input field with label and description
		'tpl_input_field'		=> <<<HTML
{input_before}
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
HTML
,
		
		// Combined input field with label and without description
		'tpl_input_field_nd'		=> <<<HTML
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}
HTML
,
		
		// Multiline text block content input
		'tpl_input_textarea'		=> <<<HTML
{input_before}{input_multiline_before}
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
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

		// Visual editor content intput
		'tpl_input_wysiwyg'		=> <<<HTML
{input_before}{input_wysiwyg_before}
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<div id="{id}-wysiwyg" data-textarea-name="{name}"></div>
<textarea id="{id}" name="{name}" rows="{rows} cols="{cols}" 
	placeholder="{placeholder}" aria-describedby="{id}-desc"
	 class="{input_classes}" {required}{extra}>{value}</textarea>{input_field_after}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
{input_wysiwyg_after}{input_after}
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
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
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
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
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
{label_before}<label for="{id}" class="{label_classes}" 
	id="{id}-label">{label}
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
HTML
,
		// Captcha hidden and image fields
		'tpl_captcha'			=> <<<HTML
<input type="hidden" name="cap_a" value="{cap_a}">
<img src="{captcha}" alt="{lang:forms:captcha:alt}" 
	class="{captcha_classes}">
HTML
,
		// Validation message template
		'tpl_validation_attr'		=> ' data-validation="{msg}" ',
		'tpl_autocomplete'		=> ' autocomplete="{msg}" ';
		
	
	public function __construct( \PubCabin\Controller $_ctrl ) {
		$this->ctrl	= $_ctrl;
		$this->render	= $_ctrl->output( 'begin' )['render'];
		$this->extendInput();
	}
	
	public function validate( array $params ) : bool {
		// TODO Form field validation
		return true;
	}
	
	/**
	 *  Functionality change helper
	 */
	protected function extendInput() {
		$this->ctrl->run( 
			'autocompleteopts',
			[ 'options' => static::$autocomplete ]
		);
		$this->ctrl->run( 
			'inputtemplates',
			[ 'types' => static::$templates ]
		);
		$this->ctrl->run( 
			'wysiwygtools', 
			[ 'tools' => static::$wysiwyg_tools ] 
		);
		
		
		// Modify base supported input type
		static::$input_types	= 
		\array_merge( 
			static::$input_types, 
			$this->ctrl->output(  
				'inputtypes' 
			)['types'] ?? [] 
		);
		
		// Add/append autocomplete properties
		static::$autocomplete	= 
		\array_merge( 
			static::$autocomplete, 
			$this->ctrl->output(  
				'autocompleteopts' 
			)['options'] ?? [] 
		);
		
		// Extend base templates
		static::$templates	= 
		\array_merge( 
			static::$templates, 
			$this->ctrl->output(  
				'inputtemplates' 
			)['templates'] ?? [] 
		);
		
		// New tools or new wysiwyg functionality?
		$nt	= $this->ctrl->output(  'wysiwygtools' );
		
		static::$wysiwyg_tools	= 
		\array_merge( 
			static::$wysiwyg_tools, 
			$nt['tools'] ?? [] 
		);
		// TODO Wysiwyg attachments and functionality
	}
	
	/**
	 *  Template selector based on input type
	 *  
	 *  @params array	$params		Input placeholders
	 */
	protected function buildField( array $params ) {
		// Set extras to blank if not set
		$params['{extra}']	??= '';
		$params['{desc_extra}']	??= '';
		
		// Find validation message, if present, and append to extras
		if ( isset( $params['validation'] ) ) {
			$params['{extra}'] .= 
			\strtr( 
				static::$templates['tpl_validation_attr'], 
				[ '{msg}' => $params['validation'] ]
			);
		}
		
		// Autocomplete available? Append to extras
		$acompl	= 
		\PubCabin\Util::lowercase( $params['autocomplete'] ?? '' );
		
		// Prepend autocomplete to existing extras if present
		if ( \in_array( $acompl, static::$autocomplete ) ) {
			$params['{desc_extra}'] .= 
			\strtr(
				static::$templates['tpl_autocomplete'],
				[ '{msg}' => $acompl ]
			);
		}
		
		// No description?
		if ( !isset( $params['{desc}'] ) ) {
			
			// No label either?
			if ( !isset( $params['{label}'] ) ) {
				
				// Template with no label or description
				return 
				\strtr( 
					static::$templates['tpl_input_nd_nl'], 
					$params
				);
			}
			
			// Template with label only
			$params['{input}'] = 
			empty( $params['{input}'] ) ?
				\strtr( 
					static::$templates['tpl_input_nd'],
					$params
				) : $params['{input}'];
		
			return 
			\strtr( 
				static::$templates['tpl_input_field_nd'], 
				$params
			);
		}
		
		// Full field template with label and description
		$params['{input}'] = 
		empty( $params['{input}'] ) ?
			\strtr( 
				static::$templates['tpl_input'], 
				$params
			) : $params['{input}'];
		
		return 
		\strtr( 
			static::$templates['tpl_input_field'], 
			$params
		);
	}
	
	/**
	 *  Generate timezone select options
	 *  
	 *  @param string	$selected	Currently set timezone
	 *  @param bool		$req		Field is required if true
	 *  @return array
	 */
	public function timezoneSelectOptions(
		string	$selected,
		bool	$req		= true
	) : array {
		static $ntpl	= '{tz} ( UTC {prefix} {format} )';
		
		$offsets	= \PubCabin\Util::timezoneOffsets();
		$out		= [];
		$format		= '';
		$nice		= '';
		$sel		= false;
		
		foreach ( $offsets as $tz => $offset ) {
			$format	= \gmdate( 'H:i', \abs( $offset ) );
			$nice	= 
			\strtr( $ntpl, [ 
				'{tz}'		=> $tz, 
				'{prefix}'	=> 
					( $offset < 0 ) ? '-' : '+',
				'{format}'	=> $format
			] );
			$sel	= 
			( 0 === \strcasecmp( $tz, \trim( $selected ) ) ) ? 
				true : false;
			
			// Value, text, selected
			$out[]	= [ $tz, $nice, $sel ];
		}
		
		return $out;
	}
	
	/**
	 *  Input field template helper
	 *  
	 *  @param string	$params		Placeholder replacements
	 */
	protected function create( array $params ) : string {
		
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
				
				// Override input_before with hidden field and image
				$params['{input_before}'] = 
					( $params['{input_before}'] ?? '' ) . 
					static::$templates['tpl_captcha'];
				
				// Append additional replacements
				return $this->buildField( $params );
			
			// Textarea is a special type
			case 'textarea':
				return 
				\strtr( 
					static::$templates['tpl_input_textarea'], 
					$params
				);
				
			// Wysiwyg is also special
			case 'wysiwyg':
				return 
				\strtr( 
					static::$templates['tpl_input_wysiwyg'], 
					$params
				);
			
			// Timezone is a special select
			case 'timezone':
				if ( empty( $params['options'] ) ) {
					$params['options'] = [ [ 0, '' ] ] + 
					$this->timezoneSelectOptions( 
						$params['selected'] ?? '' 
					);
				}
				return 
				\strtr( 
					static::$templates['tpl_input_select'], 
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
			
			// Generic type without labels or descriptions
			default:
				return 
				\strtr( 
					static::$templates['tpl_input_nd'], 
					$params
				);
		}
	}
	
	/**
	 *  Set commonly required field attributes
	 *  
	 *  @param array	$field		Raw user input field
	 *  @return array
	 */
	protected function fieldPrefilter( array $field ) : array {
		// Default input type
		$field['type']	= 
		\PubCabin\Util::lowercase( 
			\trim( $field['type'] ) ?? 'text' 
		);
		
		// Default name
		if ( 0 === \strcmp( $field['name'] ?? '', '' ) ) {
			$field['name'] = \PubCabin\Util::genAlphaNum();
		}
		
		// Default ID
		if ( 0 === \strcmp( $field['id'] ?? '', '' ) ) {
			$field['id'] = $field['name'];
		}
		
		// Fix datetime-local type
		if (
			( 0 === \strcmp( 'date-time', $field['type'] ) || 
			( 0 === \strcmp( 'datetime', $field['type'] ) 
		) {
			$field['type']	= 'datetime-local';
		}
		
		return $field;
	}
	
	/**
	 *  Create filtered input buttons
	 *  
	 *  @param array	$buttons	List of input buttons
	 *  @return string
	 */
	protected function createButtons( array $buttons ) : string {
		$btn	= '';
		$tpl	= '';
		$type	= '';
		
		foreach ( $buttons as $b ) {
			// Buttons specially need a type
			if ( empty( $b['type'] ) ) {
				continue;
			}
			
			$b	= $this->fieldPrefilter( $b );
			$type	= $b['type'];
			
			// Skip non-button elements
			if ( 
				0 !== \strcmp( $type, 'submit' ) || 
				0 !== \strcmp( $type, 'button' )
			) {
				continue;
			}
			
			// Select default template
			if ( empty( $b['template'] ) ) {
				$tpl = 
				( 0 === \strcmp( $type, 'submit' ) ) ?
					$this->render->template( 'tpl_input_submit' ) : 
					$this->render->template( 'tpl_input_button' );
				
				// Fallback 
				$tpl = empty( $tpl ) ? 
					$this->create( $b ) : $tpl;
			
			} else {
				$tpl = $b['template'];
			}
			
			$btn .= 
			$this->createInputField( 
				$b['name'] ?? '', $tpl, $b
			);
		}
		return $btn;
	}
	
	
	
	/**
	 *  This section uses the Styles module
	 */
	
	/**
	 *  Create a user input form and apply hooks per placeholder
	 *  
	 *  @param string	$name		Form name (also used for XSRF)
	 *  @param array	$fields		Form content input defaults
	 *  @param string	$action		Posting location
	 *  @param array	$buttons	Form  submission or other buttons
	 *  @param string	$method		Form submission method
	 *  @param string	$enctype	Form encoding type
	 *  @param bool		$is_block	Block level form if true
	 *  @return string
	 */
	public function createForm(
		string	$name,
		array	$fields,
		string	$action,
		array	$buttons	= [],
		string	$method		= 'get',
		string	$enctype	= '',
		bool	$is_block	= true
	) : string {
		$ctrl	= &$this->ctrl;
		$config = $ctrl->getConfig();
		
		// Check posting method
		if ( 0 != \strncasecmp( $method, 'get' ) ) {
			$ap	= 
			$config->setting( 'allow_post', 'bool' ) ?? false;
			
			if ( !$ap || 0 != \strncasecmp( $method, 'post' )) {
				// Don't build this form in a method that isn't allowed
				return '';
			}
		}
		
		// Inline or block type form
		$tpl	= $is_block ? 'tpl_form_block' : 'tpl_form';
		
		// Filter encoding type
		$enctype= Html::cleanFormEnctype( $enctype );
		
		// Hook options
		$opts	= [ 
			'name'		=> $name, 
			'is_block'	=> $is_block, 
			'fields'	=> $fields, 
			'buttons'	=> $buttons 
		];
		
		// Pre-input
		$this->ctrl->run( 'formbefore', $opts );
		
		// Call block level or inline level form events
		// Replace input fields if needed
		if ( $is_block ) {
			$ctrl->run( 'formblockbefore', $opts );
			$opts = $ctrl->output( 'formblockbefore' ) ?? $opts;
		} else {
			$ctrl->run( 'forminlinebefore', $opts );
			$opts = $ctrl->output( 'formblockbefore' ) ?? $opts;
		}
		
		$ctrl->run( 'forminputbefore', $opts );
		
		// Create anti-XSRF token fields before other fields
		$pair	= $this->module->genNoncePair( $name );
		$out	= 
		$ctrl->wrap( 
			'before'. $name .'xsrf',
			'after'. $name .'xsrf',
			$this->render->template( 'tpl_input_xsrf' ), 
			[ 
				'nonce'	=> $pair['nonce'], 
				'token'	=> $pair['token'] 
			]
		);
		
		$itpl = '';
		// Append other fields
		foreach ( $opts['fields'] as $f ) {
			$ft	= $f['type'] ?? 'text';
			// Wysiwyg added?
			$wys	= ( 0 == \strcasecmp( 'wysiwyg', $ft ) ) ? 
				true : false;
			
			// Captcha added?
			$cap	= ( 0 == \strcasecmp( 'captcha', $ft ) ) ? 
				true : false;
			
			if ( $wys ) {
				$ctrl->run( 'wysiwygload', [ 'field' => $f ] );
			} elseif ( $cap ) {
				$ctrl->run( 'captchaload', [ 'field' => $f ] );
			}
			
			$out .= $this->createFormField( $f );
		}
		
		// Append buttons
		$ctrl->run( 'buttonwrapbefore', $opts );
		$ctrl->run( 'buttonwrapafter', $opts );
		
		$out	.= 
		$this->render->parse( 
			$this->render->template( 'tpl_form_button_wrap' ), [ 
			'button_wrap_before'	=> $ctrl->stringResult( 'buttonwrapbefore' ),
			'button_wrap_after'	=> $ctrl->stringResult( 'buttonwrapafter' ),
			'buttons'		=> $this->createButtons( $buttons )
		] );
		
		// Post-input
		$ctrl->un( 'forminputafter', $opts );
		if ( $is_block ) {
			$ctrl->run( 'formblockafter', $opts );
		} else {
			$ctrl->run( 'forminlineafter', $opts );
		}
		
		// Form after event
		$this->ctrl->run( 'formafter', $opts );
		
		// Append template placeholders
		$vars	= [
			'form_before'		=> $ctrl->stringResult( 'formbefore' ), 
			'form_after'		=> $ctrl->stringResult( 'formafter' ),
			'form_input_before'	=> $ctrl->stringResult( 'forminputbefore' ),
			'form_input_after'	=> $ctrl->stringResult( 'forminputafter' ),
			'fields'		=> $out
		];
		
		if ( $is_block ) {
			$vars['form_block_before']	= $ctrl->stringResult( 'formblockbefore' );
			$vars['form_block_after']	= $ctrl->stringResult( 'formblockafter' );
		} else {
			$vars['form_inline_before']	= $ctrl->stringResult( 'forminlinebefore' );
			$vars['form_inline_after']	= $ctrl->stringResult( 'forminlineafter' );
		}
		
		return 
		$this->render->parse( 
			$this->render->template( $tpl ), $vars 
		);
	}

	/**
	 *  Create an input field and apply hooks per placeholder
	 *  
	 *  @param string		$name		Input field name
	 *  @param string		$tpl		Rendering template
	 *  @param array		$vars		Starting default values
	 *  @return string
	 */
	public function createInputField(
		string		$name, 
		string		$tpl, 
		array		$vars
	) : string {
		$ctrl		= $this->ctrl;
		// Set field ID if not already set
		$vars['id']	= $vars['id'] ?? $name;
		
		// Input specific hook events
		$nbf		= 'input' . $name . 'before';
		$naf		= 'input' . $name . 'after';
		
		// Hook settings
		$opts		= [ 'name' => $name, 'details' => $vars ];
		
		/**
		 *  Run field hooks
		 */
		// General input before/after hooks
		$ctrl->run( 'inputbefore', $opts );
		$ctrl->run( 'inputafter', $opts );
		
		// Input name specific before/after hooks
		$ctrl->run( $nbf, $opts );
		$ctrl->run( $naf, $opts );
		
		// Input label and special detail hooks
		$ctrl->run( 'labelbefore', $opts );
		$ctrl->run( 'labelafter', $opts );
		
		$ctrl->run( 'specialbefore', $opts );
		$ctrl->run( 'specialafter', $opts );
	
		// Input field hooks
		$ctrl->run( 'inputfieldbefore', $opts );
		$ctrl->run( 'inputfieldafter', $opts );
		
		// Description/help info hooks
		$ctrl->run( 'desc_before', $opts );
		$ctrl->run( 'desc_after', $opts );
		
		// Form field input wrap
		$ctrl->run( 'inputwrapbefore', $opts );
		$ctrl->run( 'inputwrapafter', $opts );
		
		$out		= 
		\array_merge( $vars, [
			'input_before'			=> $ctrl->stringResult( 'inputbefore' ),
			'input_after'			=> $ctrl->stringResult( 'inputafter' ),
			
			'input_' . $name .'_before'	=> $ctrl->stringResult ( $nbf ),
			'input_' . $name .'_after'	=> $ctrl->stringResult ( $naf ),
			
			'label_before'			=> $ctrl->stringResult( 'labelbefore' ),
			'input_after'			=> $ctrl->stringResult( 'labelafter' ),
			
			'special_before'		=> $ctrl->stringResult( 'specialbefore' ),
			'special_after'			=> $ctrl->stringResult( 'specialafter' ),
			
			'input_field_before'		=> $ctrl->stringResult( 'inputfieldbefore' ),
			'input_field_before'		=> $ctrl->stringResult( 'inputfieldafter' ),
			
			'desc_before'			=> $ctrl->stringResult( 'descbefore' ),
			'desc_after'			=> $ctrl->stringResult( 'descafter' )
		] );
		
		// Select and timezone are special types
		$it	= $vars['type'];
		$is_tz	= ( 0 === \strcasecmp( $it, 'timezone' ) ) ? true : false;
		$is_sel	= ( $is_tz || 0 === \strcasecmp( $it, 'select' ) ) ? true : false;
		
		// Timezone and no options yet? Populate
		if ( $is_tz && empty( $vars['options'] ) ) {
			$params['options'] = [ [ 0, '' ] ] + 
			$this->timezoneSelectOptions( 
				$vars['selected'] ?? '' 
			);
		}
		
		$input	= $is_sel ? 
		$this->createSelect(
			$tpl,
			$out,
			$vars['options'] ?? []
		) : $this->render->parse( $tpl, $out );
		
		return 
		$this->render->parse( 
			$this->render->template( 'tpl_form_input_wrap' ), 
			[ 
				'input_wrap_before'	=> $ctrl->stringResult( 'inputwrapbefore' ),
				'input_wrap_after'	=> $ctrl->stringResult( 'inputwrapafter' ),
				'input'			=> $input
			] 
		);
	}
	
	/**
	 *  Create select input field from options
	 */
	public function createSelect(
		string		$tpl, 
		array		$vars, 
		array		$opts 
	) : string {
		$out	= '';
		
		foreach ( $opts as $o ) {
			$out	.= 
			( empty( $o[0] ) && empty( $o[1] ) ) ? 
			$this->render->template( 'tpl_input_unselect' ) : 
			$this->render->parse( 
				$this->render->template( 'tpl_input_select_opt' ), 
				[
					'value'		=> $o[0],
					'text'		=> $o[1],
					'selected'	=> 
					empty( $o[2] ) ? '' : 
						( $o[2] ? 'selected' : '' )
				] 
			);
		}
		
		return 
		$this->render->parse( $tpl, \array_merge( $vars, [ 
			'options' => $out 
		] ) );
	}
	
	/**
	 *  Create select box and wrap data in 'before' and 'after' event output
	 *  
	 *  @param string	$before		Before template parsing event
	 *  @param string	$after		After template parsing event
	 *  @param string	$tpl		Base component template
	 *  @param array	$input		Raw select dropdown data
	 *  @param array	$opts		Select dropdown options list
	 *  
	 *  @return string
	 */
	public function hookSelectWrap( 
		string		$before, 
		string		$after, 
		string		$tpl, 
		array		$input, 
		array		$opts 
	) {
		return 
		$this->ctrl->wrap( 
			$before, 
			$after, 
			$this->createSelect( $tpl, $input, $opts ),
			$input	
		);
	}
	
	/**
	 *  Build multi-line/textarea/wysiwyg text field
	 *  
	 *  @param string	$type		Field input type
	 *  @param array	$field		Preset field properties
	 *  @return string
	 */
	public function createMultiline(
		string		$type,
		array		&$field 
	) : string {
		
		$config = $this->ctrl->getConfig();
		// Set textarea defaults
		$field['rows'] = 
		\PubCabin\Util::intRange(
			$field['rows'] ?? 
			$config->setting( 
				'render_multiline_rows', 
				'int' 
			) ?? self::RENDER_MULTILINE_ROWS,
			1, 10000
		);
		$field['cols'] = 
		\PubCabin\Util::intRange(
			$field['cols'] ?? 
			$config->setting( 
				'render_multiline_cols', 
				'int' ) ?? self::RENDER_MULTILINE_COLS,
			1, 1000
		);
		
		// Send back preset template or wysiwyg/multiline
		return 		
		$field['template'] ?? 
		$this->render->template( 
			( 0 == \strcmp( $field['type'], 'wysiwyg' ) ) ? 
			'tpl_input_wysiwyg' : 'tpl_input_multiline'			
		);
	}
	
	/**
	 *  Form field template selection helper based on input type
	 *  
	 *  @param array	$field		Form field parameters
	 */
	public function createFormField( array $field ) {
		$tpl	= '';
		
		$field	= $this->fieldPrefilter( $field );
		$type	= $field['type'];
		
		// Try to retrieve given template or use default based on type
		switch ( $type ) {
			case 'select':
			case 'timezone':
				$tpl = $field['template'] ?? 
				$this->render->template( 'tpl_input_select' );
				break;
			
			case 'text':
				$tpl = $field['template'] ?? 
				$this->render->template( 'tpl_input_text' );
				break;
				
			case 'datetime-local':
				$tpl = $field['template'] ?? 
				$this->render->template( 'tpl_input_datetime' );
				break;
				
			case 'email':
				$tpl = $field['template'] ?? 
				$this->render->template( 'tpl_input_email' );
				break;
				
			case 'pass':
			case 'password':
				$tpl = $field['template'] ?? 
				$this->render->template( 'tpl_input_pass' );
				break;
			
			case 'wysiwyg':
			case 'textarea':
			case 'multiline':
				$tpl = 
				$this->createMultiline( $type, $field );
			
				break;
				
			case 'checkbox':
				$tpl = 
				$field['template'] ?? 
					$this->render->template( 'tpl_input_checkbox' );
				break;
				
			case 'file':
			case 'upload':
				$tpl = 
				$field['template'] ?? 
					$this->render->template( 'tpl_input_upload' );
				break;
			
			// This only works if 'type' is given, E.G. number, range etc...
			default:
				$tpl = 
				$field['template'] ?? \strtr( 
					$this->render->template( 'tpl_input_field' ), 
					[ '{input}' => $this->render->template( 'tpl_input' ) ]
				);
				
		}
		
		// Base fallback if supported input type
		if ( 
			empty( $tpl ) && 
			\in_array( $type, static::$input_types ) 
		) {
			$tpl = $this->create( $field );
		}
		
		return 
		$this->createInputField( $field['name'], $tpl, $field );
	}
}



