Jason Roman's Utility Bundle
==============

This is my utility bundle.  It can be used as a standalone set of classes installed with composer, or it can be used with Symfony2 for service calls.  The bundle contains the following components:# Flot Charting Class

This is a class that transforms PHP arrays of series data into a JSON format that Flot can understand.  It supports line/bar charts, pie charts, horizontal/vertical orientation, and time series data.  It also supports single or multiple series.

See the comments in the class for examples of the various forms of array $data that can be passed to the convert() function.
# Twig Extensions via Filters

This is a class that contains Twig filters.  See the comments in the class for example usage.  There are 5 filters:

* *phone* - displays a phone number in a specified format
* *price* - essentially a PHP number_format() clone that adds '$' to the front
* *boolean* - returns 'Yes'/'No' (or custom text) based on the boolean value of the variable
* *md5* - displays the md5 hash of the passed-in value
* *timeAgo* - converts a time to time 'ago', such as 5 days ago, 27 seconds ago, 2 years ago
