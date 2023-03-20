# Accounting app

A simple app, which fetches data from https://riigipühad.ee, checks if the payment date is a working day and provides reminders for accountants, all in the form of a CSV file.

## Installation

Download the project, open a new terminal in the root directory and run: `php palgad.php {year}`, where the {year} should be replaced with the desired year. 

If PHP is not installed on you machine, refer here: https://www.php.net/manual/en/install.php

## Features:

* Written in PHP
* Accepts inputs as CLI arguments.
* Data is fetched online, which ensures future compatability.
* Inputs are validated along with dataset connection and appropriate error messages are provided if something goes wrong.
* When the reminder date falls on a non-working day, the app will look for previous dates until it finds a working day for the reminder.
* Dataset dates are converted to the Estonian date format in the final CSV file.
* BOM ensures the correct display of Estonian characters in Excel.
