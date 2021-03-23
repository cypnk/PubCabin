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
		return [ 'Hooks', 'Sessions' ];
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
		// Base HTML whitelist
		$white	= [
			'html'	=> 
			$this->getConfig()->setting( 'tag_white' )
		];
		
		// Add form tag whitelist if this is a form
		if ( $form ) {
			$white['form']	= 
			$this->getConfig()->setting( 'form_white' );
		}
		
		return Html::html( $html, '/', $white );
	}
}


