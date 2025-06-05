# moodle-composer-base
A project that aims on making it easy to install Moodle using composer.

Do NOT modify this package. If you want to use it, create and go to a **new_directory**:

create a file: 

*composer.json*

```
{
    "name": "your/projectname",
    "description": "Cool Moodle!.",
    "version": "1.0",
    "authors": [
        {
            "name": "Your Name",
            "email": "Your@Mail.YOU"
        }],
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bytesparrow/moodle-composer-base.git"
        }

    ],

    "require": {
        "bytesparrow/moodle-composer-base": "v1.*",
        "wikimedia/composer-merge-plugin": "^2.0"
    },
    "minimum-stability": "alpha",
    "config": {
        "sort-packages": true,
        "platform": {
            "php": "8.2"
        },

        "allow-plugins": {
            "composer/installers": true,
            "wikimedia/composer-merge-plugin": true,
            "bytesparrow/moodle-composer-base": true
        }
    },
    "extra": { 
        "merge-plugin": { 
            "require": [
                "vendor/bytesparrow/moodle-composer-base/composer.json"
            ],
            "recurse": true,
            "replace": true,
            "ignore-duplicates": false,
            "merge-dev": true,
            "merge-extra": true,
            "merge-extra-deep": true,
            "merge-scripts": true
        },
        "installer-paths": {}
    }
}
```

Type:

```
~/new_directory$composer install
```

If asked, type "yes"


Moodle will now be installed to the directory **new_directory/vendor/moodle/moodle/**

Using 

```
composer require
```

you can now install other Moodle-modules. See this doc: https://github.com/michaelmeneses/moodle-composer 


# Local modifications
Normally, composer update would clean the moodle-folder - including all your custom modifications and the settings.php

This composer-plugin copies the settings.php to a backup-folder and after updating it copies it back to Moodle.

Have any more custom files?
In the "extra" section add a line:
```
"keepfiles": ["your/file.txt"]
```


# Similar projects
moodle-composer https://github.com/michaelmeneses/moodle-composer is a similiar project. 
In fact I've adapted some features from it.

## Differences:
Whilst moodle-composer uses hardcoded pathes in code to move moodle-plugins to a specific directory, this plugin uses the installer-paths feature from composer/installers
In addition: with every Moodle-Upgrade I had problems with custom files being deleted from Moodle - this is composer specific.
This is what the "keepfiles"-directive is for to avoid some headaches.

## Unpacked in a wrong directory? 
Maybe sth is wrong. You can compare the definition in this composer.json with the data in https://github.com/composer/installers/blob/main/src/Composer/Installers/MoodleInstaller.php

> [!WARNING]
> This project is in a very early state. Use at your own risk. When it's production-ready it will be published as a real composer-package.
