"use strict";

(function() {
/**
 *  Basic editor test code
 *  TODO: Replace with actual form code
 */

const 

// Toolbar wrapper
tw	= `<ul>{toolbar}</ul>`,

// Toolbar button
tt	= `
<li>
	<a href="#" data-cmd="{cmd}">
		<img src="{icon}" alt="{title}" title="{title}">
	</a>
</li>
`,

// Toolbar commands
tc	= {
	"bold" : {
		"icon" : "icons/format_bold-24px.svg",
		"title": "Bold"
	},
	"italic": {
		"icon": "icons/format_italic-24px.svg",
		"title": "Italic"
	},
	"underline": {
		"icon": "icons/format_underlined-24px.svg",
		"title": "Underline"
	},
	"unordered": {
		"icon": "icons/format_list_bulleted-24px.svg",
		"title": "Bullet list"
	},
	"numbered": {
		"icon": "icons/format_list_numbered-24px.svg",
		"title": "Numbered list"
	},
	"heading": {
		"icon": "icons/title-24px.svg",
		"title": "Heading"
	},
	"link": {
		"icon": "icons/insert_link-24px.svg",
		"title": "Link"
	},
	"blockquote": {
		"icon": "icons/format_quote-24px.svg",
		"title": "Quote"
	},
	"code": {
		"icon": "icons/code-24px.svg",
		"title": "Code"
	},
	"image": {
		"icon": "icons/insert_photo-24px.svg",
		"title": "Image"
	}, /*
	"left" : {
		"icon": "icons/format_align_left-24px.svg",
		"title": "Justify left"
	}, 
	"center" : {
		"icon": "icons/format_align_center-24px.svg",
		"title": "Justify center"
	}, 
	"right" : {
		"icon": "icons/format_align_right-24px.svg",
		"title": "Justify right"
	}, 
	"indent" : {
		"icon": "icons/format_indent_increase-24px.svg",
		"title": "Increase indent"
	}, 
	"outdent" : {
		"icon": "icons/format_indent_decrease-24px.svg",
		"title": "Decrease indent"
	},
	"comment": {
		"icon": "icons/insert_comment-24px.svg",
		"title": "Add comment"
	}, */
	"undo": {
		"icon": "icons/undo-24px.svg",
		"title": "Undo"
	},
	"redo": {
		"icon": "icons/redo-24px.svg",
		"title": "Redo"
	}
	
};

Squire.prototype.testStyle = function( format, pattern ) {
	var 
	path = this.getPath(),
	test = ( pattern.test( path ) | this.hasFormat( format ) );
	return ( test ) ? true : false;
};

Squire.prototype.makeFormat = function( t ) {
	return this.modifyBlocks( ( frag ) => {
		var 
		output	= this._doc.createDocumentFragment(),
		block	= frag;
		while ( block = Squire.getNextBlock( block ) ) {
			output.appendChild(
				this.createElement( 
					t, [ Squire.empty( block ) ] 
				)
			);
		}
		return output;
	} );
};


const 

// Create a new element
create		= ( name ) => {
	return document.createElement( name );
},

// Set element attribute
attr = ( e, name, value, remove ) => {
	remove = remove || false;
	if ( remove ) {
		e.removeAttribute( name );
		return;
	}
	e.setAttribute( name, value );
},

// Get element attribute with optional default value
getAttr = ( e, n, v ) => {
	return e.getAttribute( n ) || v || '';
},

// Query the DOM or a specific parent node for elements
find = ( s, e, n ) => {
	e = e || document;
	const q = e.querySelectorAll(s);
	if ( n && q.length ) {
		return q[0];
	}
	return q;
},

// Check if element is of given type(s)
isA = ( target, types ) => {
	const
	typ = types.split( ',' ).map( e => e.trim() ),
	len = typ.length;
	
	for ( let i = 0; i < len; i++ ) {
		if ( typ[i].toUpperCase() == target.nodeName ) {
			return true;
		}
	}
	
	return false;
}, 

// Get text until last occurence of a character
textUntil	= ( txt, ch ) => {
	if ( txt.lastIndexOf( ch ) == ( txt.length - 1 ) ) {
		return '';
	}
	
	return txt.substring( 
		txt.lastIndexOf( ch ), ( txt.length - 1 )
	).trim();
},

// Get text from the first occurence of a character
textFrom	= ( txt, ch ) => {
	if ( ( txt.indexOf( ch ) + 1 ) > ( txt.length - 1 ) ) {
		return ''
	}
	return txt.substring( 
		( txt.indexOf( ch ) + 1 ), 
		( txt.length - 1 ) 
	);
},

// Remove text until given character index
cutUntil	= ( txt, ch ) => {
	if ( txt.lastIndexOf( ch ) == ( txt.length - 1 ) ) {
		return '';
	}
	
	return txt.substring( 0, txt.lastIndexOf( ch ) ).trim();
},

// Remove last n chracters from string
cut		= ( str, n ) => {
	return str.substring( 0, str.length - n );
},

// Trim text to given length
trunc		= ( str, n ) => {
	return ( str.length > n ) ? 
		str.substring( 0, n ) : str;
},

// Check if returned prompt was empty
emptyPrompt = ( pt, ep ) => {
	ep = ep || 'http://';
	if ( pt == null || pt == '' || pt == ep ) {
		return true;
	}
	return false;
},

// Attach event listener
listen = ( target, events, func, capture ) => {
	const
	val	= events.split( ',' ).map( e => e.trim() ),
	len	= val.length;
	
	capture = capture || false;
	
	for ( let i = 0; i < len; i++ ) {
		target.addEventListener( val[i], func, capture );
	}
},

// Find the special function sent ( E.G. return "s" if "ctrl + s" was used )
spec		= ( e ) => {
	// Control/Command or alt key?
	if ( e.ctrlKey || e.metaKey ) {
		// Send back the character code in lowercase
		return String.fromCharCode( e.which ).toLowerCase();
	}
	
	// Or send blank if not a command key
	return '';
},

// Check if given key is a printable character
printable	= ( e ) => {
	const key =  e.keyCode || e.charCode || e.which;
	return ( key > 47 && key < 58 )		|| 
		( key == 32 || key == 13 )	||
		( key > 64 && key < 91 )	|| 
		( key > 95 && key < 122 )	|| 
		( key > 185 && key < 193 )	|| 
		( key > 218 && key < 223 ); 
},

// Computed style with important properties
style		= ( box ) => {
	const 
	st	= window.getComputedStyle( box ),
	rc	= box.getBoundingClientRect();
	
	return { 
		"x"			: rc.x,  
		"y"			: rc.y, 
		"top"			: rc.top, 
		"left"			: rc.left, 
		"right"			: rc.right, 
		"bottom"		: rc.bottom, 
		"width"			: rc.width,  
		"height"		: rc.height,
		
		"display"		: st.display,
		
		"lineHeight"		: cut( st.lineHeight, 2 ),
		"fontSize"		: cut( st.fontSize, 2 ),
		"origin"		: st.transformOrigin,
		"insize"		: cut( st.inlineSize, 2 ),
		"block"			: cut( st.blockSize, 2 ),
		
		"marginTop"		: cut( st.marginTop, 2 ),
		"marginBottom"		: cut( st.marginBottom, 2 ),
		
		"paddingTop"		: cut( st.paddingTop, 2 ),
		"paddingBottom"		: cut( st.paddingBottom, 2 ),
		"paddingLeft"		: cut( st.paddingLeft, 2 ),
		"paddingRight"		: cut( st.paddingRight, 2 ),
		
		"paddingInlineStart"	: cut( st.paddingInlineStart, 2 ),
		"paddingInlineEnd"	: cut( st.paddingInlineEnd, 2 )
	};
},

// Template placeholder replacements
template = ( tpl, data ) => {
	for ( var key in data ) {
		tpl	= 
		tpl.replace( 
			new RegExp( '{' + key + '}', 'g' ), 
			data[key] 
		);
	}
	
	return tpl;
},

// Selected text range
selection	= ( box ) => {
	const v = ( box.value ) ? box.value : box.innerHTML;
	return {
		"start"		: box.selectionStart,
		"end"		: box.selectionEnd,
		"range"		: ( v == '' ) ? '' : v.substring( 
					box.selectionStart, 
					box.selectionEnd 
				).trim()
	};
},

// Get passed parameter options
getOptions	= ( opts ) => {
	opts		= opts || '';
	const params	= {};
	
	// Nothing set
	if ( !opts.length ) {
		return params;
	}
	
	// Find parameter values
	opts.split( '&' ).filter( function( c ) {
		const p = c.split( '=' ).map( e => e.trim() );
		params[decodeURIComponent(p[0])] = 
			decodeURIComponent( 
				( p[1] || '' ).replace( /\+/g, '%20' )
			);	
	} );
	return params;
}, 

// Separate multiple named items in a single parameter
getNames	= ( opts ) => {
	// No names?
	if ( !opts ) { return {}; }
	
	return opts.split( ';' ).map( e => e.trim() );
},

// Get start and end positions between delimiter
getExcerpt	= ( box, ch ) => {
	const
	sel	= selection( box ),
	st	= box.value.lastIndexOf( ch, sel.start ),
	ed	= box.value.indexOf( ch, sel.end );
	
	return { 
		"start"	: st < 0 ? 0 : st, 
		"end"	: ed < 0 ? 0 : ed
	};
},

// Return unique items with empty items removed
uniqueWords	= ( words, ch1, ch2, max ) => {
	// Use split character to make array. Remove empty/whitespace
	const ar	= 
	words.split( ch1 )
		.map( e => e.trim() )
		.filter( e => /\S/.test( e ) )
		.filter( e => function( v ) {
			return v.toLowerCase();
		} );
	
	// Remove duplicates and use join character to combine words
	return ar.filter( function( v, i, s ) {
		return i === s.indexOf( v.toLowerCase() );
	} ).join( ch2 );
},

// Get/copy clipboard data as text
getClipboard	= ( e ) => {
	if ( e.clipboardData ) {
		return e.clipboardData.getData( 'text/plain' );
	}
	
	if ( window.clipboardData ) {
		return window.clipboardData.getData( 'Text' );
	}
	return '';
},


/**
 *  Feature helpers
 */

// Convert title text to slug
makeSlug	= ( sx, v, m ) => {
	v		= 
	v.toLowerCase()
		// Remove non-letters
		// Firefox fallback (to be removed in the future)
		.replace( /[\u0300-\u036F]/g, '' ) 
		.replace( /[\u2000-\u206F\u2E00-\u2E7F]/g, '' )
		
		// Normalize and remove punctuation. Firefox fallback
		.normalize( 'NFKD' )
		.replace( /[\'〝〟‘’“”『』「」〈〉《》【】（）]/g, '' )
		.replace( /[\.,\/\\#!$%\^&\*;\:{}=\_\"`~()\[\]\+\|<>\?]/g, ' ' )
		
		//.replace( /^\p{L}+/u, '' ) (Chrome, Safari)
		.replace( /^\s+|\s+$/g, '' )
		.replace( / +/g,'-' )
		.replace( /-+/g,'-' )
		.replace( /-+$/, '' )
		.replace( /^-+/, '' );
	
	sx.value	= trunc( v, m ).replace( /^\-+|-\-+$/g, '' );
},

// Auto-adjust textarea height
autoheight	= ( txt ) => {
	// Reset
	if ( txt.value != '' ) {
		txt.style.height	= 'auto';
	}
	// TODO: Make padding configurable
	txt.style.height	= txt.scrollHeight + 3 + 'px';
},

// Insert or replace currently clicked tag
insertTag	= ( box, tag ) => {
	var c;
	
	const
	v = box.value,
	t = getExcerpt( box, ',' );
	
	// There's a current tag clicked? 
	if ( t.end ) {
		var
		ts	= v.substring( 0, t.start ),
		te	= v.substring( t.end );
		
		// Replace current tag with new word
		c	= ts.trim() + ',' + tag + ',' + te.trim();
		
	// Or append new tag to end
	} else {
		c = cutUntil( v, ',' ) + ',' + tag.trim();
	}
	
	// Remove any extra commas, extra spaces, duplicates etc...
	box.value	= uniqueWords( c, ',', ', ' );
},

// Basic tags
formatting	= {
	bold: function( ed ) {
		return ed.testStyle( 'B', ( />B\b/ ) ) ? 
			ed.removeBold() : ed.bold();
	}, 
	italic: function( ed ) {
		return ed.testStyle( 'I', ( />I\b/ ) ) ? 
			ed.removeItalic() : ed.italic();
	}, 
	underline: function( ed ) {
		return ed.testStyle( 'U', ( />U\b/ ) ) ? 
			ed.removeUnderline() : ed.underline();
	}, 
	unordered: function( ed ) {
		return ed.testStyle( 'UL', ( />UL\b/ ) ) ? 
			ed.removeList() : ed.makeUnorderedList();
	}, 
	numbered: function( ed ) {
		return ed.testStyle( 'OL', ( />OL\b/ ) ) ? 
			ed.removeList() : ed.makeOrderedList();
	},
	blockquote: function( ed ) {
		return ed.testStyle( 'blockquote', ( />blockquote\b/ ) ) ? 
			ed.decreaseQuoteLevel() : ed.increaseQuoteLevel();
	},
	
	//Code doesn't work?
	code: function( ed ) {
		return ed.testStyle( 'code', ( />code\b/ ) ) ? 
			ed.removeCode() : ed.code();
	}
}, 

// Special tags
special		= {
	heading: function( ed ) {
		if ( ed.testStyle( 'H2', ( />H2\b/ ) ) ) {
			ed.makeFormat( 'P' );
			return;
		}
		ed.makeFormat( 'H2' );
	},
	link: function( ed ) {
		var 
		ep = 'http://',
		pt = prompt( 'Enter Link URL', ep );
		if ( emptyPrompt( pt, ep ) ) {
			return;
		}
		ed.makeLink( pt );
	},
	image: function( ed ) {
		// Get URL from prompt;
		var 
		ep = 'http://',
		pt = prompt( 'Enter Image URL', ep );
		
		if ( emptyPrompt( pt, ep ) ) {
			return;
		}
		ed.insertImage( pt );
	}
},

// TODO: Dropdown and selectors
drop	= {
	font: () => {
		
	},
	format: () => {
		
	},
	embed: () => {
		
	}
},

// Apply toolbar command actions
action	= ( tb, ed ) => {
	
	[...find( '[data-cmd]', tb )].forEach( function( b ) {
		listen( b, 'click', ( e ) => {
			e.preventDefault();
			
			// Get command name
			const ac = getAttr( b, 'data-cmd' );
			switch( ac ) {
				case 'bold':
				case 'italic':
				case 'underline':
				case 'unordered':
				case 'numbered':
				case 'blockquote':
				case 'code':
					formatting[ac]( ed );
					break;
					
				case 'heading':
				case 'link':
				case 'image':
					special[ac]( ed );
					break;
					
				case 'font':
				case 'format':
				case 'embed':
					drop[ac]( ed );
					break;
					
				case 'undo':
					ed.undo();
					break;
					
				case 'redo':
					ed.redo();
					break;
			}
			
			// After doing the thing, return focus to editor
			ed.focus();
		}, false );
	} );
},

// Create Squire editor
makeEditor	= ( txt ) => {
	// Create WYSIWYG container
	const dv = create( 'div' );
	dv.classList.add( 'wysiwyg' );
	txt.parentNode.insertBefore( dv, txt );
	
	// TODO: Add aria-described-by to WYSIWYG
	
	// Hide text after insert
	txt.style.display = 'none';
	
	
	const 
	// Create new Squire WYSIWYG
	ed = new Squire( dv, { blockTag: 'p' } ),
	
	// Create toolbar
	tb = create( 'nav' );
	
	// Toolbar template
	var h = '';
	
	// Toolbar buttons
	[...Object.keys(tc)].forEach( ( t ) => {
		h += template( tt, {
			"cmd" : t,
			"icon": tc[t].icon,
			"title" : tc[t].title
		} );
	} );
	
	// Update original text on blur
	listen( ed, 'blur', ( e ) => {
		txt.value = ed.getHTML();
	}, false );
	
	// Append classes and template
	tb.classList.add( 'toolbar' );
	tb.innerHTML = template( tw, { "toolbar": h } );
	dv.parentNode.prepend( tb );
	
	action( tb, ed );
},

findFeatures	= ( box ) => {
	const 
	at	= getAttr( box, 'data-feature' ),
	ft	= at.split( ',' ).map( a => a.trim() );
	
	if ( !Array.isArray( ft ) || !ft.length ) {
		return;
	}
	
	ft.map( f => {
		// Optional parameters
		const p	= f.split( ':' ).map( i => i.trim() );
		
		// Empty if not set
		p[1]	= p[1] || '';
		
		switch( p[0] ) {
			
			case 'autoheight':
				makeAutoheight( box );
				break;
			
			case 'slug':
				makeTitleSlug( box, p[1] );
				break;
				
			case 'tags':
				makeTagable( box, p[1] );
				break;
			
			case 'wysiwyg':
				makeEditor( box, p[1] );
				break;
				
			//case 'droppable':
			//	makeDroppable( box, p[1] );
			//	break;
		}
	} );
};


// Setup environment
listen( window, 'load', function() {
	const
	ft	= find( '[data-feature]' );
	
	Array.from( ft ).map( w => {
		findFeatures( w );
	});
}, false );

} )(); // End


