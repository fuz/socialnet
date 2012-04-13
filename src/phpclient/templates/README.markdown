# SocialNet Haml Templates

The SocialNet interface is composed from [HAML][] templates and parsed by [PHamlP][], a PHP library for processing HAML in PHP.

# Template Compilation

When a template is used, PHamlP processes a template file and the output is saved to a file. This is used rather than re-processing the template for every page load.

[HAML]: http://haml-lang.com/
[PhamlP]: https://code.google.com/p/phamlp/
