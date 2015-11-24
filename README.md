# Cellio
**A Twilio-based cell phone dump**

## Description

When I moved abroad, I wanted to keep my U.S. cell phone number because I've had it forever, and because I have a lot of things that are still attached to this number.  [Twilio](https://www.twilio.com) allows you to port your phone number to them and hold onto it for $1 USD per month, which was a perfect option.  Even better, Twilio allows you to execute web addresses upon receipt of a call or SMS and MMS -- an even more perfect option.

The problem, though, is while Twilio offers a few hosted options (called [Twimlets](https://www.twilio.com/labs/twimlets)), they are not very feature-rich.  Specifically, there exists no option to receive an email upon receipt of a text message.  Thus, like any good hacker, I coded my own, hence this project.  (I still use Twimlets, but only as a fallback if my scripts are inaccessible.)

## Dependencies

* [Twilio](https://www.twilio.com) - Obviously, none of this will work without a Twilio account and active phone number.

* PHP - The main TwiML scripts are coded in PHP, because reasons.  The Gearman worker is, too, but this will eventually change (see TODO below).
  * Modules: *curl*, *dom* (with *xml*), *json*, *pecl-gearman*

* [Gearman](http://gearman.org) - Asynchronous job handler.  I added this so the TwiML scripts could respond immediately to Twilio, but still queue jobs to fetch the call/text data (e.g. MMS attachments or voicemail MP3s).
  * Debian Packages: *gearman-job-server*, *libgearman-client-perl*


## TODO

* The Gearman worker should be coded in Perl, simply because [PHP is meant to die](https://www.bram.us/2013/11/11/php-is-meant-to-die/).  Right now it's in PHP for a proof-of-concept (since the libraries are super simple), but PHP leaks memory like a sieve.

* Add a MariaDB (MySQL) backend to store call and text data.

* Add some frontend so I can interact with the MySQL-stored content, as well as send texts (SMS and MMS) and perhaps use other parts of the Twilio REST API.