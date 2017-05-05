this project has been moved to gitlab: https://gitlab.com/huwylphi/nPOauditTrailViewer

--------------------
# nPOauditTrailViewer
an alternativ audittrail viewer for novaPro Open

## system requirement:
- web-server like iis
- php with sqlsrv extensions activated
- MS SQL-Server
- nPO AuditTrail database attached into the sql-server
- Microsoft ODBC Driver for SQL Server
- Adobe flash-player for file-export from html report web-site (optional)

## tested environment:
- Windows Server 2008 R2 with IIS, Windows Server 2012 R2 with IIS
- PHP 5.3, 5.4, 5.5 with sqlsrv extensions activated
- MS SQL-Server 2008 R2 Express edition but should be working with 2005 or 2012 or 2014 too
- MS IE9, IE10, IE11, Google Chrome 42, Mozilla Firefox 35

## setup:
1. unzip the file in to a folder
2. integrate web-app in to iis
3. configure the parameters in the config.json.txt file
4. configure audit-trail in nPO

## use:
- generate web-report via the url `http://<host>/<virtual directory>/index.html`
- url parameters: goto `/inc/mains.js`
- generate csv-export via command-line: `php -f exportCsv.php out=<exportFilePath> startDate=<start date in yyyy-mm-dd format> endDate=<end date in yyyy-mm-dd format>`
- generate predefine csv-export via command-line: `php -f reportLastMonth.php out=<exportFilePath>`
- or make your own predefine csv-export php script like in the `reportLastMonth.php` (you only have to calculate the startDate and endDate var)

**URL Parameters** (`http://<host-name or ip>/<virtual directory>/?<PARAM1&PARAM2>`):
- dynFilterList: ex. `dynFilterList=%5B"TagDescription%20LIKE%20%27%25UK3%25%27"%5D` (like SQL filter but use url encoder. You can use an online url encoder like [url-encode-decode](http://www.url-encode-decode.com/))
- startDate and endDate: ex. `startDate=2015-06-01` or `endDate=-7` in absolute or in relative format
- autoStart: ex. `autoStart=true`
- pageLen: ex. `pageLen=50`
- colSort: ex. `colSort=-5` or `colSort=-0`. sort by col index
- top: ec. `top=25`. the top x items
