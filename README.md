# PHP script to track a history of client public IP addresses

## Server side

### Directory and file structure:

* `.ip` (hidden root directory)
  * `index.html` (empty file to prevent automatic content display)
  * `some-uuid` (directory named using a unique identifier known only by the client)
    * `index.php` (copy of `public-ip-history.php`)

### Usage

Requesting `/.ip/some-uuid/` will trigger `index.php`,
which will track the client IP address in `/.ip/history.json`.

### History format

Example of `history.json` file:

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

## Client side

### Store current IP

Create a file `/etc/cron.d/public-ip-history`, with the following content,
to call the server and store current IP address every 10 minutes:

```cron
*/10 * * * *   user   ( cd /tmp ; wget -O- http://my.domain.net/.ip/some-uuid/ >public-ip.out 2>public-ip.err )
```

### Display public IP history

Visit page `http://my.domain.net/.ip/history.json` to display the public IP history.
