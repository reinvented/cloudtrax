<?php
/**
  * download-graphs.php - A PHP script to automate the downloading of
  * network traffic graphs from Cloudtrax.com.
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.1, November 20, 2012
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2012, Prince Street Home and School
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * User-configurable settings
  */

// Username and password for cloudtrax.com
$username = "";
$password = "";

// The internal Cloudtrax.com name for your network.
$network  = "";

// The width, in pixels, you would like you graphs to be generated with.
$graph_width = 640;

// An array holding the label and number of hours for each graph you want to download
$graphs = array("month" => 744,"week" => 168,"day" => 24,"8hours" => 8,"lasthour" => 1);
						
// Directory into which graphs should be saved (include trailing slash)
$graph_dir = "/tmp/graphs/";

// URLs at CloudTrax.com -- you shouldn't really need to change these.
$cloudtrax_base = "https://cloudtrax.com/";
$loginurl = "dashboard.php";
$graphurl = "usage_graph_month2.php";

/**
  * Login to Cloudtrax.com, saving cookies to a temporary file.
  * This has the effect of saving a PHP session ID cookie, which is
  * later sent *back* to authenticate future requests.
  */
exec("curl -s -L -e $cloudtrax_base$loginurl -c /tmp/cloudtrax-cookies.txt -X POST -d 'account=" . urlencode($username) . "&password=" . urlencode($password) ."&edit=Login' $cloudtrax_base$loginurl");

/**
  * Download graphs.
  */
foreach($graphs as $label => $hours) {
	$ch = curl_init("$cloudtrax_base$graphurl?id=" . urlencode($network) . "&hours=$hours&width=$graph_width");
	curl_setopt($ch,CURLOPT_COOKIEFILE,"/tmp/cloudtrax-cookies.txt");
	curl_setopt($ch,CURLOPT_COOKIEJAR,"/tmp/cloudtrax-cookies.txt");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
	$html = curl_exec($ch);
	curl_close($ch); 
	preg_match("/src=\"(getchart\.php\?img=chart1&id=.*&)\"/", $html, $matches);
	$ch = curl_init("$cloudtrax_base" . $matches[1]);
	curl_setopt($ch,CURLOPT_COOKIEFILE,"/tmp/cloudtrax-cookies.txt");
	curl_setopt($ch,CURLOPT_COOKIEJAR,"/tmp/cloudtrax-cookies.txt");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
	$png = curl_exec($ch);
	curl_close($ch); 
	$fp = fopen("$graph_dir$label.png","w");
	fwrite($fp,$png);
	fclose($fp);
}