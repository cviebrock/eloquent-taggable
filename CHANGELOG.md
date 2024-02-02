# Changelog

## 10.0.3 - 02-Feb-2024

- fix for deprecated `preg_split` parameter value (#144, thanks @XternalSoft)


## 10.0.2 - 11-Jan-2024

- more fixes for morph relations (#142, thanks @anaxamaxan)


## 10.0.1 - 06-Jan-2024

- fixes for morph relations (#140, thanks @anaxamaxan)


## 10.0.0 - 17-Feb-2023

- Laravel 10.0 support
- switch to using Pest instead of PHPUnit for tests


## 9.0.1 - 17-Feb-2023

- fixes for `taggedModels` not working


## 9.0.0 - 27-Jan-2022

- Laravel 9.0 support


## 8.0.3 - 30-Mar-2021

- fixes for Postgres (#123 and #124, thanks @0528Makoto)


## 8.0.2 - 04-Jan-2021

- add `tagById()`, `untagById()`, and `retagById()` methods (suggested by #110)
- fix for handling accented characters in normalized column (#116, thanks @pierrocknroll)
- update test internals to use static assertions
- update automated testing to use MySQL instead of sqlite (to catch issues like #116)


## 8.0.1 - 02-Dec-2020

- support PHP 8.0
- move automated testing from travis-ci to Github actions
- clean up some third-party tools and badges


## 8.0.0 - 10-Sep-2020

- Laravel 8.0 support


## 7.0.0 - 04-Mar-2020

- Laravel 7.0 support


## 6.0.2 - 19-Jan-2020

- several improvements from #99 & #104
  - make migrations publishable
  - use bigUnsignedIntegers for key columns
  - make table names customizable
  

## 6.0.1 - 13-Sep-2019

- fix for semantic versioning


## 6.0.0 - 03-Sep-2019

- Laravel 6.0 support (note the package version will now follow the Laravel version)


## 3.5.3 - 24-Apr-2019

- add ability to chain scopes (#97, @devguar)


## 3.5.2 - 08-Apr-2019

- add `ModelTagged` and `ModelUntagged` events (#95, @devguar)


## 3.5.1 - 16-Mar-2019

- add `hasTag()` method (#87, suggestion by @MordiSacks)
- add ability to combine scopes (#90, @devguar)
- clean up method return value declarations


## 3.5.0 - 28-Feb-2019

- Laravel 5.8 support
- fix `getAllTags()`, `getUnusedTags()` and `getPopularTags()` 
  when using a table prefix (#84, @tuxfamily)


## 3.4.1 - 04-Sep-2018

- de-tag models when they are deleted, to help keep the 
  polymorphic table under control (#78, @pierrocknroll)


## 3.4.0 - 04-Sep-2018

- Laravel 5.7 support


## 3.3.2 - 21-May-2018

- bump dependency versions


## 3.3.1 - 10-Feb-2018

- support custom Tag classes
- support custom polymorphic types via getMorphClass()


## 3.3.0 - 10-Feb-2018

- Laravel 5.6 support


## 3.2.5 - 01-Jan-2018

- fix when tagging with the same tag multiple times in one call
- prettier PHPUnit output


## 3.2.4 - 28-Dec-2017

- fix in migration connection


## 3.2.3 - 01-Nov-2017

- various bug fixes


## 3.2.2 - 06-Sep-2017

- fix SQL error when using popular tag methods


## 3.2.1 - 05-Sep-2017

- fix SQL error when preparing table join


## 3.2.0 - 31-Aug-2017

- Laravel 5.5 support


## 3.1.1 - 05-Sep-2017

- fix SQL error when using popular tag methods


## 3.1.0 - 31-Aug-2017

- fixed package requirements and constraints
- restricted package to Laravel 5.4


## 3.0.1 - 28-Aug-2017

- minor tweaks


## 3.0.0 - 18-Aug-2017

- refactor SQL queries to be more performant
- add new and rename scope queries:
    - `withAllTags`
    - `withAnyTags`
    - `isTagged`
    - `withoutAllTags`
    - `withoutAnyTags`
    - `isNotTagged`
- add and refactor static methods:
    - `allTagModels`
    - `allTags`
    - `allTagsList`
    - `renameTag`
    - `popularTags`
    - `popularTagsNormalized`
- new/improved service methods:
    - `getAllUnusedTags`
    - `getPopularTags`
    - `renameTags`
- better test coverage


## 2.1.3 - 23-Jun-2017

- support for Laravel 5.5 and auto-discovery


## 2.1.2 - 26-May-2017

- support a different database connection for the Tag model


## 2.1.1 - 27-Feb-2017

- fix for migrations running more than once


## 2.1.0 - 01-Feb-2017

- Laravel 5.4 updates/fixes


## 2.0.0 - 20-Apr-2016

- Rewrite and Laravel 5 release


## 2.0.0-beta - 04-Mar-2015

- First beta release for Laravel 5


## 1.0.0 - 17-Feb-2015

- Version 1.0.0 release for Laravel 4 (no significant changes from previous version)


## 0.9.1 - 16-May-2014

- Minor bug fixes


## 0.9.0 - 15-May-2014

- Initial release
