[![Smarty 2](https://repository-images.githubusercontent.com/414840734/351f0ce4-ef36-4b8c-a8b4-80ef843d58d8)](https://github.com/kktsvetkov/smarty2)

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
as aliased to their descendants at `Smarty2\Legacy` and `Smarty2\Compiler`.

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
* and many more.

## Resources

Template contents are read from **resource** objects. You can create new
resources using `Smarty2\Resource\ResourceInterface`, or you can re-use some of
the existing resources, such as:

- `FolderResource` reads templates from a folder
- `CustomResource` is wrapper for the custom resource code from `register_resource()` method
- `PluginResource` is a wrapper for plugin resources, e.g. `plugins/resource.file.php`

Loaded resources are hosted in a `Smarty2\Resource\Aggregate` object, and this
is the object you must use to register new resource object:
```php
$smarty->getResourceAggregate()->register('admin',
	new Smarty2\Resource\FolderResource('admin/templates')
	);
```

At the same time, the old `register_resource()` and `unregister_resource()` are
preserved, and the custom callbacks used in them for the resources are used
through the `CustomResource` class.

## Security

The old security settings at `$smarty->security` and `$smarty->security_settings`
are preserved in `Smarty2\Legacy` engine. Under the hood these settings are
imported into the new `Smarty2\Security\Policy` classes. If you are using the
new engine class, `Smarty2\Engine`, then in order to enforce a security policy
you must set it explicitly like this:

```php
$smarty = new Smarty2\Engine;
$smarty->setSecurityPolicy(
	new Smarty2\Security\Policy(
		Smarty2\Security\Policy::DEFAULT_IF_FUNCS,
		Smarty2\Security\Policy::DEFAULT_MODIFIER_FUNCS,
		$allowConstants = false,
		$allowSuperGlobals = true		
	)
);
```

## Dropped or Deprecated Features

One of the goals for this project is to cut down any dated or legacy features,
as well as exotic and questionable features that have outlived their purpose.

#### Debugging

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

#### Config Vars

The config file reading is removed. In order to introduce config_vars use
the `Smarty::set_config_vars()` method instead.

#### Caching

The built-in caching of the Smarty 2 project is removed from this fork. The
methods used in caching are still present, but they are considered deprecated
and do nothing.

Consider other options for caching for your web app, not within the
presentation layer that Smarty provides.

#### Core Internals (from libs/internals)

The `libs/internals` folder is removed from the project. The some of the core
internals were moved to `Smarty2\Engine`, while other are dropped (as they
are not necessary anymore). This happened as the core internals code was
refactored, with some of the functionality delegated to other classes, and
some of the old unnecessary code was just dropped.  

#### Plugins (from libs/plugins)

As the project was converted to using PSR-4 loaded namespace, the `libs/` folder
was dropped. The main project files are moved to `src/`, and the `plugins/`
folder is moved to the root. The `SMARTY_DIR` used to load the plugins is adjusted
accordingly to point to the correct folder.

#### PHP_HANDLING

Any PHP code a la `<?php do_something(); ?>` inside the templates will always
be printed in the template in its quoted form. This is the behaviour that used
to be triggered by the `SMARTY_PHP_QUOTE` setting of `$smarty->php_handling`.
Now that's the only available option.

#### {php} block tags

It goes without saying how bad of an idea is to use the `{php}` block tags.
Now all of those tags will be stripped when the template is compiled. The
`PHP_TAGS` security option is also removed as it is no longer needed.

#### {include_php} tag

Again, including PHP scripts from templates is a bad ideas. Another option that
made this possible was the `{include_php}` tag. That is now removed from this
project. If you do need to use this tag, look into implementing your own custom
plugin functions for this.

#### Dropped Plugins

These plugins have been removed from this project. If you need them for your
work you can either get them from the original source code at [**Smarty 2.6.31**](https://github.com/smarty-php/smarty/tree/v2.6.31) and add them as custom plugins, or you can modify your
code around them.

* `{mailto}` -- 2002 called and asked for it back  
* `{fetch}` -- it is a bad idea to fetch anything during rendering
* `{html_image}` -- there are better ways to do this
* `{config_load}` -- the whole config file reading feature is removed
* `{debug}` -- the debug console is removed
* `{popup}`, `{popup_init}` -- seriously ?

#### Removed Features

Few more things stripped form this project:

- the `append="..."` argument for `{capture}` block tags
- the `script="..."` argument for `{insert}` tags

#### Smarty2\\Legacy

All of the deprecated methods are kept in the `Smarty2\Legacy` class. They
are all empty and do nothing, but at least your code should be able to continue
your existing code without any real big changes.
