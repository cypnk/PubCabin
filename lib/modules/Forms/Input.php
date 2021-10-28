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
	const RENDER_MULTILINE_COLS = 60;
	
	/**
	 *  Default textarea rows
	 */
	const RENDER_MULTILINE_ROWS = 10;
	
	/**
	 *  Form module
	 */
	protected $module;
	
	/**
	 *  Supported input types
	 *  @var array
	 */
	protected static $input_types = [
		'text', 'password', 'textarea', 'search', 'select', 'email', 
		'radio', 'checkbox', 'number', 'range', 'datetime-local', 
		'file', 'submit', 'button', 'hidden', 'captcha', 'color', 
		'tel', 'url', 'wysiwyg'
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
HTML;
	
	
	public function __construct( 
		\PubCabin\Modules\Forms\Module $_module 
	) {
		$this->module = $_module;
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
		$hooks	= $this->module->getModule( 'Hooks' );
		$hooks->event( [ 
			'autocompleteopts',
			[ 'options' => static::$autocomplete ]
		] );
		$hooks->event( [
			'inputtemplates',
			[ 'types' => static::$templates ]
		] );
		$hooks->event( [ 
			'wysiwygtools', 
			[ 'tools' => static::$wysiwyg_tools ] 
		] );
		
		
		// Modify base supported input type
		static::$input_types	= 
		\array_merge( 
			static::$input_types, 
			$hooks->arrayResult( 
				'inputtypes' 
			)['types'] ?? [] 
		);
		
		// Add/append autocomplete properties
		static::$autocomplete	= 
		\array_merge( 
			static::$autocomplete, 
			$hooks->arrayResult( 
				'autocompleteopts' 
			)['options'] ?? [] 
		);
		
		// Extend base templates
		static::$templates	= 
		\array_merge( 
			static::$templates, 
			$hooks->arrayResult( 
				'inputtemplates', [] 
			)['templates'] ?? [] 
		);
		
		// New tools or new wysiwyg functionality?
		$nt	= $hooks->arrayResult( 'wysiwygtools' );
		
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
		// Find extras if present
		$extras = empty( $params['extras'] ) ? 
				'' : $params['extras'];
		
		$acompl	= 
		\PubCabin\Util::lowercase( $params['autocomplete'] ?? '' );
		
		// Prepend autocomplete to existing extras if present
		if ( \in_array( $acompl, static::$autocomplete ) ) {
			$params['extras'] = 
				"autocomplete=\"{$acompl}\"" . $extras;
		}
		
		// No description?
		if ( !isset( $params['description'] ) ) {
			
			// No label either?
			if ( !isset( $params['label'] ) ) {
				
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
		\PubCabin\Util::lowercase( $field['type'] ?? 'text' );
		
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
		$render	= $this->module->getRender();
		
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
					$render->template( 'tpl_input_submit' ) : 
					$render->template( 'tpl_input_button' );
				
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
		$config = $this->module->getConfig();
		
		// Check posting method
		if ( 0 != \strncasecmp( $method, 'get' ) ) {
			$ap	= 
			$config->setting( 'allow_post', 'bool' ) ?? false;
			
			if ( !$ap || 0 != \strncasecmp( $method, 'post' )) {
				// Don't build this form in a method that isn't allowed
				return '';
			}
		}
		
		$hooks	= $this->module->getModule( 'Hooks' );
		$render	= $this->module->getRender();
		
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
		
		// Pre-input hooks
		$hooks->event( [ 'formbefore', $opts ] );
		
		// Call block level or inline level form hooks
		// Replace input fields if needed
		if ( $is_block ) {
			$hooks->event( [ 'formblockbefore', $opts ] );
			$opts = $hooks->arrayResult( 'formblockbefore', $opts );
		} else {
			$hooks->event( [ 'forminlinebefore', $opts ] );
			$opts = $hooks->arrayResult( 'formblockbefore', $opts );
		}
		
		$hooks->event( [ 'forminputbefore', $opts ] );
		
		// Create anti-XSRF token fields before other fields
		$pair	= $this->module->genNoncePair( $name );
		$out	= 
		$hooks->wrap( 
			'before'. $name .'xsrf',
			'after'. $name .'xsrf',
			$render->template( 'tpl_input_xsrf' ), 
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
				$hooks->event( [ 'wysiwygload', [ 'field' => $f ] );
			} elseif ( $cap ) {
				$hooks->event( [ 'captchaload', [ 'field' => $f ] );
			}
			
			$out .= $this->createFormField( $f );
			
			if ( $wys ) {
				$hooks->event( [ 'wysiwygload', '' ] );
			} elseif ( $cap ) {
				$hooks->event( [ 'captchaload', '' ] );
			}
		}
		
		// Append buttons
		$hooks->event( [ 'buttonwrapbefore', $opts ] );
		$hooks->event( [ 'buttonwrapafter', $opts ] );
		
		$out	.= 
		$render->parse( $render->template( 'tpl_form_button_wrap' ), [ 
			'button_wrap_before'	=> $hooks->stringResult( 'buttonwrapbefore' ),
			'button_wrap_after'	=> $hooks->stringResult( 'buttonwrapafter' ),
			'buttons'		=> $this->createButtons( $buttons )
		] );
		
		// Post-input hooks
		$hooks->event( [ 'forminputafter', $opts ] );
		if ( $is_block ) {
			$hooks->event( [ 'formblockafter',  $opts ] );
		} else {
			$hooks->event( [ 'forminlineafter', $opts ] );
		}
		
		// Form after event
		$hooks->( [ 'formafter', $opts ] );
		
		// Append template placeholders
		$vars	= [
			'form_before'		=> $hooks->stringResult( 'formbefore' ), 
			'form_after'		=> $hooks->stringResult( 'formafter' ),
			'form_input_before'	=> $hooks->stringResult( 'forminputbefore' ),
			'form_input_after'	=> $hooks->stringResult( 'forminputafter' ),
			'fields'		=> $out
		];
		
		if ( $is_block ) {
			$vars['form_block_before']	= $hooks->stringResult( 'formblockbefore' );
			$vars['form_block_after']	= $hooks->stringResult( 'formblockafter' );
		} else {
			$vars['form_inline_before']	= $hooks->stringResult( 'forminlinebefore' );
			$vars['form_inline_after']	= $hooks->stringResult( 'forminlineafter' );
		}
		
		return $render->parse( $render->template( $tpl ), $vars );
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
		// Set field ID if not already set
		$vars['id']	= $vars['id'] ?? $name;
		
		// Input specific hook events
		$nbf		= 'input' . $name . 'before';
		$naf		= 'input' . $name . 'after';
		
		// Hook settings
		$opts		= [ 'name' => $name, 'details' => $vars ];
		
		$hooks	= $this->module->getModule( 'Hooks' );
		$render	= $this->module->getRender();
		
		/**
		 *  Run field hooks
		 */
		// General input before/after hooks
		$hooks->event( [ 'inputbefore', $opts ] );
		$hooks->event( [ 'inputafter', $opts ] );
		
		// Input name specific before/after hooks
		$hooks->event( [ $nbf, $opts ] );
		$hooks->event( [ $naf, $opts ] );
		
		// Input label and special detail hooks
		$hooks->event( [ 'labelbefore', $opts ] );
		$hooks->event( [ 'labelafter', $opts ] );
		
		$hooks->event( [ 'specialbefore', $opts ] );
		$hooks->event( [ 'specialafter', $opts ] );
	
		// Input field hooks
		$hooks->event( [ 'inputfieldbefore', $opts ] );
		$hooks->event( [ 'inputfieldafter', $opts ] );
		
		// Description/help info hooks
		$hooks->event( [ 'desc_before', $opts ] );
		$hooks->event( [ 'desc_after', $opts ] );
		
		// Form field input wrap
		$hooks->event( [ 'inputwrapbefore', $opts ] );
		$hooks->event( [ 'inputwrapafter', $opts ] );
		
		$out		= 
		\array_merge( $vars, [
			'input_before'			=> $hooks->stringResult( 'inputbefore' ),
			'input_after'			=> $hooks->stringResult( 'inputafter' ),
			
			'input_' . $name .'_before'	=> $hooks->stringResult( $nbf ),
			'input_' . $name .'_after'	=> $hooks->stringResult( $naf ),
			
			'label_before'			=> $hooks->stringResult( 'labelbefore' ),
			'input_after'			=> $hooks->stringResult( 'labelafter' ),
			
			'special_before'		=> $hooks->stringResult( 'specialbefore' ),
			'special_after'			=> $hooks->stringResult( 'specialafter' ),
			
			'input_field_before'		=> $hooks->stringResult( 'inputfieldbefore' ),
			'input_field_before'		=> $hooks->stringResult( 'inputfieldafter' ),
			
			'desc_before'			=> $hooks->stringResult( 'descbefore' ),
			'desc_after'			=> $hooks->stringResult( 'descafter' )
		] );
		
		// Select is a special type
		$input	= 
		( 0 === \strcmp( $vars['type'], 'select' ) ) ? 
			$this->createSelect(
			$tpl,
			$out,
			$vars['options'] ?? []
		) : $render->parse( $tpl, $out );
		
		return 
		$render->parse( 
			$render->template( 'tpl_form_input_wrap' ), 
			[ 
				'input_wrap_before'	=> $hooks->stringResult( 'inputwrapbefore' ),
				'input_wrap_after'	=> $hooks->stringResult( 'inputwrapafter' ),
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
		$render	= $this->module->getRender();
		
		foreach ( $opts as $o ) {
			$out	.= 
			$render->parse( 
				$render->template( 'tpl_input_select_opt' ), 
				[
					'value'		=> $o[0],
					'text'		=> $o[1],
					'selected'	=> $o[2] ? 'selected' : ''
				] 
			);
		}
		
		return 
		$render->parse( $tpl, \array_merge( $vars, [ 
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
		$this->module->getModule( 'Hooks' )->wrap( 
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
		
		$render	= $this->module->getRender();
		$config = $this->module->getConfig();
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
		$render->template( 
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
		
		$render	= $this->module->getRender();
		
		// Try to retrieve given template or use default based on type
		switch ( $type ) {
			case 'select':
				$tpl = $field['template'] ?? 
				$render->template( 'tpl_input_select' );
				break;
			
			case 'text':
				$tpl = $field['template'] ?? 
				$render->template( 'tpl_input_text' );
				break;
				
			case 'datetime-local':
				$tpl = $field['template'] ?? 
				$render->template( 'tpl_input_datetime' );
				break;
				
			case 'email':
				$tpl = $field['template'] ?? 
				$render->template( 'tpl_input_email' );
				break;
				
			case 'pass':
			case 'password':
				$tpl = $field['template'] ?? 
				$render->template( 'tpl_input_pass' );
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
					$render->template( 'tpl_input_checkbox' );
				break;
				
			case 'file':
			case 'upload':
				$tpl = 
				$field['template'] ?? 
					$render->template( 'tpl_input_upload' );
				break;
			
			// This only works if 'type' is given, E.G. number, range etc...
			default:
				$tpl = 
				$field['template'] ?? \strtr( 
					$render->template( 'tpl_input_field' ), 
					[ '{input}' => $render->template( 'tpl_input' ) ]
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



