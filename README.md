Doctrine 1
==========

[![Build Status](https://travis-ci.com/ovos/doctrine1.svg?branch=master)](https://travis-ci.com/ovos/doctrine1)

This is a fork of [doctrine/doctrine1](https://github.com/doctrine/doctrine1), adjusted for compatibility with php 5.3-7.3
and mysql 5.7. Tested only with mysql.

It will be maintained as long as we are using it.
Feel free to submit your [Issues](https://github.com/ovos/doctrine1/issues) and [Pull Requests](https://github.com/ovos/doctrine1/pulls).

There are also some performance tweaks and features added, i.a.:
- **[BC BREAK]** modified doctrine collection & record serialization - store less data in cache, but losing the feature of keeping state of modified data
- **[BC BREAK]** fixed orderBy handling in relations - for ordering m2m relations by columns in `refClass` use `refOrderBy`!
- refactored link/unlink methods in Doctrine_Record - now they do not load whole relations before linking/unlinking
- added `postRelatedSave` hook in Record to be called on save, after all relations are also saved (original postSave method is called before any relation is saved)
- Added `Doctrine_Query_Abstract::getDqlWithParams` - returns the DQL query that is represented by this query object, with interpolated param values, and modified Doctrine_Connection to use PDO::quote for quoting string whenever possible
- queryCache reworked:
  - hook it in getSqlQuery method instead of execute method only (better cache usage)
  - added rootAlias, sqlParts (without offset or limit), isLimitSubqueryUsed and limitSubquery to cache
  - always prequery the query in getSqlQuery to call dql callbacks before any sql is generated, not only on execute(), so that cache hash is always calculated properly, and that this method always returns actual end-query incl. any modifications from dql callbacks
  - added `isQueryCacheEnabled()` method
  - cache queries without limit and offset (to save less cache records) - added `Doctrine_Core::ATTR_QUERY_CACHE_NO_OFFSET_LIMIT` - set to true to enable the feature
  - added parent query components for subqueries, to indicate subquery context - changing cache hash
  - WHERE IN adjustments for better caching and performance
- Limit subquery adjustments for mysql 5.7 and performance

These are only highlights, [full changelog here](CHANGELOG.md).


### Installation: 
```
composer require ovos/doctrine1
```
