GENERAL
-------

Q: What is Smarty?
A: Smarty is a template engine for PHP... but be aware this isn't just another
   PHP template engine. It's much more than that.

Q: What's the difference between Smarty and other template engines?
A: Most other template engines for PHP provide basic variable substitution and
   dynamic block functionality. Smarty takes a step further to be a "smart"
   template engine, adding features such as configuration files, template
   functions, variable modifiers (see the docs!) and making all of this
   functionality as easy as possible to use for both programmers and template
   designers. Smarty also compiles the templates into PHP scripts, eliminating
   the need to parse the templates on every invocation, making Smarty extremely
   scalable and manageable for large application needs.

Q: What do you mean "Compiled PHP Scripts" ?
A: Smarty reads the template files and creates PHP scripts from them. Once
   these PHP scripts are created, Smarty executes these, never having to parse
   the template files again. If you change a template file, Smarty will
   recreate the PHP script for it. All this is done automatically by Smarty.
   Template designers never need to mess with the generated PHP scripts or even
   know of their existance. (NOTE: you can turn off this compile checking step
   in Smarty for increased performance.)

Q: Why can't I just use PHPA (http://php-accelerator.co.uk) or Zend Cache?
A: You certainly can, and we highly recommend it! What PHPA does is caches
   compiled bytecode of your PHP scripts in shared memory or in a file. This
   speeds up server response and saves the compilation step. Smarty creates PHP
   scripts, which PHPA will cache nicely. Now, Smarty's built-in cache is
   something completely different. It caches the _output_ of the template
   contents. For example, if you have a template that requires several database
   queries, Smarty can cache this output, saving the need to call the database
   every time. Smarty and PHPA (or Zend Cache) complement each other nicely. If
   performance is of the utmost importance, we would recommend using one of
   these with any PHP application, using Smarty or not. As you can see in the
   benchmarks, Smartys performance _really_ excels in combination with a PHP
   accelerator.

TROUBLESHOOTING
---------------

Q: I get the following error when running Smarty:
   Warning:  Smarty error: problem creating directory "templates_c/239/239105369"
   in /path/to/Smarty.class.php on line 542
A: Your web server user does not have permission to write to the templates_c
   directory, or is unable to create the templates_c directory. Be sure the
   templates_c directory exists in the location defined in Smarty.class.php,
   and the web server user can write to it. If you do not know the web server
   user, chmod 777 the templates_c directory, reload the page, then check the
   file ownership of the files created in templates_c. Or, you can check the
   httpd.conf (usually in /usr/local/apache/conf) file for this setting:
   User nobody
   Group nobody

Q: I get PHP errors in my {if} tag logic.
A: All conditional qualifiers must be separated by spaces. This syntax will not
   work: {if $name=="Wilma"} You must instead do this: {if $name == "Wilma"}.
   The reason for this is syntax ambiguity. Both "==" and "eq" are equivalent
   in the template parser, so something like {if $nameeq"Wilma"} wouldn't be
   parsable by the tokenizer.

Q: I'm changing my php code and/or templates, and my results are not getting
   updated.
A: This may be the result of your compile or cache settings. If you are
   changing your php code, your templates will not necessarily get recompiled
   to reflect the changes. Use $force_compile during develpment to avoid these
   situations. You can also remove everything from your
   compile_dir and reload the page to be sure everything gets
   regenerated.

Q: Javascript is causing Smarty errors in my templates.
A: Surround your javascript with {literal}{/literal} tags. See the docs.

HOWTO
-----
Q: How do I pass a template variable as a parameter? {function param={$varname}}
   does not work.
A: {function param=$varname} (You cannot nest template delimiters.)
