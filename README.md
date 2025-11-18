# PHP script to track public IP addresses over time

A simple PHP-based procedure to store a timestamped history of client public IP addresses as a JSON structure.

## Server side file structure

* `.ip` (hidden root directory)
  * `index.html` (empty file to prevent automatic content display)
  * [`public-ip-history.php`](public-ip-history.php) (main script, referenced by `index.php`)
  * `some-uuid` (directory named using a unique identifier dedicated to one client only)
    * [`index.php`](index.php) as follows:

```php
<?php
$ipHistoryFileName = '../some-history.json';
require_once('../public-ip-history.php');
?>
```

## Manual usage

Requesting `/.ip/some-uuid/` will trigger `index.php` execution,
which will track the client IP address in `/.ip/some-history.json`.

To keep it simple, no constraint is put on the HTTP verb to be used.

It can so be triggered easily from any web browser,
HTTP client (`curl`, `wget`, `bruno`...),
or custom application.

## Automatic and recurring usage

On Linux, create a file `/etc/cron.d/public-ip-history` with the following content,
so that the current IP address is tracked every 10 minutes:

```cron
*/10 * * * *  user  (cd /tmp; wget -O- http://some.domain.ext/.ip/some-uuid/ >pub-ip.out 2>pub-ip.err)
```

These can be changed to your liking:
* `public-ip-history`, `pub-ip.out`, ` pub-ip.err`: file names
* `some-uuid`: unique caller/client identifier
* `10`: time interval in minutes

These should be changed to fit your technical context:
* `user`: the Linux user to be used to run the command
* `http://some.domain.ext/`: the root URL of the server used to track IP addresses

## History file format

Example of `some-history.json` file:

```json
[
  {
    "ipAddress": "140.150.160.170",
    "firstDateTime": "2024-01-01,03:29:40",
    "latestDateTime": "2024-05-05,14:12:09"
  },
  {
    "ipAddress": "100.110.120.130",
    "firstDateTime": "2023-01-01,14:20:01",
    "latestDateTime": "2024-01-01,03:19:39"
  }
]
```

Entries are ordered from newest to oldest.

Timestamps are in the following sortable format: `YEAR-MONTH-DAY,HOURS:MINUTES:SECONDS`
