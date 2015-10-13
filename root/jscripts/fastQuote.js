/**
 * This file is part of Fast Quote plugin for MyBB.
 * Copyright (C) Lukasz Tkacz <lukasamd@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */ 
 
/* All code from phpBB3 SEO Premod, based on GPLv2 License */ 
function initInsertions() 
{
	var doc;

	if (document.forms[form_name])
	{
		doc = document;
	}
	else 
	{
		doc = opener.document;
	}

	var textarea = doc.forms[form_name].elements[text_name];
	if (is_ie && typeof(baseHeight) != 'number')
	{	
        setTimeout(function() { textarea.focus(); }, 10);
		baseHeight = doc.selection.createRange().duplicate().boundingHeight;

		if (!document.forms[form_name])
		{
            setTimeout(function() { document.body.focus(); }, 10);
		}
	}
}

/**
* Insert text at position
*/
function insert_text(text, spaces, popup)
{
	var textarea;
	
	if (!popup) 
	{
		textarea = document.forms[form_name].elements[text_name];
	} 
	else 
	{
		textarea = opener.document.forms[form_name].elements[text_name];
	}
	if (spaces) 
	{
		text = ' ' + text + ' ';
	}
	
	if (!isNaN(textarea.selectionStart))
	{
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '');
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	}	
	
	else if (textarea.createTextRange && textarea.caretPos)
	{
		if (baseHeight != textarea.caretPos.boundingHeight) 
		{
            setTimeout(function() { textarea.focus(); }, 10);
			storeCaret(textarea);
		}		
		var caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
		
	}
	else
	{
		textarea.value = textarea.value + text;
	}
	if (!popup) 
	{
        setTimeout(function() { textarea.focus(); }, 10);
	} 	

}



// Startup variables
var imageTag = false;
var theSelection = false;
var bbcodeEnabled = true;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf('msie') != -1) && (clientPC.indexOf('opera') == -1));
var is_win = ((clientPC.indexOf('win') != -1) || (clientPC.indexOf('16bit') != -1));

function addquote(post_id, post_time, username, l_wrote)
{
	var message_name = 'message_fq' + post_id;
	var theSelection = '';
	var divarea = false;

	if (l_wrote === undefined)
	{
		// Backwards compatibility
		l_wrote = 'wrote';
	}

	if (document.all)
	{
		divarea = document.all[message_name];
	}
	else
	{
		divarea = document.getElementById(message_name);
	}

	// Get text selection - not only the post content :(
	if (window.getSelection)
	{
		theSelection = window.getSelection().toString();
	}
	else if (document.getSelection)
	{
		theSelection = document.getSelection();
	}
	else if (document.selection)
	{
		theSelection = document.selection.createRange().text;
	}

	if (theSelection == '' || typeof theSelection == 'undefined' || theSelection == null)
	{
		if (divarea.innerHTML)
		{
			theSelection = divarea.innerHTML.replace(/<br>/ig, '\n');
			theSelection = theSelection.replace(/<br\/>/ig, '\n');
			theSelection = theSelection.replace(/&lt\;/ig, '<');
			theSelection = theSelection.replace(/&gt\;/ig, '>');
			theSelection = theSelection.replace(/&amp\;/ig, '&');
			theSelection = theSelection.replace(/&nbsp\;/ig, ' ');
		}
		else if (document.all)
		{
			theSelection = divarea.innerText;
		}
		else if (divarea.textContent)
		{
			theSelection = divarea.textContent;
		}
		else if (divarea.firstChild.nodeValue)
		{
			theSelection = divarea.firstChild.nodeValue;
		}
	}

	if (theSelection)
	{
		if (bbcodeEnabled)
		{
			insert_text('[quote="' + username + '" pid="' + post_id + '" dateline="' + post_time + '"]' + theSelection + '[/quote]\n');
		}
		else
		{
			insert_text(username + ' ' + l_wrote + ':' + '\n');
			var lines = split_lines(theSelection);
			for (i = 0; i < lines.length; i++)
			{
				insert_text('> ' + lines[i] + '\n');
			}
		}
	}

	return;
}


/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close)
{
	var selLength = (typeof(txtarea.textLength) == 'undefined') ? txtarea.value.length : txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	if (selEnd == 1 || selEnd == 2) 
	{
		selEnd = selLength;
	}

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selStart + open.length;
	txtarea.selectionEnd = selEnd + open.length;
    setTimeout(function() { txtarea.focus(); }, 10);
	txtarea.scrollTop = scrollTop;

	return;
}