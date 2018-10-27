<?php
/*
 * MyBB: Who Read Thread
 *
 * File: whoreadthread.php
 * 
 * Authors: Mirko T. & Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.2
 * 
 *
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Add hook

$plugins->add_hook("showthread_start", "whoreadthread_run");

// Plugin Info

function whoreadthread_info() {

   global $lang;

    $lang->load("whoreadthread");
    
    $lang->whoreadthread_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->whoreadthread_Desc;

    return Array(
        'name' => $lang->whoreadthread_Name,
        'description' => $lang->whoreadthread_Desc,
        'website' => $lang->whoreadthread_Web,
        'author' => $lang->whoreadthread_Auth,
        'authorsite' => $lang->whoreadthread_AuthSite,
        'version' => $lang->whoreadthread_Ver,
        'compatibility' => $lang->whoreadthread_Compat
    );
}

// Plugin Activate

function whoreadthread_activate() {

    global $db, $mybb, $lang;

    $lang->load("whoreadthread");
	
    // Adding New Template

    $wvt_template = array(
        "title"		=> 'who_visit_thread',
	"template"	=> $db->escape_string('
<br/>
<table class="tborder" cellspacing="1" cellpadding="4" border="0">
<tbody>
<tr>
<td class="thead"><strong>{$lang->whoreadthread_show_user_who_read}</strong></div></td>
</tr>
<tr>
<td class="tcat"><span class="smalltext"><em><strong>{$totalvisitors} {$lang->whoreadthread_show_user_read}</strong></em></span></td>
</tr>
<tr>
<td class="trow1">{$threadvisitorslist}
</td>
</tr>
</tbody>
</table>'),
        "sid"	        => "-1",
        "version"	=> 1800,
        "dateline"	=> TIME_NOW
    );

    $db->insert_query('templates', $wvt_template);

    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

    find_replace_templatesets("showthread", "#".preg_quote('{$similarthreads}')."#i",'{$similarthreads}{$who_visit_thread}');

    // Plugin Settings Group

    $gid = $db->insert_id();

    $whoreadthread_group = array(
        'gid'            => '0',
        'name'           => 'whoreadthread',
        'title' => ''.$lang->whoreadthread_option_0_Title.'', 
        'description' => ''.$lang->whoreadthread_option_0_Description.'', 
        'disporder'      => '1',
        'isdefault'      => '0',
    );
    $db->insert_query("settinggroups", $whoreadthread_group);

    $gid = $db->insert_id();

    // Plugin Settings 
    
    $whoreadthread_setting_1 = array(
        'sid'            => '0',
        'name'           => 'whoreadthread_1',
        'title' => ''.$lang->whoreadthread_option_1_Title.'', 
        'description' => ''.$lang->whoreadthread_option_1_Description.'', 
        'optionscode'   => 'yesno',
        'value'          => '0',
        'disporder'      => '1',
        'gid'            => intval($gid),
    );

    $whoreadthread_setting_2 = array(
        'sid'            => '0',
        'name'           => 'whoreadthread_2',
        'title' => ''.$lang->whoreadthread_option_2_Title.'', 
        'description' => ''.$lang->whoreadthread_option_2_Description.'', 
        'optionscode'    => 'select\n
uid=ID
username=Username
dateline=Read Time',
        'value'          => '1',
        'disporder'      => '2',
        'gid'            => intval($gid),
    );

    $whoreadthread_setting_3 = array(
        'sid'            => '0',
        'name'           => 'whoreadthread_3',
        'title' => ''.$lang->whoreadthread_option_3_Title.'', 
        'description' => ''.$lang->whoreadthread_option_3_Description.'', 
        'optionscode'    => 'select\n
ASC=Ascending
DESC=Descending',
        'value'          => '1',
        'disporder'      => '3',
        'gid'            => intval($gid),
    );

    $whoreadthread_setting_4 = array(
        'sid'            => '0',
        'name'           => 'whoreadthread_4',
        'title' => ''.$lang->whoreadthread_option_4_Title.'', 
        'description' => ''.$lang->whoreadthread_option_4_Description.'', 
        'optionscode'    => 'text',
        'value'          => '100',
        'disporder'      => '4',
        'gid'            => intval($gid),
    );

    $whoreadthread_setting_5 = array(
        'sid'            => '0',
        'name'           => 'whoreadthread_5',
        'title' => ''.$lang->whoreadthread_option_5_Title.'', 
        'description' => ''.$lang->whoreadthread_option_5_Description.'', 
        'optionscode'    => 'yesno',
        'value'          => '1',
        'disporder'      => '5',
        'gid'            => intval($gid),
    );

    $db->insert_query("settings", $whoreadthread_setting_1);
    $db->insert_query("settings", $whoreadthread_setting_2);
    $db->insert_query("settings", $whoreadthread_setting_3);
    $db->insert_query("settings", $whoreadthread_setting_4);
    $db->insert_query("settings", $whoreadthread_setting_5);

    // Rebuilding settings

    rebuild_settings();
}

// Plugin Deactivate

function whoreadthread_deactivate() {

    global $db, $mybb;

    $db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title = 'who_visit_thread'");

    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

    find_replace_templatesets("showthread", "#".preg_quote('{$who_visit_thread}')."#i", '', 0);

    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='whoreadthread'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='whoreadthread_1'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='whoreadthread_2'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='whoreadthread_3'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='whoreadthread_4'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='whoreadthread_5'");

    // Rebuilding settings

    rebuild_settings();
}

// Run plugin

function whoreadthread_run() {

    global $db, $mybb, $lang, $templates, $who_visit_thread, $tid;

    $lang->load("whoreadthread");

    if($mybb->settings['whoreadthread_1'] == 1) {

      $field = $mybb->settings['whoreadthread_2'];
      $order = $mybb->settings['whoreadthread_3'];
      
      if($mybb->settings['whoreadthread_4'] < 1) { $limit = "1"; } else { $limit = $mybb->settings['whoreadthread_4']; }

      $query = $db->query("SELECT r.*, u.username, u.usergroup, u.displaygroup FROM ".TABLE_PREFIX."threadsread r LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid = r.uid) WHERE r.tid = '{$tid}' AND u.uid != '0' ORDER BY {$field} {$order} LIMIT {$limit}");

      $totalvisitors = $db->num_rows($query);

          if($totalvisitors > 0) {

           $comma = '';

            while($row = $db->fetch_array($query)) {

            $row['username'] = $db->escape_string(htmlspecialchars($row['username']));
            $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $profilelink = build_profile_link($username, $row['uid']);


              if($mybb->settings['whoreadthread_5'] == 1) {
                $readdate = my_date($mybb->settings['dateformat'], $row['dateline']);
                $readtime = my_date($mybb->settings['timeformat'], $row['dateline']);
                $threadvisitorslist .= "{$comma}{$profilelink} <span class='smalltext'>({$readdate}, {$readtime})</span>";
              } else {
                $threadvisitorslist .= "{$comma}{$profilelink}";
              }
             $comma = $lang->comma;
            }
          
    eval("\$who_visit_thread = \"".$templates->get("who_visit_thread")."\";");

         }
    }

}

?>