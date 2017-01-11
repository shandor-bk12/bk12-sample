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
# Blackbaud K12 "On" products API example in PowerShell to retrieve a list
# and open it in Excel.  
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


# School Website Login Information.
$schoolWebsite = "https://integration.myschoolapp.com"
$apiUser = "my_username"
$apiPassword = "my_password"
$listID = "49748" # See note above about where to find the listID

# Get a login token to use for authentication
$response = Invoke-RestMethod "$schoolWebsite/api/authentication/login?username=$apiUser&password=$apiPassword&format=json"

if($response.Token) { # Did we get a token in our response?
    $token = $response.Token

	# Use the authentication token to retrieve a list.  You will need to
	# replace the list ID with one from your account.
    $response = Invoke-RestMethod "$schoolWebsite/api/list/$listID/?t=$token&format=json"

	# Convert the response to a CSV file that can be read by Excel
    $response | Export-CSV -NoTypeInformation -Encoding ascii -Path "C:/tmp/list.csv"

	# Open the list in Excel or whatever app handles CSV files
    Invoke-Item "C:\tmp\list.csv"

} else {
    echo "Login failed."
}
