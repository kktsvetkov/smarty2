# Smarty 2 - the PHP compiling template engine

The **Smarty 2** library is still used, even 20 years after its initial
inception. However, it is abandoned, as the focus has shifted to the (not-so)
newer version 3, as well as other alternatives.

This project is an effort to to clean it up and make some improvements. The goal
is to keep all of the class methods and their arguments the same, but still
improve the underlying code and how it works. A lot of things has changed from
2001, and PHP as a whole has evolved a lot. This needs to be reflected in this
project, so mainly the changes involve removing outdated features and other
things we do not use, but also improving some of the codebase and bring it
closely to the state of PHP nowadays (in the 2020s).

This fork is started from [**Smarty 2.6.31**](https://github.com/smarty-php/smarty/tree/v2.6.31).

## Installation

To get the latest stable version add this in your `composer.json` file:
```
	"require": {
	   "kktsvetkov/smarty2": "~2.7"
	}
```
or you can just use the composer tool:
```
php composer.phar require kktsvetkov/smarty2
```

#### Folders

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

#### Namespace

The project was converted to use a PSR-4 loaded namespace called `Smarty2`.
The legacy class names of `Smarty` and `Smarty_Compiler` are still available
as aliased to their descendants at `Smarty2\Engine` and `Smarty2\Compiler`.

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

## Config Vars

The config file reading is removed. In order to introduce config_vars use
the `Smarty::set_config_vars()` method instead.

## Caching

The built-in caching of the Smarty 2 project is removed from this fork. The
methods used in caching are still present, but they are considered deprecated
and do nothing.

Consider other options for caching for your web app, not within the
presentation layer that Smarty provides.

## Internals (from libs/internals)

The `libs/internals` folder is removed from the project. All of the core
internals are moved as methods to the `Smarty_Core` class, which eventually
became the `Smarty2\Core` class.

## Plugins (from libs/plugins)

As the project was converted to using PSR-4 loaded namespace, the `libs/` folder
was dropped. The core project files are moved to `src/`, and the `plugins/`
folder is moved to the root. The `SMARTY_DIR` used to load the plugins is adjusted
accordingly to point to the correct folder.

## PHP_HANDLING

Any PHP code a la `<?php do_something(); ?>` inside the templates will always
be printed in the template in its quoted form. This is the behaviour that used
to be triggered by the `SMARTY_PHP_QUOTE` setting of `$smarty->php_handling`.
Now that's the only available option.

## {php} block tags

It goes without saying how bad of an idea is to use the `{php}` block tags.
Now all of those tags will be stripped when the template is compiled. The
`PHP_TAGS` security option is also removed as it is no longer needed.

## {include_php} tag

Again, including PHP scripts from templates is a bad ideas. Another option that
made this possible was the `{include_php}` tag. That is now removed from this
project. If you do need to use this tag, look into implementing your own custom
plugin functions for this.

## What is Smarty?

*(from original README)*

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
* arbitrary template sources (filesystem, databases, etc.)
* template if/elseif/else/endif constructs are passed to the PHP parser,
  so the if syntax can be as simple or as complex as you like.
* unlimited nesting of sections, conditionals, etc. allowed
* it is possible to embed PHP code right in your template files,
  although not recommended and doubtfully needed since the engine
  is so customizable.
* and many more.
