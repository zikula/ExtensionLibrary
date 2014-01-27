Zikula Extension Manifest Specification
=======================================
(With sincere thanks and apologies to the jQuery crew, from whom this concept was basically reverse-engineered)

This document is all you need to know about what's required in your `zikula.manifest.json` file(s).

Manifest files must live in the root of your repository and exist in your tags. The files must be actual JSON, not just
a JavaScript object literal.

Fields
------

Required Fields
 - name
 - version
 - title
 - author
 - licenses
 - dependencies

Optional Fields
 - description
 - keywords
 - homepage
 - docs
 - demo
 - download
 - bugs
 - maintainers

name
----

The most important things in your manifest file are the name and version fields. The name and version together form an
identifier that is assumed to be completely unique. Changes to the extension should come along with changes to the version.

The name is what your thing is called. Some tips:

Don't put "zikula" in the name. It's assumed that it's zikula related, since you're writing a zikula.manifest.json file.
The name ends up being part of a URL. Any name with non-url-safe characters will be rejected. The Zikula Extension
Library Site is UTF-8. The name should be short, but also reasonably descriptive.
You may want to check the library site to see if there's something by that name already, before you get too attached to
it. If you have a extension with the same name as a extension already in the Zikula Extension Library Site, either
consider renaming your extension or namespacing it.

version
-------

The most important things in your manifest file are the name and version fields. The name and version together form an 
identifier that is assumed to be completely unique. Changes to the extension should come along with changes to the version. 
Version number must be a valid semantic version number per node-semver.

See Specifying Versions.

title
-----

A nice complete and pretty title of your extension. This will be used for the page title and top-level heading on your 
extension's page. Include spaces and mixed case as desired, unlike name.

author
------

One person.

See People Fields.

licenses
--------

Array of licenses under which the extension is provided. Each license is a hash with a url property linking to the actual 
text and an optional "type" property specifying the type of license. If the license is one of the official open source 
licenses, the official license name or its abbreviation may be explicated with the "type" property.

```json
"licenses": [
    {
        "type": "GPLv2",
        "url": "http://www.example.com/licenses/gpl.html"
    }
]
```

dependencies
------------

Dependencies are specified with a simple hash of package name to version range. The version range is EITHER a string 
which has one or more space-separated descriptors, OR a range like "fromVersion - toVersion".

If a extension that you depend on uses other extensions as dependencies that your extension uses as well, we recommend
you list those also. In the event that the depended on extension alters its dependencies, your extension's dependency
tree won't be affected.

description
-----------

Put a description in it. It's a string. This helps people discover your extension, as it's listed on the Zikula extensions Site.

keywords
--------

Put keywords in it. It's an array of strings. This helps people discover your extension as it's listed on the Zikula extensions 
Site. Keywords may only contain letters, numbers, hyphens, and dots.

homepage
--------

The url to the extension homepage.

docs
----

The url to the extension documentation.

demo
----

The url to the extension demo or demos.

download
--------

The url to download the extension. A download URL will be automatically generated based on the tag in GitHub, but you can 
specify a custom URL if you'd prefer to send users to your own site.

bugs
----

The url to the bug tracker for the extension.

maintainers
-----------

An array of people.

See People Fields.


People Fields
-------------
A "person" is an object with a "name" field and optionally "url" and "email", like this:

```json
{
    "name" : "Barney Rubble",
        "email" : "b@rubble.com",
        "url" : "http://barnyrubble.tumblr.com/"
}
```

Both the email and url are optional.

Specifying Versions
-------------------

Version range descriptors may be any of the following styles, where "version" is a semver compatible version identifier.

 - `version` Must match `version` exactly
 - `=version` Same as just `version`
 - `>version` Must be greater than `version`
 - `>=version` etc
 - `<version`
 - `<=version`
 - `~version` See 'Tilde Version Ranges' below
 - `1.2.x` See 'X Version Ranges' below
 - `*` Matches any version
 - `version1 - version2` Same as `>=version1 <=version2`
 - `range1 || range2` Passes if either range1 or range2 are satisfied.
 
For example, these are all valid:
```json
{ "dependencies" :
    {
        "foo" : "1.0.0 - 2.9999.9999",
            "bar" : ">=1.0.2 <2.1.2",
            "baz" : ">1.0.2 <=2.3.4",
            "boo" : "2.0.1",
            "qux" : "<1.0.0 || >=2.3.1 <2.4.5 || >=2.5.2 <3.0.0",
            "asd" : "http://asdf.com/asdf.tar.gz",
            "til" : "~1.2",
            "elf" : "~1.2.3",
            "two" : "2.x",
            "thr" : "3.3.x"
    }
}
```

Tilde Version Ranges
--------------------

A range specifier starting with a tilde ~ character is matched against a version in the following fashion.

The version must be at least as high as the range.
The version must be less than the next major revision above the range.
For example, the following are equivalent:

 - `"~1.2.3" = ">=1.2.3 <1.3.0"`
 - `"~1.2" = ">=1.2.0 <1.3.0"`
 - `"~1" = ">=1.0.0 <2.0.0"`

X Version Ranges
----------------

An "x" in a version range specifies that the version number must start with the supplied digits, but any digit may be 
used in place of the x.

The following are equivalent:

 - `"1.2.x" = ">=1.2.0 <1.3.0"`
 - `"1.x.x" = ">=1.0.0 <2.0.0"`
 - `"1.2" = "1.2.x"`
 - `"1.x" = "1.x.x"`
 - `"1" = "1.x.x"`

You may not supply a comparator with a version containing an x. Any digits after the first "x" are ignored.

Sample manifest
---------------

```json
{
    "name": "color",
    "title": "Zikula Color",
    "description": "Zikula extension for color manipulation and animation support.",
    "keywords": [
        "color",
        "animation"
    ],
    "version": "2.1.2",
    "author": {
        "name": "Zikula Foundation and other contributors",
        "url": "https://github.com/Zikula/Zikula-color/blob/2.1.2/AUTHORS.txt"
    },
    "maintainers": [
        {
            "name": "Corey Frang",
            "email": "gnarf37@gmail.com",
            "url": "http://gnarf.net"
        }
    ],
    "licenses": [
        {
            "type": "MIT",
            "url": "https://github.com/Zikula/Zikula-color/blob/2.1.2/MIT-LICENSE.txt"
        }
    ],
    "bugs": "https://github.com/Zikula/Zikula-color/issues",
    "homepage": "https://github.com/Zikula/Zikula-color",
    "docs": "https://github.com/Zikula/Zikula-color",
    "download": "http://code.Zikula.com/#color",
    "dependencies": {
        "Zikula": ">=1.5"
    }
}
```