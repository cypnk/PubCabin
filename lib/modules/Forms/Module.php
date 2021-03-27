<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Module/Forms/Module.php
 *  @brief	User input, form field generator, and handler
 */
namespace PubCabin\Modules\Forms;

class Module extends \PubCabin\Modules\Module {
	
	// Form check statuses
	const FORM_STATUS_VALID		= 0;
	const FORM_STATUS_INVALID	= 1;
	const FORM_STATUS_EXPIRED	= 2;
	const FORM_STATUS_FLOOD		= 3;
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Files' ];
	}
	
	/**
	 *  Submitted HTML formatting helper
	 *  
	 *  @param string	$html	Raw input from the user
	 *  @param string	$path	Relative link URL path
	 *  @param bool		$form	Allow form field related HTML
	 * 
	 */
	public function formatHTML(
		string		$html, 
		string		$path	= '/', 
		bool		$form	= false
	) : string {
		$config	= $this->getConfig();
		
		// Base HTML whitelist
		$white	= [
			'html'	=> $config->setting( 'tag_white', 'json' )
		];
		
		// Add form tag whitelist if this is a form
		if ( $form ) {
			$white['form']	= 
			\array_merge( 
				$white['html'], 
				$config->setting( 'form_white', 'json' )
			);
		}
		
		return Html::html( $html, $path, $white );
	}
}


