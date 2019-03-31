# Release notes

## 1.5.3

* The list of valid TLDs is now static for better performance.

## 1.5.2

* Added a missing use statement in HttpAuth.

## 1.5.1

* Removed the option from UrlEditor::checkUrl() to throw an exception, it just returns a boolean now.

## 1.5.0

* UrlEditor can now be constructed as an empty object, for performance reasons.
* Domains without TLDs are now supported, like https://localhost.
* Removed Tld::fromHost(), moved the logic to Tld::fromString().
* Slightly better performance when getting valid TLD list.
* A number of fixes.

## 1.4.1

* The port number is now updated when changing isSecure.

## 1.4.0

* Removed the Host class.
* All URL properties are now part of the main UrlEditor class (Subdomains and Tld used to be part of Host).
* Default (in)secure HTTP port numbers are now static properties of Port.

## 1.3.1

* Added 443 as an ignored port when outputting Port as string.
* Using default ports (80 or 443) when constructing Port.

## 1.3.0

* Added the HttpAuth class for adding a username and password to a URL.
* Added the InvalidHttpAuthException class.

## 1.2.0

* Added the Port class, to edit the port of a URL.
* Added the Intable interface.
* Added the InvalidPortException class.
* Small fixes and improvements.

## 1.1.0

* Added the Host, Subdomains and Tld classes.
* Added the Stringable, Arrayable and Jsonable interfaces.
* Added the HasArray and HasAssociativeArray traits.
* Added custom exceptions.
* Added some unit tests.
* Updated the readme file.
* Removed the test file, in favor of the new Wiki pages.
* PSR-2 compliance and other small fixes.

## 1.0.1

* Fixed: autoload section in composer.json.
* Added URL validation.

## 1.0.0

* Divided logic into separate classes.
* Added some methods to the new classes.
* Added a file for testing, which is also a simple tutorial.

## 0.1.0

* Initial release.
