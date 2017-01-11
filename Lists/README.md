Blackbaud K12 API Lists Example

One of the easiest ways to get data out of Blackbaud using the API, is to make use of their list function.  This allows for you to get the content of a Advanced or Basic list as a JSON or XML file.  You can read their documentation here: http://on-api.developer.blackbaud.com/API/resources/list/

There is sample code using PHP and PowerShell.

For this to work, you will need to create a list within the "On" products and get the list ID.  Lists can be created in many places within the system, including Core > Lists > More Lists. Each list has a unique ID.  You can find this ID within Core > Lists > More Lists by looking at the "Edit" link next to the URL.  Part of the URL should show "slid=" followed by a number.  That number is the list ID.  For example, if the "Edit" link shows: javascript:__pdL('23192','Edit%20Basic%20List:%20API%20List',%20'1',%20'slid=49748~admin=False',%20'',%20'False',%20'0',%20'',%20'default.aspx') Then the list ID is 49748


I have a post discussing how to use this in PHP on my blog: http://duff.io/2016/07/19/using-php-with-the-blackbaud-k12-api/ and with PowerShell here: http://duff.io/2016/07/13/blackbaud-k12-on-api/

