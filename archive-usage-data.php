<?php
/**
  * archive-usage-data.php - A PHP script to automate the downloading of
  * usage data from Cloudtrax.com for archival purposes.
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

// The name of the data file to archive into
$logfile = "./cloudtrax.log";

// URLs at CloudTrax.com -- you shouldn't really need to change these.
$cloudtrax_base = "https://cloudtrax.com/";
$loginurl = "dashboard.php";
$dataurl = "nodes_attnt2.php";

/**
  * Login to Cloudtrax.com, saving cookies to a temporary file.
  * This has the effect of saving a PHP session ID cookie, which is
  * later sent *back* to authenticate future requests.
  */
exec("curl -s -L -e $cloudtrax_base$loginurl -c /tmp/cloudtrax-cookies.txt -X POST -d 'account=" . urlencode($username) . "&password=" . urlencode($password) ."&edit=Login' $cloudtrax_base$loginurl");

/**
  * Download the data from Cloudtrax.com.
  */
$ch = curl_init("$cloudtrax_base$dataurl?id=" . urlencode($network) . "&showall=1&details=0");
curl_setopt($ch,CURLOPT_COOKIEFILE,"/tmp/cloudtrax-cookies.txt");
curl_setopt($ch,CURLOPT_COOKIEJAR,"/tmp/cloudtrax-cookies.txt");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
$html = curl_exec($ch);
curl_close($ch); 

/**
  * Take the HTML we downloaded and parse the DOM, looking at the cells
  * of the table with id of 'mytable'.
  */
$dom = new DOMDocument();
//$dom->validateOnParse = true;
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$table = $xpath->query("//*[@id='mytable']")->item(0);
$rows = $table->getElementsByTagName("tr");
foreach ($rows as $rowkey => $row) {
  $cells = $row->getElementsByTagName('td');
  foreach ($cells as $cellkey => $cell) {
    $data[$rowkey][$cellkey] = innerHTML($cell);
  }
}

/**
  * Write the data out to a log file.
  * Obviously you could output elsewhere too -- to MySQL or SQLite, or...
  */
$fp = fopen($logfile,"a");
foreach ($data as $key => $value) {
	list($id,$label) = explode("<br></br>",$data[$key][1]);
	$id = strip_tags($id);
	$users = $data[$key][2];
	list($down,$up) = explode("<br></br>",$data[$key][3]);
	fwrite($fp,strftime("%Y-%m-%d %H:%M:%S") . "\t" . $id . "\t" . $label . "\t" . $users . "\t" . $down . "\t" . $up . "\n");
}
fclose($fp);

/**
  * Helper function from http://kuttler.eu/post/php-innerhtml/
  * Allows the innerHTML of a table cell to be extracted.
  */
function innerHTML( $contentdiv ) {
	$r = '';
	$elements = $contentdiv->childNodes;
	foreach( $elements as $element ) { 
		if ( $element->nodeType == XML_TEXT_NODE ) {
			$text = $element->nodeValue;
			$r .= $text;
		}	 
		// FIXME we should return comments as well
		elseif ( $element->nodeType == XML_COMMENT_NODE ) {
			$r .= '';
		}	 
		else {
			$r .= '<';
			$r .= $element->nodeName;
			if ( $element->hasAttributes() ) { 
				$attributes = $element->attributes;
				foreach ( $attributes as $attribute )
					$r .= " {$attribute->nodeName}='{$attribute->nodeValue}'" ;
			}	 
			$r .= '>';
			$r .= innerHTML( $element );
			$r .= "</{$element->nodeName}>";
		}	 
	}	 
	return $r;
}