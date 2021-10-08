# Smarty 2 - the PHP compiling template engine

## Installation

To get the latest stable version use
```
	"require": {
	   "kktsvetkov/smarty2": "~2.6"
	}
```
in your `composer.json` file.

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
