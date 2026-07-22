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
 */

(function ($, window, document) {
    'use strict';

    var FastQuote = {
        config: {
            formName: 'quick_reply_form',
            fieldName: 'message'
        },

        init: function () {
            FastQuote.config = $.extend({}, FastQuote.config, window.FastQuoteConfig || {});

            $(document).on('click', '.postbit_fastquote', FastQuote.handleClick);
        },

        handleClick: function (event) {
            var button = event.currentTarget;
            var pid = parseInt(button.getAttribute('data-fastquote-pid'), 10) || 0;
            var dateline = parseInt(button.getAttribute('data-fastquote-dateline'), 10) || 0;
            var username = button.getAttribute('data-fastquote-username') || '';
            var message = FastQuote.getSelectedText() || FastQuote.getStoredMessage(button);

            event.preventDefault();

            if (!message) {
                return false;
            }

            FastQuote.insert('[quote="' + FastQuote.escapeQuoteAttribute(username) + '" pid="' + pid + '" dateline="' + dateline + '"]' + message + '[/quote]\n');

            return false;
        },

        getSelectedText: function () {
            var selection = '';

            if (window.getSelection) {
                selection = window.getSelection().toString();
            } else if (document.getSelection) {
                selection = document.getSelection().toString();
            }

            return $.trim(selection);
        },

        getStoredMessage: function (button) {
            var messageId = button.getAttribute('data-fastquote-message-id');
            var messageElement = messageId ? document.getElementById(messageId) : null;

            if (!messageElement) {
                return '';
            }

            return messageElement.textContent || messageElement.innerText || '';
        },

        insert: function (quote) {
            var textarea = FastQuote.getTextarea();

            if (typeof window.MyBBEditor !== 'undefined' && window.MyBBEditor !== null && typeof window.MyBBEditor.insert === 'function') {
                window.MyBBEditor.insert(quote);
                return;
            }

            if (!textarea) {
                return;
            }

            if (typeof textarea.selectionStart === 'number' && typeof textarea.selectionEnd === 'number') {
                FastQuote.insertAtSelection(textarea, quote);
            } else {
                textarea.value += quote;
            }

            textarea.focus();
        },

        getTextarea: function () {
            var form = document.forms[FastQuote.config.formName];

            if (form && form.elements[FastQuote.config.fieldName]) {
                return form.elements[FastQuote.config.fieldName];
            }

            if (FastQuote.config.fieldName) {
                return document.getElementsByName(FastQuote.config.fieldName)[0] || document.getElementById(FastQuote.config.fieldName);
            }

            return document.getElementById('message');
        },

        insertAtSelection: function (textarea, quote) {
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var scrollTop = textarea.scrollTop;

            textarea.value = textarea.value.substring(0, start) + quote + textarea.value.substring(end);
            textarea.selectionStart = start + quote.length;
            textarea.selectionEnd = start + quote.length;
            textarea.scrollTop = scrollTop;
        },

        escapeQuoteAttribute: function (username) {
            return String(username)
                .replace(/[\r\n]+/g, ' ')
                .replace(/"/g, '&quot;');
        }
    };

    window.FastQuote = FastQuote;
    $(FastQuote.init);
}(jQuery, window, document));
