<?php
#------------------------------------------------------------------------------ 
# Copyright (c) 2017 Shandor Simon <s@duff.io>  https://duff.io
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
# Version 1.0.2 - 2017-01-09
#------------------------------------------------------------------------------ 
# Blackbaud K12 "On" products API example in PHP to retrieve a list
# and display it on the screen  
#
# For this to work, you will need to create a list within the "On" products and
# get the list ID.  Lists can be created in many places within the system, 
# including Core > Lists > More Lists. Each list has a unique ID.  You can find
# this ID within Core > Lists > More Lists by looking at the "Edit" link next to
# the URL.  Part of the URL should show "slid=" followed by a number.  That
# number is the list ID.  For example, if the "Edit" link shows:
# javascript:__pdL('23192','Edit%20Basic%20List:%20API%20List',%20'1',
# %20'slid=49748~admin=False',%20'',%20'False',%20'0',%20'',%20'default.aspx')
# Then the list ID is 49748
#
# This requires the PHP Httpful client, available at http://phphttpclient.com/
# which makes dealing with APIs more reliable and consistent in PHP

include_once ('./httpful.phar');     # Httpful http://phphttpclient.com/

# replace my_school, my_username and my_password with values from your school
$schoolWebsite = "https://my_school.myschoolapp.com"  # website used to login to the "ON" products
$apiUser = "my_username";
$apiPassword = "my_password";
$listID = "49748"; # See note above about where to find the listID

# Authenticate to the API, and get a token to use for further calls
$uri = "$schoolWebsite/api/authentication/login/?username=". $apiUser . "&password=" . $apiPassword . "&format=json";
$response = \Httpful\Request::get($uri)->expectsJson() ->send();

# Store the token as a variable so it's handy
$token = $response->body->{"Token"};

# Call the list API with the ID# of the list we want
$uri = $schoolWebsite . "/api/list/" . $listID . "/?t=" . $token . "&format=json";
$response = \Httpful\Request::get($uri)-> send();
$listResults = $response->body;

var_dump($listResults); # Not the prettiest way to show the data, but you get the idea

?>