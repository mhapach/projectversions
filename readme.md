# ProjectVersions

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This package is a Laravel (6.0+) utility which helps you keep and manage your project version(increment major, minor version numbers  )

And also there is a UI for version checkout from VCS (only SVN for a while )

Attention if you use SVN make sure that you have *trunk* folder in svn structure   

## Installation

Step 1. Add Eloquent Model Generator to your project via Composer

``` bash
$ composer require mhapach/projectversions
```
Set in .env next values
- required fields
``` bash
# it's better to write svn path to root folder of project where are branches tags and trunk exists  
VCS_PATH=http://svn-server/your-project/  
VCS_TYPE=svn
```
- optional fields   
``` bash
#if you will not set it you will be propted to enter you account in Authorisation form for VCS in UI
VCS_LOGIN=Yourlogin
VCS_PASSWORD=YourPassword

#if it true you then access to UI will be through auth middleware, so only authorised users will able to update project 
VCS_USE_AUTH_MIDDLEWARE=true

#if VCS_USE_AUTH_MIDDLEWARE is true then in this field enter comma separated ids of users allowed to checkout and update project from VCS  
VCS_UPDATE_USERS=1,3
```

Step 2. Register ProjectVersionsServiceProvider in config/app.php
```  
'providers' => [
    //...
    \mhapach\ProjectVersions\ProjectVersionsServiceProvider::class,
]
```

## Usage
### Use project version in your code. It's very useful when you need prevent loading of js of css libs from cache
    Example 1 - getting current version number 
    print  app('project.version');
    
    Example 2 - usage within blade code  
    <link href="{{ asset('css/app.css') }}?v={{app("project.version")}}" rel="stylesheet">

      
   
### Easily increment your version numbers, using Artisan commands
    
    php artisan pv:commit
     
    Which should commit last changes into VCS and print the new version number in file 
    project.info in root folder of project
    
    Example of project.info file
    ---------------------------------------
    Project=Laravel
    Description=New build commit
    Date=2020-05-22 01:42:27
    Version=1.1.2.12345-Beta
    
    Examples of usage artisan commands (attention run all examples from root folder of project:
   
    Example 1 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit versionNumber (the same as: php artisan pv:commit versionNumber=+1)
    Result:  changes will be commited with descrition "New build commit. Version: 2.0.0.12346-Pre-Alfa"
             In svn trunk will be coppied to folder tags with name of new version 2.0.0.1-Pre-Alfa
    
    Example 2 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit versionNumber=3
    Result: changes will be commited with descrition "New build commit. 3.1.2.12346-Beta"
            In svn trunk will be coppied to folder tags with name of new version 3.1.2.12346-Beta
    
    Example 3 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit releaseNumber (the same as: php artisan pv:commit releaseNumber=+1)    
    Result: changes will be commited with descrition "New build commit. Version: 1.2.0.1-Pre-Alfa"
            In svn trunk will be coppied to folder tags with name of new version 1.2.0.1-Pre-Alfa
    
    Example 4 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit buildNumber (the same as: php artisan pv:commit buildNumber=+1)
    Result: changes will be commited with descrition "New build commit. Version: 1.1.3.1-Beta
    
    Example 5 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit releaseType (the same as: php artisan pv:commit releaseType=+1)
    Result: changes will be commited with descrition "New build commit. Version: 1.1.2.12346-Release-Candidate
    
    Example 6 (source version 1.1.2.12345-Beta)
    Command: php artisan pv:commit releaseType="Release"
    Result: changes will be commited with descrition "New build commit. Version: 1.1.2.12346-Release

    
### Rollback at previous project versions or checkout latest commited version.
 It very useful at dev servers where you testing your project and want to make regression testing.
 
    UI interface address 
    http://yourproject/project_versions  

### Find out if newest version exists by ajax request - get the json response and use it your javascript 
 
    http://yourproject/project_versions/new  

### Checkout latest VCS version  - get the json response and use it your javascript, this command will update tour project till last version 
 
    http://yourproject/project_versions/checkout/0 

    or just
 
    http://yourproject/project_versions/update 
    
    
## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mhapach/projectversions.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mhapach/projectversions.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mhapach/projectversions/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/mhapach/projectversions
[link-downloads]: https://packagist.org/packages/mhapach/projectversions
[link-travis]: https://travis-ci.org/mhapach/projectversions
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/mhapach
[link-contributors]: ../../contributors
