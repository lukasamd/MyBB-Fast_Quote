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
 * Add hooks
 * 
 */
$plugins->add_hook('parse_message_start', array('fastQuote', 'injectParser'));
$plugins->add_hook('postbit', array('fastQuote', 'addButton'));
$plugins->add_hook('showthread_start', array('fastQuote', 'checkQuickReplyStatus'));


/**
 * Standard MyBB info function
 * 
 */
function fastQuote_info() {
    global $lang;

    $lang->load('fastQuote');

    return array(
        'name' => $lang->fastQuoteName,
        'description' => $lang->fastQuoteDesc,
        'website' => 'https://tkacz.pro',
        'author' => 'Lukasz Tkacz',
        'authorsite' => 'https://tkacz.pro',
        'version' => '2.0.0',
        'compatibility' => '18*',
        'codename' => 'fast_quote'
    );
}

/**
 * Standard MyBB installation functions 
 * 
 */
function fastQuote_install() {
    require_once dirname(__FILE__).'/fastQuote.settings.php';
    fastQuoteInstaller::install();

    rebuild_settings();
}

function fastQuote_is_installed() {
    global $mybb;

    return (isset($mybb->settings['fastQuoteStatus']));
}

function fastQuote_uninstall() {
    require_once dirname(__FILE__).'/fastQuote.settings.php';
    fastQuoteInstaller::uninstall();

    rebuild_settings();
}

/**
 * Standard MyBB activation functions 
 * 
 */
function fastQuote_activate() {
    require_once dirname(__FILE__).'/fastQuote.tpl.php';
    fastQuoteActivator::activate();
}

function fastQuote_deactivate() {
    require_once dirname(__FILE__).'/fastQuote.tpl.php';
    fastQuoteActivator::deactivate();
}

/**
 * Plugin Class 
 * 
 */
class fastQuote
{
    private static $posts = array();
    private static $quick_reply_status = false;

    /**
     * Collect post data if full quote option is enabled
	 *
     *
     */
    public static function injectParser($message) {
        global $mybb, $post;
        
        if (defined('THIS_SCRIPT')
            && THIS_SCRIPT == 'showthread.php'
            && !empty($post['pid'])
            && !empty($mybb->user['uid'])
            && self::isEnabled()
        ) {
            self::$posts[(int)$post['pid']] = htmlspecialchars_uni(isset($post['message']) ? $post['message'] : '');
        }
            
        return $message;
    }
    
    /**
     * Check is quick reply enabled
     *
     */
    public static function checkQuickReplyStatus(&$unused = null) {
        global $fastquote_config, $fid, $forum, $forumpermissions, $mybb, $thread;

        self::$quick_reply_status = false;
        $fastquote_config = self::buildJsConfig();

        if (!self::isEnabled()) {
            return;
        }

        if (!empty($forumpermissions['canpostreplys'])
            && empty($mybb->user['suspendposting'])
            && (empty($thread['closed']) || is_moderator((int)$fid, 'canpostclosedthreads'))
            && !empty($mybb->settings['quickreply'])
            && (!isset($mybb->user['showquickreply']) || $mybb->user['showquickreply'] != '0')
            && !empty($forum['open'])
            && (!isset($forumpermissions['canonlyreplyownthreads']) || empty($forumpermissions['canonlyreplyownthreads']) || (int)$thread['uid'] === (int)$mybb->user['uid'])
        ) {
            self::$quick_reply_status = true;
        }
    }
    
    /**
     * Add fast quote button to post data
     *
     */
    public static function addButton(&$post) {
        global $lang, $mybb, $templates;

        $post['button_quote_fast'] = '';
        if (!self::$quick_reply_status || !self::isEnabled()) {
            return;
        }

        $pid = isset($post['pid']) ? (int)$post['pid'] : 0;
        $fastquote_data = array(
            'pid' => $pid,
            'username' => self::escapeAttribute(isset($post['username']) ? $post['username'] : ''),
            'dateline' => isset($post['dateline']) ? (int)$post['dateline'] : 0,
            'title' => self::escapeAttribute(self::getSetting('fastQuoteText')),
            'style' => self::escapeAttribute(self::getSetting('fastQuoteImageStyle')),
            'message' => isset(self::$posts[$pid]) ? self::$posts[$pid] : '',
        );

        eval("\$post['button_quote_fast'] .= \"" . $templates->get('fastQuote_button') . "\";");
    }

    private static function isEnabled()
    {
        return (bool)self::getSetting('fastQuoteStatus');
    }

    private static function getSetting($name)
    {
        global $mybb;

        return isset($mybb->settings[$name]) ? $mybb->settings[$name] : '';
    }

    private static function escapeAttribute($value)
    {
        return htmlspecialchars_uni((string)$value);
    }

    private static function buildJsConfig()
    {
        $config = array(
            'formName' => self::getSetting('fastQuoteFormName') ?: 'quick_reply_form',
            'fieldName' => self::getSetting('fastQuoteFieldName') ?: 'message',
        );

        return json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

}
