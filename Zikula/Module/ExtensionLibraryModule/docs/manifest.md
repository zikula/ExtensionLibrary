Zikula Extension Manifest Specification
=======================================
(With sincere thanks and apologies to the jQuery crew, from whom this concept was basically reverse-engineered)

This document is all you need to know about what's required in your `zikula.manifest.json` file(s).

[View the sample](el/doc/sample) `zikula.manifest.json` file

Manifest files must live in the root of your repository and exist in your tags. The files must be actual JSON, not just
a JavaScript object literal.

Fields
------

 - [vendor](#vendor)
     - [title](#vendor-title)
     - [url](#vendor-url)
     - [logo](#vendor-logo)
     - [owner](#vendor-owner)
 - [extension](#extension)
     - [title](#extension-title) (required)
     - [type](#extension-type) (required)
     - [url](#extension-url)
     - [icon](#extension-icon)
 - [version](#version)
     - [semver](#version-semver) (required)
     - [compatibility](#version-compatibility) (required)
     - [licenses](#version-licenses) (required)
     - [description](#version-description)
     - [keywords](#version-keywords)
     - [urls](#version-urls)
     - [contributors](#version-contributors)
     - [dependencies](#version-dependencies)


<a name="vendor"></a>Vendor
=======

<a name="vendor-title"></a>title
-----

The display title of your vendor. This will be used for the page title and top-level heading on your vendor's page.
Include spaces and mixed case as desired.

<a name="vendor-url"></a>url
---

The url to the homepage for your self, group or company. A Github page is suitable or any other page as you desire.

<a name="vendor-logo"></a>logo
----

The url to your vendor logo (different from extension logo/icon). The image will be copied to our servers.
Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`.

<a name="vendor-owner"></a>owner
-----

One person. See [People Fields](#people-fields).


<a name="extension"></a>Extension
=========

<a name="extension-title"></a>title (required)
----------------

The display title of your extension. This will be used for the page title and top-level heading on your
extension's page. Include spaces and mixed case as desired.

<a name="extension-type"></a>type (required)
---------------

A single character only: "m", "t" or "p" for module, theme or plugin, respectively.

<a name="extension-url"></a>url
---

The url to the site for the extension. A Github page is suitable or any other page as you desire.

<a name="extension-icon"></a>icon
----

The url to your extension icon (different from your vendor logo). The image will be copied to our servers.
Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`.


<a name="version"></a>Version
=======

<a name="version-semver"></a>semver (required)
-----------------

A valid version string as defined by [SemVer 2.0.0](http://semver.org). Changes to the extension should come along with
changes to the version. See [Specifying Versions](#versions).

<a name="version-compatibility"></a>compatibility (required)
------------------------

A string defining Zikula Core version compatibility. See [Specifying Versions](#versions).

<a name="version-licenses"></a>licenses (required)
-------------------

Array of licenses under which the extension is provided. Each license is a hash with a url property linking to the actual 
text and a "type" property specifying the type of license. You must use the standardized identifier acronym for the
license as defined by [Software Package Data Exchange](http://spdx.org/licenses/)

<pre>
"licenses": [
    {
        "type": "GPLv2",
        "url": "http://www.example.com/licenses/gpl.html"
    }
]
</pre>

<a name="version-description"></a>description
-----------

Put a description in it. It's a string. This helps people discover your extension, as it's listed in the
Zikula Extensions Library site.

<a name="version-keywords"></a>keywords
--------

Put keywords in it. It's an array of strings. This helps people discover your extension as it's listed on the
Zikula Extensions Library site. Keywords may only contain letters, numbers, hyphens, and dots.

<a name="version-urls"></a>urls
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

<a name="version-contributors"></a>contributors
------------

An array of people. See [People Fields](#people-fields).

<a name="version-dependencies"></a>dependencies
------------

Dependencies are specified with a simple hash of name, type and version. The name should be the repository name and not
the title. The type must be as defined in extension type above. Version uses the same definition as core compatibility.
See [Specifying Versions](#versions).

If a extension that you depend on uses other extensions as dependencies that your extension uses as well, we recommend
you list those also. In the event that the depended on extension alters its dependencies, your extension's dependency
tree won't be affected.


Additional Details
==================

<a name="people-fields"></a>People Fields
-------------
A "person" is an object with a "name" field and optional "url" and "email", like this:

<pre>
{
    "name" : "Susan Miller",
    "email" : "smiller@acme.com",
    "url" : "http://www.acme.com/smiller"
}
</pre>

<a name="versions"></a>Specifying Versions
-------------------

Version range descriptors may be any of the following styles, where "version" is a semver compatible version identifier.

 - `version` Must match `version` exactly
 - `=version` Same as just `version`
 - `>version` Must be greater than `version`
 - `>=version` etc
 - `<version`
 - `<=version`
 - `~version` See '[Tilde Version Ranges](#tilde)' below
 - `1.2.x` See '[X Version Ranges](#x-version)' below
 - `*` Matches any version
 - `version1 - version2` Same as `>=version1 <=version2`
 - `range1 || range2` Passes if either range1 or range2 are satisfied.
 
For example, these are all valid:
<pre>
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
</pre>

<a name="tilde"></a>Tilde Version Ranges
--------------------

A range specifier starting with a tilde ~ character is matched against a version in the following fashion.

The version must be at least as high as the range.
The version must be less than the next major revision above the range.
For example, the following are equivalent:

 - `"~1.2.3" = ">=1.2.3 <1.3.0"`
 - `"~1.2" = ">=1.2.0 <1.3.0"`
 - `"~1" = ">=1.0.0 <2.0.0"`

<a name="x-version"></a>X Version Ranges
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