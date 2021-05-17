"use strict";

/**
 *  Basic editor test code
 *  TODO: Replace with actual form code
 */

const 
dt	= document.querySelectorAll( "details" ),

// Editor element
dv	= document.getElementById( 'editor' ),

// Create new Squire WYSIWYG
ed	= new Squire( dv, { blockTag: 'p' } ),

// Toolbar element
tb	= document.createElement( 'nav' ),

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
}


const 

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

// Basic tags
formatting = {
	bold: function() {
		return ed.testStyle( 'B', ( />B\b/ ) ) ? 
			ed.removeBold() : ed.bold();
	}, 
	italic: function() {
		return ed.testStyle( 'I', ( />I\b/ ) ) ? 
			ed.removeItalic() : ed.italic();
	}, 
	underline: function() {
		return ed.testStyle( 'U', ( />U\b/ ) ) ? 
			ed.removeUnderline() : ed.underline();
	}, 
	unordered: function() {
		return ed.testStyle( 'UL', ( />UL\b/ ) ) ? 
			ed.removeList() : ed.makeUnorderedList();
	}, 
	numbered: function() {
		return ed.testStyle( 'OL', ( />OL\b/ ) ) ? 
			ed.removeList() : ed.makeOrderedList();
	},
	blockquote: function() {
		return ed.testStyle( 'blockquote', ( />blockquote\b/ ) ) ? 
			ed.decreaseQuoteLevel() : ed.increaseQuoteLevel();
	},
	
	//Code doesn't work?
	code: function() {
		return ed.testStyle( 'code', ( />code\b/ ) ) ? 
			ed.removeCode() : ed.code();
	}
}, 

// Special tags
special = {
	heading: function() {
		if ( ed.testStyle( 'H2', ( />H2\b/ ) ) ) {
			ed.makeFormat( 'P' );
			return;
		}
		ed.makeFormat( 'H2' );
	},
	link: function() {
		var 
		ep = 'http://',
		pt = prompt( 'Enter Link URL', ep );
		if ( emptyPrompt( pt, ep ) ) {
			return;
		}
		ed.makeLink( pt );
	},
	image: function() {
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
action = ( b ) => {
	listen( b, 'click', ( e ) => {
		e.preventDefault();
		const ac = getAttr( b, 'data-cmd' );
		switch( ac ) {
			case 'bold':
			case 'italic':
			case 'underline':
			case 'unordered':
			case 'numbered':
			case 'blockquote':
			case 'code':
				formatting[ac]();
				break;
				
			case 'heading':
				special.heading();
				break;
				
			case 'link':
				special.link();
				break;
				
			case 'image':
				special.image();
				break;
				
			case 'font':
				drop.font();
				break;
				
			case 'format':
				drop.format();
				break;
				
			case 'embed':
				drop.embed();
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
},

// Create toolbar
toolbar = () => {
	var h = '';
	
	// Toolbar buttons
	[...Object.keys(tc)].forEach( ( t ) => {
		h += template( tt, {
			"cmd" : t,
			"icon": tc[t].icon,
			"title" : tc[t].title
		} );
	} );
	
	tb.classList.add( 'toolbar' );
	tb.innerHTML = template( tw, { "toolbar": h } );
	dv.parentNode.prepend( tb );
	
	// Toolbar actions
	[...find( '[data-cmd]' )].forEach( action );
};

// Init toolbar
toolbar();


