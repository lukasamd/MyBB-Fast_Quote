<?php
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
 
/**
 * Disallow direct access to this file for security reasons
 * 
 */
if (!defined('IN_MYBB')) {
    exit;
}

/**
 * Plugin Activator Class
 * 
 */
class fastQuoteActivator
{

    private static $tpl = array();

    private static function getTpl() {
        global $db;

        self::$tpl = array();
        
        self::$tpl[] = array(
            "title" => 'fastQuote_button',
            "template" => $db->escape_string('
<a href="#message" title="{$fastquote_data[\'title\']}" class="postbit_fastquote" data-fastquote-pid="{$fastquote_data[\'pid\']}" data-fastquote-dateline="{$fastquote_data[\'dateline\']}" data-fastquote-username="{$fastquote_data[\'username\']}" data-fastquote-message-id="message_fq{$fastquote_data[\'pid\']}"><span class="postbit_fastquote_span" style="{$fastquote_data[\'style\']}">*</span></a>
<div style="display:none;" id="message_fq{$fastquote_data[\'pid\']}">{$fastquote_data[\'message\']}</div>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );  
    }

    public static function activate() {
        global $db;
        self::deactivate();

        for ($i = 0; $i < count(self::$tpl); $i++) {
            $db->insert_query('templates', self::$tpl[$i]);
        }

        require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

        $showthreadChange = "<!-- start: FastQuote -->\n";
        $showthreadChange .= "<script type=\"text/javascript\">\n";
        $showthreadChange .= "window.FastQuoteConfig = {\$fastquote_config};\n";
        $showthreadChange .= "</script>\n";
        $showthreadChange .= "<script type=\"text/javascript\" src=\"jscripts/fastQuote.js\"></script>\n";
        $showthreadChange .= "<!-- end: FastQuote -->\n";
        $showthreadChange .= "</head>";

        find_replace_templatesets('showthread', '#<\/head>#', $showthreadChange);
        find_replace_templatesets('postbit', '#{\$post\[\'button_quote\'\]}#', '{$post[\'button_quote_fast\']}{$post[\'button_quote\']}');
        find_replace_templatesets('postbit_classic', '#{\$post\[\'button_quote\'\]}#', '{$post[\'button_quote_fast\']}{$post[\'button_quote\']}');
    }

    public static function deactivate() {
        global $db;
        self::getTpl();

        for ($i = 0; $i < count(self::$tpl); $i++)
        {
            $db->delete_query('templates', "title = '" . self::$tpl[$i]['title'] . "'");
        }

        require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
        find_replace_templatesets('showthread', "#<\!-- start: FastQuote -->.*<\!-- end: FastQuote -->\n#siU", '', 0);
        find_replace_templatesets('postbit', '#{\$post\[\'button_quote_fast\'\]}#', '', 0);
        find_replace_templatesets('postbit_classic', '#{\$post\[\'button_quote_fast\'\]}#', '', 0);
    }

}
