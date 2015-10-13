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
if (!defined("IN_MYBB")) exit;

/**
 * Plugin Installator Class
 * 
 */
class fastQuoteInstaller
{

    public static function install() {
        global $db, $lang, $mybb;
        self::uninstall();

        $result = $db->simple_select('settinggroups', 'MAX(disporder) AS max_disporder');
        $max_disporder = $db->fetch_field($result, 'max_disporder');
        $disporder = 1;

        $settings_group = array(
            'gid' => 'NULL',
            'name' => 'fastQuote',
            'title' => $db->escape_string($lang->fastQuoteName),
            'description' => $db->escape_string($lang->fastQuoteGroupDesc),
            'disporder' => $max_disporder + 1,
            'isdefault' => '0'
        );
        $db->insert_query('settinggroups', $settings_group);
        $gid = (int) $db->insert_id();

        $setting = array(
            'sid' => 'NULL',
            'name' => 'fastQuoteStatus',
            'title' => $db->escape_string($lang->fastQuoteStatus),
            'description' => $db->escape_string($lang->fastQuoteStatusDesc),
            'optionscode' => 'onoff',
            'value' => '1',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
            'sid' => 'NULL',
            'name' => 'fastQuoteImageStyle',
            'title' => $db->escape_string($lang->fastQuoteImageStyle),
            'description' => $db->escape_string($lang->fastQuoteImageStyleDesc),
            'optionscode' => 'textarea',
            'value' => $db->escape_string("background-position: 0 -100px;
padding-left:16px;
text-indent: -9999px;"),
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
            'sid' => 'NULL',
            'name' => 'fastQuoteText',
            'title' => $db->escape_string($lang->fastQuoteText),
            'description' => $db->escape_string($lang->fastQuoteTextDesc),
            'optionscode' => 'text',
            'value' => $lang->fastQuoteTextDef,
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
            'sid' => 'NULL',
            'name' => 'fastQuoteFormName',
            'title' => $db->escape_string($lang->fastQuoteFormName),
            'description' => $db->escape_string($lang->fastQuoteFormNameDesc),
            'optionscode' => 'text',
            'value' => 'quick_reply_form',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
            'sid' => 'NULL',
            'name' => 'fastQuoteFieldName',
            'title' => $db->escape_string($lang->fastQuoteFieldName),
            'description' => $db->escape_string($lang->fastQuoteFieldNameDesc),
            'optionscode' => 'text',
            'value' => 'message',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);
    }

    public static function uninstall() {
        global $db;

        $result = $db->simple_select('settinggroups', 'gid', "name = 'fastQuote'");
        $gid = (int) $db->fetch_field($result, "gid");
        
        if ($gid > 0) {
            $db->delete_query('settings', "gid = '{$gid}'");
        }
        $db->delete_query('settinggroups', "gid = '{$gid}'");
    }

}
