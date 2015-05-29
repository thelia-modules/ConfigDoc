# Config Doc

This module exports Thelia configuration variables name, title and description in json, yml, xml and php array formats.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ConfigDoc.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/config-doc-module:~1.1
```

## Usage

To use this module, open a terminal and run:

```sh
$ php Thelia thelia:config:export [-f|--format (yml,json,xml,array)] [-o|--output-file where_to_write_the_file.ext]
```
