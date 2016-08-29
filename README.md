# T7Api Example

## Getting Started

### Download ZIP file

You may download example.zip from the following URL.

Since this file may be outdated, you should proceed with 'Install with Git and Composer'

https://git.dirtyherri.de/T7/T7Api-Example/wikis/home

Extract the file and ```cd``` into the destination folder.

Proceed with 'Finish Installation'

### Install with Git and Composer

If you have not yet installed Composer, you should do so now:
https://getcomposer.org/

The following section assumes, that Composer is installed globally:

* ```git clone https://git.dirtyherri.de/T7/T7Api-Example.git```
* ```cd T7Api-Example```
* ```composer install```

### Finish Installation

* ```cd public```
* ```php -S localhost:3333```

Copy ```config/custom.php.dist``` to ```config/custom.php```.

Open ```config/custom.php``` with your favourite editor and enter valid values for ```reqId``` and ```secretKey```.

> For questions regarding ```reqId``` and ```secretKey```, please contact your account manager at 777

Open localhost:3333 in your browser.

Profit!