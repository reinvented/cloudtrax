Download CloudTrax Usage Data
=============================

The [CloudTrax.com Open-Mesh access point manager](http://cloudtrax.com) provides usage data for access points in a network.

There is, however, now way to find historical data -- only the last 24 hours is provided. To allow for the archiving of data,
the **archive-usage-data.php** script will scrape this data out of Cloudtrax.com and dump it into a tab-delimited ASCII file
where you can then manipulate it further.

Requirements
------------

* PHP 5.x (with DOM module)
* cURL

Usage
-----

Paste your Cloudtrax.com login information and network name into the script, and set the file where you would like log output, then:

    php archive-usage-data.php

Download CloudTrax Graphs
=========================

The [CloudTrax.com Open-Mesh access point manager](http://cloudtrax.com) provides graphs of bandwidth usage and active users.

To include these graphs in other websites requires an automated process to download them, which is what the script download-graphs.php will do.

Requirements
------------

* PHP 5.x
* cURL

Usage
-----

Paste your Cloudtrax.com login information and network name into the script, and set the directory where you would like graphs output, then:

    php download-graphs.php

Generate CloudTrax Vouchers
===========================

The [CloudTrax.com Open-Mesh access point manager](http://cloudtrax.com) has two mechanisms for generating vouchers.

The regular mechanism, accessed through the [administrative interface](http://cloudtrax.com), allows for the bulk-generation of random vouchers.

The [lobby assistant](http://lobby.cloudtrax.com) mode allows for manual generation of vouchers.

Because we had a list of 50 staff that we wanted to create non-random vouchers for, we developed an alternative method that uses a PHP script to programmatically create vouchers from the command line using the "lobby assistant" mechanism.

Requirements
------------

* PHP 5.x
* cURL

Usage
-----

Create comma-delimited ASCII file with five columns:

* name - full name of user
* class - class name or role of user ("Grade 1", etc.)
* access - "Yes" or "No" as to whether user gets wifi access voucher.
* random - a random number; we use 100-999, but you can choose whatever you like.
* voucher - the voucher code assigned to the user; we concatenate last name + random number

The resulting file looks something like this:

    Harry Vlenson,8B,Yes,236,vlenson236
    Fred Wiley,9A,Yes,281,wiley281
    Sven Boshane,5B,Yes,778,boshane778

Place the file, called users.csv, into the same directory as this script, and then:

    php generate-cloudtrax-vouchers.php

The result should be, when you login to your Cloudtrax.com voucher page, new vouchers
created for every user.

Credits
-------

* [Peter Rukavina, Reinvented Inc.](http://ruk.ca/)
* [Prince Street Home and School](http://princestreetschool.ca/teachernet)