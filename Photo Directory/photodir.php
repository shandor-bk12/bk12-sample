<html>
 <head>
 <style type="text/css">
 table { page-break-inside:auto }
 tr { page-break-inside:avoid; page-break-after:auto }
 thead { display:table-header-group }
 tfoot { display:table-footer-group }
 </style>
 </head>
 
<?php
#------------------------------------------------------------------------------ 
# Copyright (c) 2016-2017 Shandor Simon <s@duff.io>  https://duff.io
# 
# MIT License
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#------------------------------------------------------------------------------ 
# Version 1.0.1 - 2016-07-01
#------------------------------------------------------------------------------ 
# This requires the PHP Httpful client, available at http://phphttpclient.com/
# which makes dealing with APIs more reliable and consistent in PHP
 
error_reporting( error_reporting() & ~E_NOTICE );
ini_set('max_execution_time', 300); # set to five minutes (300 seconds)
$time_start = microtime(true); # start timing how long this script takes
include_once ('./httpful.phar'); # use Httpful http://phphttpclient.com/

# replace my_school, my_username and my_password with values from your school
$schoolWebsite = "https://my_school.myschoolapp.com"  # website used to login to the "ON" products
$apiUser = "my_username";
$apiPassword = "my_password";
$debug = true;
$genericPhotoURL = "http://vignette4.wikia.nocookie.net/detectiveconan96/images/7/72/Generic_Male_Profile.jpg/revision/latest?cb=20140709000724"; # This photo will show if we don't have a user profile 
 
#Some parameters for the photo directory
$numColums = 4; # How many columns do we want?
$tdHeight = 210; # Height of the table cell that holds the entry
$imgHeight = 170; # Image height
 
// Get authentication token for the Blackbaud K12 API
$uri = "$schoolWebsite/api/authentication/login/?username=". $apiUser . "&password=" . $apiPassword . "&format=json";
$response = \Httpful\Request::get($uri)->expectsJson() ->send();
$token = $response->body->{"Token"};
 
if (strpos($token, 'Invalid') !== false) {
 echo "Invalid Login.<br>";
} else {
 echo "<table border=0>";
 $currentColumn = 1;
  
 # Use Blackbaud K12 list API to gather nonteaching staff and teachers.
 # You can get the listid by hovering over edit and look for slid= in the link
 # on the website. Using lists is faster than doing individual API calls, 
 # and they can be edited by end users.
 #
 # If you'd like to generate a photo directory of something else, you can
 # change this list.
 #
 # The following objects were selected for this list:
 # + User Base
 # + User School Defined Fields
 # + User Role
 # + User Detail
 #
 # The following fields were selected for "display" (Display As)
 # + User Base.User ID (UserID)
 # + User Base.First Name (FirstName)
 # + User Base.Last Name (LastName)
 # + User Base.E-Mail (email)
 # + User Base.Host ID (HostID)
 # + User School Defined Fields. Defined 2 (latinid)
 #
 # The list has the following filter:
 # User Role.Role any of Non-Teaching Staff,Teacher
 #
 # The list is ordered by: (Change, to change sort order for photo directory)
 #
 # User Base.Last Name Ascending
 # Then By
 # User Base.First Name Ascending
 #
  
 $uri = $schoolWebsite . "/api/list/46815/?t=" . $token . "&format=json";
 $response = \Httpful\Request::get($uri)-> send();
 $employees = $response->body;
  
 $i = 0;
  
 foreach ($employees as $employee) {
 $i++;
 $fname = $employee->{"FirstName"};
 $lname = $employee->{"LastName"};
 $whsid = $employee->{"UserID"};
 $hostid = $employee->{"HostID"};
  
 # get details on this employee via the Blackbaud K12 API
 # /user/extended uses the system ID or "UserID" in the call
 # to get detailed information on an individual user. It can
 # give us access to data we cannot otherwise see, such as the 
 # URL to their profile photo.
 $uri = $schoolWebsite . "/api/user/extended/" . $whsid . "/?t=" . $token . "&format=json";
 $response = \Httpful\Request::get($uri)-> send();
  
 # Let's set some variables with the data from user/extended:
 $employeeDetail = $response->body; 
 $uname = $employeeDetail->{"UserName"};
 $employeeLatinID = $employeeDetail->{"CustomField2"}; # My school stores our ID number in this custom field
 $employeePhotobookCode = $employeeDetail->{"CustomField10"}; # Code to use to omit people from the directory
 $employeeProfilePhoto = $employeeDetail->{"ProfilePhoto"};
 $employeePhotoURL = $employeeProfilePhoto->{"LargeFilenameUrl"};
 if($employeePhotoURL !== "") {
 $employeePhotoURL = $schoolWebsite . $employeePhotoURL; # The photo URL needs the school website added to it
 }
  
 # Job Titles are harder. They are contained within an array called OccupationList.
 # A single person can have multiple occupations. We are going to go through the
 # array and look at each occupation. We don't have a great well to tell which is 
 # the right one to use. In my example, I'm going to look for a business name 
 # matching my school name and use the title found there. Obviously, if there are
 # more then one occupation listed with the same employer name, it will overwrite
 # the data, but hopefully, we don't have the same person listed more than once.
 # Finally, as a fail safe, it will set it to the last title found, if we haven't
 # set the job title yet.
  
 $employeeOccupationList = $employeeDetail->{"OccupationList"};
 $employeeTitle = "";
 foreach ($employeeOccupationList as $employeeOccupation) {
 $employeeBusinessName = $employeeOccupation->{"BusinessName"};
 $employeeOccupationTitle = $employeeOccupation->{"JobTitle"};
  
 if (($employeeBusinessName = "Latin School of Chicago") || ($employeeBusinessName = "The Latin School of Chicago")) {
 $employeeTitle = $employeeOccupationTitle;
 } else {
 if ($employeeTitle == "") { 
 // If we haven't set something yet for the user, let's try setting this title, 
 // even though the business name isn't set right
 $employeeTitle = $employeeOccupationTitle;
 }
 }
 }
  
 # Let's grab their work address. It's stored in an array, but we never
 # have more than one address. This will loop through it and grab the
 # last (hopefully only) value. If you have multiple values here, you'll
 # have to come up with a better way of dealing with this.
 $employeeOffice = "";
 $employeeAddressList = $employeeDetail->{"AddressList"};
 foreach ($employeeAddressList as $employeeAddress) {
 $employeeAddressType = $employeeAddress->{"address_type"};
 $employeeAddressLn1 = $employeeAddress->{"AddressLine1"};
  
 if($employeeAddressType == "Business/College") {
 $employeeOffice = $employeeAddressLn1;
 }
 }
  
 # Now let's grab their work phone number. Same deal as address.
 $employeeOfficePhone = "";
 $employeeOfficePhoneList = $employeeDetail->{"PhoneList"};
 foreach ($employeeOfficePhoneList as $employeeOfficePhoneItem) {
 $employeeOfficePhoneType = $employeeOfficePhoneItem->{"Type"};
 $employeeOfficePhoneNumber = $employeeOfficePhoneItem->{"PhoneNumber"};
  
 if($employeeOfficePhoneType == "Business/College") {
 $employeeOfficePhone = $employeeOfficePhoneNumber;
 $employeeOfficePhone = str_replace(' ', '', $employeeOfficePhone);
 $employeeOfficePhone = str_replace(')', '.', $employeeOfficePhone);
 $employeeOfficePhone = str_replace('(', '', $employeeOfficePhone); 
 $employeeOfficePhone = str_replace('-', '.', $employeeOfficePhone); 
 }
 }
  
 if($employeePhotobookCode == "") { 
 # We use CustomField10 as a place to exclude folks from the photo directory
  
 if($currentColumn == 1) {
 echo "<tr>";
 }
 
 echo "<td valign=top align=center height=$tdHeight width=25%>";
 # This is the table cell with all of the user data
  
 # First the photo: 
 if($employeePhotoURL !== "") {
 echo "<a href=\"$uri\"><img src=\"img.php?url=$employeePhotoURL\" height=\"$imgHeight\"></a><br>\n";
 # img.php is a script that crops and resizes the images to a standard size
 } else {
 echo "<a href=\"$uri\"><img src=\"img.php?url=$genericPhotoURL\"></a><br>\n";
 }
 echo "<font size=-1>$fname $lname<br></font>\n";
 echo "<font size=-2>$employeeTitle<br></font>\n";
 echo "<font size=-2>$employeeOffice &nbsp; &nbsp; $employeeOfficePhone<br></font>\n";
  
 if($currentColumn == $numColums) {
 echo "</td></tr>";
 $currentColumn = 1; 
 } else {
 echo "</td>";
 $currentColumn++;
 }
 
 }
  
 if($i>2500) { break;} # Stop if we have way too many results. Can decrease number to debug 
 } 
  
 if ($currentColumn == $numColums) {
 echo "</table>";
 } else {
 echo "</tr></table>";
 
 }
}
 
echo "<br><br>Employees Found: $i<br>";
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Run time: " . round($time,2) . " s";
?>
 
</html>