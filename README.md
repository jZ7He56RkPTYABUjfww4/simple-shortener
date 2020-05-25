# Simple Shortener

A super simple url shortener. I wanted a url shortener but all the existing ones were too complicated and I couldn't be bothered to make them work with my existing setup. So I made one that worked for me.

## Installation

1.  Put this git repo on a webserver
2.  Create a mysql database
3.  Run `database.sql` on the database
4.  Edit `config.php` and enter your database credentials

## Notes

-   I didn't bother to change the mysql statements to prepared
-   For password protection use htpasswd on `api.php` and `admin.php`

<br><br>

Forked from <https://github.com/mathiasbynens/php-url-shortener> by [Mathias Bynens](http://mathiasbynens.be/)
