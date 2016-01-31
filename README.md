# ![vendorify-gr-sm.png](https://bitbucket.org/repo/4yy7r9/images/3970888937-vendorify-gr-sm.png) Vendorify 

---

![master](https://codeship.com/projects/71d37270-395d-0133-347d-7a11fbda2904/status?branch=master)

## Overview

Vendorify was created to allow retail shops with a multiple vendor/commission structure to be able to utilize the [Square payment system](https://squareup.com/) efficiently. The application allows for business owners to setup and maintain their vendors, provide real-time email sale notifications to vendors, and generate payroll reports for individual vendors that calculates discounts, fees, commission, and vendor rents. This provides the business owner with a one-click solution to generating and sending payroll reports.

Vendorify utilizes Square items with an empty price (typed in at the time of sale) and their name (code) to make a connection between vendors and Square item sales. Multiple item names (codes) may be utilized to tie multiple items to a vendor.

### Features

**Vendor Management** 

Manage your vendors and their information including their business name, phone, name, email, rental fee, commission fee, and square transaction codes.

**Google Contacts Import/Sync** 

Store your vendors information in google contacts and import/re-sync them with a click of a button by connecting your google account.

**CSV exports**

Export a csv of your vendors, report summaries, and reports.

**Real-time vendor notifications** 

Vendors receive real time sale notifications tied directly to the Square payment system any time an item of theirs sells.

**Payroll Reports** 

Generate reports for specific timeframes in order to determine payroll without having to do any manual calculations.

**Payroll Report Notifications** 

Send generated reports to individual or all vendors with messages.

**Vendor Transaction Management** 

Update transactions changing the transaction description and the associated vendor to account for Square Register mistakes.

## PHP CLI Commands

The vendorify application has several Laravel Artisan commands that can be run to perform specific tasks.

---

`square:process-payments {start?} {end?}`

The process payments command grabs transactions from the Square API and attempts to assign them to a vendor in the system. If a transaction is found and matched, a sales notification will be sent to the vendor. Vendors with the `notify` flag disabled will not receive these notifications.

**Parameters**

* start (optional): a string timestamp to start at
* end (optional): a string timestamp to end at

_The start and end parameters may be any timestamp format that can be parsed by PHP's `strtotime()` function. When left out, the command will default to 'midnight' and 'tomorrow', parsing a full day of payments._

---

`report:generate {start} {end} --include-rent`

The report generate command will parse transaction data in the system and create a vendor report that includes the calculated commission and rent totals. The `include-rent` flag determines whether or not the report should take vendor rent information into account when calculating totals.

**Parameters**

* start: report start date
* end: report end date

_The start and end parameters may be any timestamp format that can be parsed by strtotime()._

---

`report:send {reportId} {vendorId?}`

The report send command sends vendor reports to all active/flagged vendors in the system via email. The email also includes an attached csv for the vendors records/tax purposes.

**Parameters**

* reportId: id of the report to send
* vendorId (optional): vendor id of the report to send

_If the vendorId parameter is omitted, the report will send to all vendors._

---

## API

The Vendorify application comes baked in with a RESTFul API that can be utilized by 3rd party applications.
To generate an access token you can use the following artisan command:

`api-key:generate --user-id=an-admin-vendor-id`


### Authentication

**HTTP Header**

`X-Authorization your-access-token`

### Endpoints

**Vendor Endpoints**

* `GET,POST` `/api/vendor`
* `GET,POST` `/api/vendor/{id}/transaction`
* `GET,PUT,DELETE` `/api/vendor/{id}/transaction/{id}`

```json
{
    "id": "int",
    "name": "string",
    "business": "string",
    "phone": "string",
    "status": "active|pending|flagged|inactive",
    "rent": "int",
    "rate": "int",
    "email_notification": "boolean",
    "sms_notification": "boolean",
    "email": "string",
    "group": "admin|user",
    "notes": "text",
    "google_email": "string",
    "google_token": "string",
    "created_at": "Y-M-D H:M:S",
    "updated_at": "Y-M-D H:M:S",
    "transactions": [
         {
           "id": "int",
           "vendor_id": "int",
           "payment_id": "int",
           "code": "string",
           "quantity": "int",
           "description": "string",
           "gross": "decimal",
           "discounts": "decimal",
           "net": "decimal",
           "processed_at": "Y-M-D H:M:S",
           "created_at": "Y-M-D H:M:S",
           "updated_at": "Y-M-D H:M:S"
          }
    ]
}
```

**Report Endpoints**

* `GET` `/api/report`
* `GET,PUT,DELETE` `/api/report/{id}`
* `POST` `/api/generate/report [ {start}, {end}, {rent?} ]`
* `POST` `/api/send/report [{reportId}, {vendorId?}, {message?} ]`

_The only parameter of a report that can be modified by a put request is `message`. The message parameter is used as an optional message included when sending a report to all vendors. The `message` parameter for a report is only persisted in the database for global reports (sending all reports) individual vendor report messages are not persisted._

---

## Scheduled Tasks

To deal with daily transactions and to perform close to real-time sale notifications for the vendors, the application runs the `square:process-payments` command every 10 minutes. The command grabs transactions for the entire day, but will only process each transaction once by keeping a record of which Square "payment ids" it has already parsed. Having the command grab transactions for the entire day prevents most dataloss (unless the application/square is down for more than a full day).

The application being built on Laravel utilizes the Laravel scheduler. To ensure the scheduler runs correctly the following cron is required: 

`* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

---

## Application Queue

The application uses a queue monitored and run by [Supervisor](http://supervisord.org/). The queue is required to queue email notifications when sending reports. This allows for the system to send a report email once every 5 seconds to prevent high volume email output by the system.


To restart or start a supervisor task the following might be necessary.

```
sudo supervisorctl start business-name-queue:*
sudo service supervisor restart
```

The following supervisor config is recommended:

```
[program:business-name-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --sleep=3 --tries=3 --daemon
autostart=true
autorestart=true
user=webuser
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/supervisor.log
```

Supervisor config files are typically found at `/etc/supervisor/conf.d/business-name-queue.conf`
