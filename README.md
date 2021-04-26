# vayd-delegate-ballot
 HTML form that uses CSV and Javascript to submit a delegate vote into a Google Spreadsheet

## How it works
The HTML contained in this repository reads from two CSV files. The first CSV file helps authenticates the delegate with a unique code. The second one relates the delegate to a specific delegation and adds the delegate vote ratio. The latter is based on a Credentials Report that would drive the vote allocation for each delegation. 

This logic was used at the Virginia Young Democrats convention to add voting-specific validation when a delegate casted a vote. It also allowed us to leverage Google Sheets to avoid writing into a proprietary database, hence driving bandwidth and processing constraints significantly down. This, in turn, allowed the organization to have around 200 delegates casting votes simultaneously without major performance degradation.

Each delegation had a unique scriptURL that wrote on a specific Google Sheet. This allowed a delegation chair to mimic the same dynamic as it were in an in-person convention while still making the individual delegate vote anonymous. This meant that a delegation chair would not know how a single delegated voted but instead just know who, based on a roll call, had not voted yet. 

We have truncated the end of the scriptURL from the CSV file for security reasons. However, please see below under previous work on how to populate this unique scriptURL.

## Previous Work
This work uses the [jamiewilson/form-to-google-sheets](https://github.com/jamiewilson/form-to-google-sheets) repository to submit the values entered in the HTML to a Google Sheet