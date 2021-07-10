# Navionics Export Cleaner

Navionics' Boating app by default creates very large GPX files unsuitable for import to Google My Maps.

This simple script takes a succession of GPX files and creates a simplified XML with one path per GPX file.

## Usage

* Clone the repository
* Install dependencies with `composer`
* Copy source GPX files to `gpx` directory
* Run `public/index.php` with a server or CLI.
* The resulting KML is a direct output from the script
