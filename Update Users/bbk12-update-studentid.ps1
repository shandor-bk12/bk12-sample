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
# Version 1.0.1 - 2017-01-11
#------------------------------------------------------------------------------ 
# Sample PowerShell script for Blackbaud K12 "ON" products API that
# updates the students ID field for a bunch of students based on a CSV file
#
# USE THIS AT YOUR OWN RISK.  YOU CAN CORRUPT OR LOSE DATA.
#

$schoolWebsite = "https://my_school.myschoolapp.com"  # website used to login to the "ON" products
$apiUser = "my_username"
$apiPassword = "my_password"
$updateFilePath = "C:\tmp\update.csv"

# First, we use the login method to get a authentication token using our username and password
$response = Invoke-RestMethod "$schoolWebsite/api/authentication/login?username=$apiUser&password=$apiPassword&format=json"

if($response.Token) { # Did we get a token in our response?
    $token = $response.Token
    $userId = $response.UserId # The login method returns the UserId of the user used to login

    $updateFile = Import-Csv $updateFilePath 

    foreach ($person in $updateFile) {
        echo "Attempting to update the user with system ID: $($person.SystemID)"
        $userInfo = Invoke-RestMethod "$schoolWebsite/api/user/$($person.SystemID)/?t=$token&format=json"
        if ($person.SystemID -eq $userInfo.UserId) {
            # This should always be true, but we're checking just in case

            # We need an array with the UserId and any parameters we want to update
            $updateUser = @{
                UserId = $person.SystemID
                StudentId = $person.StudentID
            }

            $jsonUpdateUser = $updateUser | ConvertTo-Json # Convert it to JSON foramt

            $request = "$schoolWebsite/api/user/$($person.SystemID)/?userId=$($person.SystemID)&t=$token&format=json"
            $response = Invoke-RestMethod $request -Method Put -Body $jsonUpdateUser   # Note we are using the Put method to update!

            if($response.Message -eq $($person.SystemID)) {  # The API respond with the system id of the modified user
                # Unfortunately, this response does not actually let us know if the update was successful.  If the user
                # making the API call lacks sufficent permission, it will fail silenetly.  So, we are going to do another
                # API call to check if our change actually happen
                
                $userInfo = Invoke-RestMethod "$schoolWebsite/api/user/$($person.SystemID)/?t=$token&format=json"

                if ($userInfo.StudentId -eq $person.StudentID) {
                    echo "Updated user $($person.SystemID) with StudentId $($person.StudentID)"
                } else {
                    echo "Failed to update user $($person.SystemID) with StudentId $($person.StudentID)"
                }
            } else { 
                echo "ERROR: Problem Updating user $response" 
            }

        } else {
            echo "ERROR: Something went wrong in finding the user: $($person.SystemID)"
        }
    }

} else {
    echo $response.Error # This displays the error message
}
 