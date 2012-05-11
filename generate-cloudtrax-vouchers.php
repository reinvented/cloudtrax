<?php
/**
  * generate-cloudtrax-vouchers.php - A PHP script for bulk-generation of
  * CloudTrax.com access vouchers.
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
  * @version 0.1, May 11, 2012
  * @link http://princestreetschool.ca/content/bulk-generation-cloudtraxcom-vouchers Documentation
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2012, Prince Street Home and School
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * User-configurable settings
  */

// Username and password for lobby.cloudtrax.com
$username = "";
$password = "";

// Voucher parameters
$duration = 4368;   // how many hours should the vouchers last for?
$down = 4.5;        // downstream bandwidth in Mbps
$up = 2;            // upstream bandwidth in Mbps
$maxusers = 3;      // how many devices can voucher be used for?

// URLs at CloudTrax.com -- you shouldn't really need to change these.
$loginurl = "https://lobby.cloudtrax.com/lobby.php";
$voucherurl = "https://lobby.cloudtrax.com/vouchers/vouchers2.php";

/**
  * Login to Cloudtrax.com, saving cookies to a temporary file.
  * This has the effect of saving a PHP session ID cookie, which is
  * later sent *back* to authenticate future requests.
  */
exec("curl -s -L -e https://lobby.cloudtrax.com/lobby.php -c /tmp/cloudtrax-lobby-cookies.txt -X POST -d 'account=" . urlencode($username) . "&password=" . urlencode($password) ."&edit=Login' $loginurl");

/**
  * Loop through the CSV file of users, which needs to have the
  * following structure: name,class,access,random,voucher where
  *  name - full name of user
  *  class - class name or role of user ("Grade 1", etc.)
  *  access - "Yes" or "No" as to whether user gets wifi access voucher.
  *  random - a random number; we use 100-999, but you can choose whatever you like.
  *  voucher - the voucher code assigned to the user; we concatenate last name + random number
  */
$fp = fopen("users.csv","r");
while(!feof($fp)) {
	list($name,$class,$access,$random,$voucher) = explode(",",trim(fgets($fp,4096)));
	if ($access == "Yes") {
		print $name . "\n";
		passthru("curl -s -o output.txt -e https://lobby.cloudtrax.com/vouchers/edit_vouchers.php -c /tmp/cloudtrax-lobby-cookies.txt -b /tmp/cloudtrax-lobby-cookies.txt -X POST -d 'submit=Create+Vouchers&voucher_code=" . urlencode($voucher) . "&comment=" . urlencode($name . " / " . $class) ."&duration=$duration&max_users=$maxusers&downValue=$down&upValue=$up&valid=Hours+Valid&login=Login+Code&border=1&logo=1' $voucherurl");
	}
}

