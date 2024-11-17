Usage: php parser.php parameters

Description:
Run this script with the name of the parser class to parse URLs from csv file.

Parameters:
--parser: Name of the class that contains 'fulfilled' and 'rejected' methods to handle the responses.
--csv_file: csv file containing the URLs to check
--concurrency: number of async threads
--timeout: timeout for Guzzle requests
--ssl_verify: skip SSL check
--allow_redirects: following redirects

Example:
php parser.php --parser=HttpCodeCheck --csv_file=file.csv --concurrency=10 
--timeout=10 ssl_verify=false --allow_redirects=true

This command will use 'HttpCodeCheck' class from parsers folder for handling the HTTP request responses.
