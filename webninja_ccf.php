<?php
/*
Plugin Name: Web Ninja Comment Count Fixer
Plugin URI: http://josh-fowler.com/?page_id=70
Description: Checks the number of comments a post has and compares them to the comment count and fixes the count if the count is wrong. Can be manually checked by the click of a button or set on a timer to do it automatically.
Version: 1.0.1
Author: Josh Fowler
Author URI: http://josh-fowler.com
*/

/*  Copyright 2010  Josh Fowler (http://josh-fowler.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('wbccfversion', '1.0.1', true);

$wbccf_options = get_option('webninja_ccf_options'); 

function wbccf_set_option($option_name, $option_value) {
	$wbccf_options = get_option('webninja_ccf_options');
	$wbccf_options[$option_name] = $option_value;
	update_option('webninja_ccf_options', $wbccf_options);
}

function wbccf_get_option($option_name) {
	$wbccf_options = get_option('webninja_ccf_options'); 
	if (!$wbccf_options || !array_key_exists($option_name, $wbccf_options)) {
		$wbccf_default_options=array();
		$wbccf_default_options['last_check']          = '';  
		$wbccf_default_options['enable_timer']        = false;  
		$wbccf_default_options['timer_interval']      = '1';  
		add_option('webninja_ccf_options', $wbccf_default_options, 'Settings for Web Ninja Comment Count Fix plugin');
		$result = $wbccf_default_options[$option_name];
	} else {
		$result = $wbccf_options[$option_name];
	}
	return $result;
}


function wbccf_admin() {
  if (function_exists('add_options_page')) {
    add_options_page('Web Ninja Comment Count Fixer', 
                     'Web Ninja CCF', 
                     8, 
                     basename(__FILE__), 
                     'wbccf_options');
  }
}

function wbccf_options() {
  global $wpdb;
  if (isset($_POST['info_update'])) {
    ?><div class="updated"><p><strong><?php 
    $wbccf_options = get_option('webninja_ccf_options');
    $wbccf_options['enable_timer']       = $_POST['enable_timer'];
    $wbccf_options['timer_interval']     = $_POST['timer_interval'];
    update_option('webninja_ccf_options', $wbccf_options);
    _e('Options saved', 'wbccf')
    ?></strong></p></div><?php
	} 
	?>
    <?php if (isset($_POST['check_now'])) {
	$querystr = "SELECT wpp.id, wpp.post_title, wpp.comment_count, wpc.cnt
FROM $wpdb->posts wpp
LEFT JOIN
(SELECT comment_post_id AS c_post_id, count(*) AS cnt FROM $wpdb->comments
 WHERE comment_approved = 1 GROUP BY comment_post_id) wpc
ON wpp.id=wpc.c_post_id
WHERE wpp.post_type IN ('post', 'page')
AND (wpp.comment_count!=wpc.cnt OR (wpp.comment_count != 0 AND wpc.cnt IS NULL));";
 $comment_check_results = $wpdb->get_results($querystr);
 if ($comment_check_results) {
	 
	 $fix_querystr = "UPDATE $wpdb->posts wpp
LEFT JOIN
(SELECT comment_post_id AS c_post_id, count(*) AS cnt FROM $wpdb->comments
 WHERE comment_approved = 1 GROUP BY comment_post_id) wpc
ON wpp.id=wpc.c_post_id
SET wpp.comment_count=wpc.cnt
WHERE wpp.post_type IN ('post', 'page')
      AND (wpp.comment_count!=wpc.cnt OR (wpp.comment_count != 0 AND wpc.cnt IS NULL));";
 	$comment_fix_results = $wpdb->get_results($fix_querystr);
	wbccf_set_option('last_check', date('Y-m-d H:i:s'));
	  _e('<div class="updated"><p><strong>Check Done: '.count($comment_check_results).' comment count(s) fixed.</strong></p></div>', 'wbccf');
 } else {
	 _e('<div class="updated"><p><strong>Check Done: All comments counts are correct.</strong></p></div>', 'wbccf');
	 wbccf_set_option('last_check', date('Y-m-d H:i:s'));
 }
} ?>

<div class=wrap style="width:820px">
    <h2>Web Ninja Comment Count Fixer</h2>
    <?php if (wbccf_get_option('check_updates')) { echo "<br /><strong>Update Check</strong>: "; wbccf_check_updates(true); } ?>
<div style="float:right; width:390px; border:1px #DEDEDD dashed; background-color:#FEFAE7; padding:10px 10px 10px 10px">
<b>Homepage:</b> <a href="http://josh-fowler.com/?page_id=124" target="_blank">Web Ninja Comment Count Fixer</a><br />
<Br />
<b>Support:</b> <a href="http://josh-fowler.com/forum/" target="_blank">Web Ninja Forums</a><br />
<br />
<b>Developed by:</b> <a href="http://josh-fowler.com/" target="_blank">Josh Fowler</a><br />
<br />
<b>Like the plugin? Then "Like" The Web Ninja!</b>
<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FThe-Web-Ninja%2F160118787364131&amp;layout=standard&amp;show_faces=false&amp;width=375&amp;action=like&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:375px; height:35px;" allowTransparency="true"></iframe>
<br />
<b>Donate:</b> I spend a lot of time on the plugins I've written for WordPress. Donations are not required but any donation would be highly appreciated. Just enter the donation amount and click the "Donate Now" button below.<br />
<br />
<center><form class="gcheckout" method="POST" action="https://checkout.google.com/cws/v2/Merchant/462781349183533/checkoutForm" accept-charset="utf-8"> <input name="item_name_1" value="Web Ninja Comment Count Fixer" type="hidden"> <input name="item_description_1" value="Thanks! Every little bit helps!" type="hidden"> <input name="item_quantity_1" value="1" id="qty" type="hidden"> <label><b>Amount:</b> $</label><input name="item_price_1" value="" id="amt" type="text" size="10"> <input name="charset" type="hidden"> <br /><input id="submit" name="Google Checkout" alt="Fast checkout through Google" src="http://josh-fowler.com/images/donateNow.png" type="image"> </form></center><br />
</div>
<div style="float:left; width:400px">
  <form method="post">
  <script language="javascript" type="text/javascript">
  var tooltip=function(){
 var id = 'tt';
 var top = 3;
 var left = 3;
 var maxw = 500;
 var speed = 10;
 var timer = 20;
 var endalpha = 95;
 var alpha = 0;
 var tt,t,c,b,h;
 var ie = document.all ? true : false;
 return{
  show:function(v,w){
   if(tt == null){
    tt = document.createElement('div');
    tt.setAttribute('id',id);
    t = document.createElement('div');
    t.setAttribute('id',id + 'top');
    c = document.createElement('div');
    c.setAttribute('id',id + 'cont');
    b = document.createElement('div');
    b.setAttribute('id',id + 'bot');
    tt.appendChild(t);
    tt.appendChild(c);
    tt.appendChild(b);
    document.body.appendChild(tt);
    tt.style.opacity = 0;
    tt.style.filter = 'alpha(opacity=0)';
    document.onmousemove = this.pos;
   }
   tt.style.display = 'block';
   c.innerHTML = v;
   tt.style.width = w ? w + 'px' : 'auto';
   if(!w && ie){
    t.style.display = 'none';
    b.style.display = 'none';
    tt.style.width = tt.offsetWidth;
    t.style.display = 'block';
    b.style.display = 'block';
   }
  if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
  h = parseInt(tt.offsetHeight) + top;
  clearInterval(tt.timer);
  tt.timer = setInterval(function(){tooltip.fade(1)},timer);
  },
  pos:function(e){
   var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
   var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
   tt.style.top = (u - h) + 'px';
   tt.style.left = (l + left) + 'px';
  },
  fade:function(d){
   var a = alpha;
   if((a != endalpha && d == 1) || (a != 0 && d == -1)){
    var i = speed;
   if(endalpha - a < speed && d == 1){
    i = endalpha - a;
   }else if(alpha < speed && d == -1){
     i = a;
   }
   alpha = a + (i * d);
   tt.style.opacity = alpha * .01;
   tt.style.filter = 'alpha(opacity=' + alpha + ')';
  }else{
    clearInterval(tt.timer);
     if(d == -1){tt.style.display = 'none'}
  }
 },
 hide:function(){
  clearInterval(tt.timer);
   tt.timer = setInterval(function(){tooltip.fade(-1)},timer);
  }
 };
}();
</script>
<style type="text/css">
#tt {
 position:absolute;
 display:block;
 }
 #tttop {
 display:block;
 height:5px;
 margin-left:5px;
 overflow:hidden;
 }
 #ttcont {
 display:block;
 padding:2px 12px 3px 7px;
 margin-left:5px;
 background:#666;
 color:#fff;
 }
#ttbot {
display:block;
height:5px;
margin-left:5px;
overflow:hidden;
}
th {
	text-align:right;
}
</style>
<br>
    <input type="submit" name="check_now" class="button-primary" value="<?php _e('Check &amp; Fix Comment Counts Now', 'wbccf') ?>" /><Br><br>
    <b>Last Checked: </b><?php echo (wbccf_get_option('last_check') != '') ? wbccf_get_option('last_check') : 'Never checked'; ?><br />
    <br />
    <fieldset class="options" name="general">
      <h3>Option Settings</h3>
      <table width="300px" cellspacing="5" cellpadding="5" class="editform">
        <tr>
          <th nowrap valign="top" width="200px">Timer Enabled: <span onmouseover="tooltip.show('By checking this it will enable the automatic check and fix of wrong comment counts.', 400);" onmouseout="tooltip.hide();" style="color:#00F; cursor:pointer">[?]</span></th>
          <td><input type="checkbox" name="enable_timer" id="enable_timer" value="true" <?php if (wbccf_get_option('enable_timer')) echo "checked"; ?> /></td>
		</tr>
        <tr>
        <tr>
          <th nowrap valign="top">Timer Interval: <span onmouseover="tooltip.show('This is the interval in which the automatic timer works.', 400);" onmouseout="tooltip.hide();" style="color:#00F; cursor:pointer">[?]</span></th>
          <td><?php $ccf_timer_interval = wbccf_get_option('timer_interval'); ?>
          <select name="timer_interval" id="timer_interval">
              <option value="1" <?php echo ($ccf_timer_interval == '1') ? 'selected' : ''; echo ($ccf_timer_interval == '') ? 'selected' : ''; ?>>Every 1 hour</option>
              <option value="6" <?php echo ($ccf_timer_interval == '6') ? 'selected' : ''; ?>>Every 6 hours</option>
              <option value="12" <?php echo ($ccf_timer_interval == '12') ? 'selected' : ''; ?>>Every 12 hours</option>
              <option value="14" <?php echo ($ccf_timer_interval == '24') ? 'selected' : ''; ?>>Every 24 hours</option>
            </select></td>
		</tr>
      </table> 
    </fieldset>
    <div class="submit"> 
      <input type="submit" name="info_update" class="button-primary" value="<?php _e('Save Options', 'wbccf') ?>" />
	  </div>
  </form>
</div>
</div><?php
}

function wbccf_date_diff($d1){
  $d2 = date("Y-m-d H:i:s");
  if ($d1 < $d2){
    $temp = $d2;
    $d2 = $d1;
    $d1 = $temp;
  }
  else {
    $temp = $d1; 
  }
  $d1 = date_parse($d1);
  $d2 = date_parse($d2);
  if ($d1['second'] >= $d2['second']){
    $diff['second'] = $d1['second'] - $d2['second'];
  }
  else {
    $d1['minute']--;
    $diff['second'] = 60-$d2['second']+$d1['second'];
  }
  if ($d1['minute'] >= $d2['minute']){
    $diff['minute'] = $d1['minute'] - $d2['minute'];
  }
  else {
    $d1['hour']--;
    $diff['minute'] = 60-$d2['minute']+$d1['minute'];
  }
  if ($d1['hour'] >= $d2['hour']){
    $diff['hour'] = $d1['hour'] - $d2['hour'];
  }
  else {
    $d1['day']--;
    $diff['hour'] = 24-$d2['hour']+$d1['hour'];
  }
  if ($d1['day'] >= $d2['day']){
    $diff['day'] = $d1['day'] - $d2['day'];
  }
  else {
    $d1['month']--;
    $diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
  }
  if ($d1['month'] >= $d2['month']){
    $diff['month'] = $d1['month'] - $d2['month'];
  }
  else {
    $d1['year']--;
    $diff['month'] = 12-$d2['month']+$d1['month'];
  }
  $diff['year'] = $d1['year'] - $d2['year'];
  return $diff;   
}

function wbccf_init() {
  load_plugin_textdomain('wbccf');
}

function wbccf_shutdown() {
  global $wpdb;
  if (wbccf_get_option('enable_timer')) {
	  $diff = wbccf_date_diff(wbccf_get_option('last_check'));
	  if (wbccf_get_option('timer_interval') <= $diff['minute']) {
		$querystr = "SELECT wpp.id, wpp.post_title, wpp.comment_count, wpc.cnt
FROM $wpdb->posts wpp
LEFT JOIN
(SELECT comment_post_id AS c_post_id, count(*) AS cnt FROM $wpdb->comments
 WHERE comment_approved = 1 GROUP BY comment_post_id) wpc
ON wpp.id=wpc.c_post_id
WHERE wpp.post_type IN ('post', 'page')
AND (wpp.comment_count!=wpc.cnt OR (wpp.comment_count != 0 AND wpc.cnt IS NULL));";
		$comment_check_results = $wpdb->get_results($querystr);
		if ($comment_check_results) {
			$fix_querystr = "UPDATE $wpdb->posts wpp
LEFT JOIN
(SELECT comment_post_id AS c_post_id, count(*) AS cnt FROM $wpdb->comments
 WHERE comment_approved = 1 GROUP BY comment_post_id) wpc
ON wpp.id=wpc.c_post_id
SET wpp.comment_count=wpc.cnt
WHERE wpp.post_type IN ('post', 'page')
      AND (wpp.comment_count!=wpc.cnt OR (wpp.comment_count != 0 AND wpc.cnt IS NULL));";
			$comment_fix_results = $wpdb->get_results($fix_querystr);
			wbccf_set_option('last_check', date('Y-m-d H:i:s'));
		} else {
			wbccf_set_option('last_check', date('Y-m-d H:i:s'));
		}
	  }
  }
}
if (wbccf_get_option('check_updates') && wbccf_get_option('version_sent')!=wbccf_version) {
  wbccf_set_option('version_sent', wbccf_version);
  wbccf_check_updates(false);
}

add_action('admin_menu', 'wbccf_admin');
add_action('init', 'wbccf_init');
add_action('shutdown', 'wbccf_shutdown');

?>