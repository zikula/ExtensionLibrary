Zikula Extension Manifest Specification
=======================================
(With sincere thanks and apologies to the jQuery crew, from whom this concept was basically reverse-engineered)

This document is all you need to know about what's required in your `zikula.manifest.json` file(s).

See the sample zikula.manifest.json file in `/docs`

Manifest files must live in the root of your repository and exist in your tags. The files must be actual JSON, not just
a JavaScript object literal.

Fields
------

 - vendor
     - title
     - url
     - logo
     - owner
 - extension
     - title (required)
     - type (required)
     - url
     - icon
 - version
     - semver (required)
     - compatibility (required)
     - licenses (required)
     - description
     - keywords
     - urls
     - contributors
     - dependencies


# Vendor

title
-----

The display title of your vendor. This will be used for the page title and top-level heading on your vendor's page.
Include spaces and mixed case as desired.

url
---

The url to the homepage for your self, group or company. A Github page is suitable or any other page as you desire.

logo
----

The url to your vendor logo (not extension logo/icon). The image will be copied to our servers.

owner
-----

One person. See People Fields.


Extension
=========

title (required)
----------------

The display title of your extension. This will be used for the page title and top-level heading on your
extension's page. Include spaces and mixed case as desired.

type (required)
---------------

A single character only: "m", "t" or "p" for module, theme or plugin, respectively.

url
---

The url to the site for the extension. A Github page is suitable or any other page as you desire.

icon
----

The url to your extension icon (not vendor logo). The image will be copied to our servers.


Version
=======

semver (required)
-----------------

A valid version string as defined by SemVer 2.0.0 (http://semver.org). Changes to the extension should come along with
changes to the version. See Specifying Versions.

compatibility (required)
------------------------

A string defining Zikula Core version compatibility. See Specifying Versions.

licenses (required)
-------------------

Array of licenses under which the extension is provided. Each license is a hash with a url property linking to the actual 
text and a "type" property specifying the type of license. You must use the standardized identifier acronym for the
license as defined (here)[http://spdx.org/licenses/]

```json
"licenses": [
    {
        "type": "GPLv2",
        "url": "http://www.example.com/licenses/gpl.html"
    }
]
```

description
-----------

Put a description in it. It's a string. This helps people discover your extension, as it's listed in the
Zikula Extensions Library site.

keywords
--------

Put keywords in it. It's an array of strings. This helps people discover your extension as it's listed on the
Zikula Extensions Library site. Keywords may only contain letters, numbers, hyphens, and dots.

urls
----

 - version
     - The url to the specific version site.
 - docs
     - The url to the version documentation.
 - demo
     - The url to the version demo.
 - download
     - The url to download the extension. A download URL will be automatically generated based on the tag in GitHub,
       but you can specify a custom URL if you'd prefer to send users to your own site.
 - issues
     - The url to the issue tracker for the version.

contributors
------------

An array of people. See People Fields.

dependencies
------------

Dependencies are specified with a simple hash of name, type and version. The name should be the repository name and not
the title. The type must be as defined in extension type above. Version uses the same definition as core compatibility.
See Specifying Versions.

If a extension that you depend on uses other extensions as dependencies that your extension uses as well, we recommend
you list those also. In the event that the depended on extension alters its dependencies, your extension's dependency
tree won't be affected.


Additional Details
==================

People Fields
-------------
A "person" is an object with a "name" field and optional "url" and "email", like this:

```json
{
    "name" : "Susan Miller",
    "email" : "smiller@acme.com",
    "url" : "http://www.acme.com/smiller"
}
```

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