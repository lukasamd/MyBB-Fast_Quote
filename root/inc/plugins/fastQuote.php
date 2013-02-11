<?php
/**
 * This file is part of Fast Quote plugin for MyBB.
 * Copyright (C) 2010-2013 Lukasz Tkacz <lukasamd@gmail.com>
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
 * Create plugin object
 * 
 */
$plugins->objects['fastQuote'] = new fastQuote();

/**
 * Standard MyBB info function
 * 
 */
function fastQuote_info()
{
    global $lang;

    $lang->load('fastQuote');

    $lang->fastQuoteDesc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="3BTVZBUG6TMFQ">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->fastQuoteDesc;

    return Array(
        'name' => $lang->fastQuoteName,
        'description' => $lang->fastQuoteDesc,
        'website' => 'http://lukasztkacz.com',
        'author' => 'Lukasz "LukasAMD" Tkacz',
        'authorsite' => 'http://lukasztkacz.com',
        'version' => '1.5',
        'guid' => '233a28d0a679315aa30cf168f84c3485',
        'compatibility' => '16*'
    );
}

/**
 * Standard MyBB installation functions 
 * 
 */
function fastQuote_install()
{
    require_once('fastQuote.settings.php');
    fastQuoteInstaller::install();

    rebuildsettings();
}

function fastQuote_is_installed()
{
    global $mybb;

    return (isset($mybb->settings['fastQuoteStatus']));
}

function fastQuote_uninstall()
{
    require_once('fastQuote.settings.php');
    fastQuoteInstaller::uninstall();

    rebuildsettings();
}

/**
 * Standard MyBB activation functions 
 * 
 */
function fastQuote_activate()
{
    require_once('fastQuote.tpl.php');
    fastQuoteActivator::activate();
}

function fastQuote_deactivate()
{
    require_once('fastQuote.tpl.php');
    fastQuoteActivator::deactivate();
}

/**
 * Plugin Class 
 * 
 */
class fastQuote
{
    private $posts;
    private $quick_reply_status = false;

    /**
     * Constructor - add plugin hooks
     */
    public function __construct()
    {
        global $plugins;

        // Add all hooks
        $plugins->hooks["parse_message_start"][10]["fastQuote_injectParser"] = array("function" => create_function('&$arg', 'global $plugins; $plugins->objects[\'fastQuote\']->injectParser($arg);'));
        $plugins->hooks["postbit"][10]["fastQuote_addButton"] = array("function" => create_function('&$arg', 'global $plugins; $plugins->objects[\'fastQuote\']->addButton($arg);'));
        $plugins->hooks["showthread_start"][10]["fastQuote_checkQuickReplyStatus"] = array("function" => create_function('', 'global $plugins; $plugins->objects[\'fastQuote\']->checkQuickReplyStatus();'));
    }
    
    /**
     * Collect post data if full quote option is enabled
     *
     */
    public function injectParser($message)
    {
        global $mybb, $post;
        
        if (THIS_SCRIPT == 'showthread.php' 
            && $mybb->user['uid'] > 0 
            && $mybb->settings['fastQuoteStatus'])
        {
            $this->posts[$post['pid']] = htmlspecialchars($post['message']);
        }
            
        return $message;
    }
    
    /**
     * Check is quick reply enabled
     *
     */
    public function checkQuickReplyStatus()
    {
        global $fid, $forum, $forumpermissions, $mybb, $thread;
        
        if ($forumpermissions['canpostreplys'] != 0 
            && $mybb->user['suspendposting'] != 1 
            && ($thread['closed'] != 1 || is_moderator($fid)) 
            && $mybb->settings['quickreply'] != 0 
            && $mybb->user['showquickreply'] != '0' 
            && $forum['open'] != 0)
        {
            $this->quick_reply_status = true;
        }
	
    }
    
    /**
     * Add fast quote button to post data
     *
     */
    public function addButton(&$post)
    {
        global $lang, $mybb;

        $post['button_quote_fast'] = '';

        if (!$this->quick_reply_status)
        {
            return;
        }
        else
        {
            $post['button_quote_fast'] .= '<a href="#message" onclick="addquote(\'fq' . $post['pid'] . "','" . $post['username'] . "'";
            $post['button_quote_fast'] .= ');" title="' . $mybb->settings['fastQuoteText'] . '">';
            $post['button_quote_fast'] .= '<img src="' . $mybb->settings['fastQuoteImagePath'] . '" alt="' . $mybb->settings['fastQuoteText'] . '" /></a>';
            $post['button_quote_fast'] .= '<div style="display:none;" id="message_fq' . $post['pid'] . '">' . $this->posts[$post['pid']] . '</div>';
        }
    }

}
