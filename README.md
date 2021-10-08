# Smarty 2 - the PHP compiling template engine

This is a fork from [**Smarty 2.6.31**](https://github.com/smarty-php/smarty/tree/v2.6.31).
I am involved in a project that is heavily integrated with this version of the
library and I wanted to clean it up and make some changes to it. Mainly the changes
involve removing outdated features and other things we do not use, but also
improving some of the codebase and bring it closely to the state of PHP in the 2020s.

## Installation

To get the latest stable version add this in your `composer.json` file:
```
	"require": {
	   "kktsvetkov/smarty2": "~2.6"
	}
```
or you can just use the composer tool:
```
php composer.phar require kktsvetkov/smarty2
```

Additionally you will need to create few folders. Using the default
settings, those folders are "templates" (to read the templates from),
and "templates_c" (to write the compiled templates into). If you want
to use different folders, be sure to set the appropriate directory
settings in Smarty for them.

Make sure the "templates_c" folder (or whatever other folder you used for
compiled templates) is writable by your web server user (usually nobody):
```
chown nobody:nobody templates_c;
chmod 700 templates_c
```

## Debugging

The debugging console is removed. The debugging stats are collected in
the `Smarty::$_smarty_debug_info` array, and you can inspect and render
them in whatever way is best for you -- for examples something like this:

```php
...
$smarty->display('index.tpl');
print_r($smarty->_smarty_debug_info);
```
and the output will be something similar to this:
```
Array
(
    [0] => Array
        (
            [type] => template
            [filename] => index.tpl
            [depth] => 0
            [exec_time] => 0.0049879550933838
        )

    [1] => Array
        (
            [type] => template
            [filename] => header.tpl
            [depth] => 1
            [exec_time] => 7.2002410888672E-5
        )

    [2] => Array
        (
            [type] => template
            [filename] => footer.tpl
            [depth] => 1
            [exec_time] => 6.103515625E-5
        )

)
```

## From Original README

**What is Smarty?**

Smarty is a template engine for PHP. Many other template engines for PHP
provide basic variable substitution and dynamic block functionality.
Smarty takes a step further to be a "smart" template engine, adding
features such as configuration files, template functions, and variable
modifiers, and making all of this functionality as easy as possible to
use for both programmers and template designers. Smarty also converts
the templates into PHP scripts, eliminating the need to parse the
templates on every invocation. This makes Smarty extremely scalable and
manageable for large application needs.

Some of Smarty's features:

* it is extremely fast
* no template parsing overhead, only compiles once.
    * it is smart about recompiling only the template files that have
      changed.
* the template language is remarkably extensible via the plugin
  architecture.
* configurable template delimiter tag syntax, so you can use
  `{}`, `{{}}`, `<!--{}-->`, or whatever you like.
* built-in caching of template output.
* arbitrary template sources (filesystem, databases, etc.)
* template if/elseif/else/endif constructs are passed to the PHP parser,
  so the if syntax can be as simple or as complex as you like.
* unlimited nesting of sections, conditionals, etc. allowed
* it is possible to embed PHP code right in your template files,
  although not recommended and doubtfully needed since the engine
  is so customizable.
* and many more.
