# Introduction

This component provides authorization middleware for [PSR-7](https://www.php-fig.org/psr/psr-7/)
and [PSR-15](https://www.php-fig.org/psr/psr-15/) applications.

An authorization system first needs authentication: to verify that an identity
has access to something (i.e., is authorized) we first need the _identity_, which
is provided during authentication.

Authentication is provided via the package
[mezzio-authentication](https://docs.mezzio.dev/mezzio-authentication/).
That library provides an `AuthenticationMiddleware` class that verify
credentials using the HTTP request, and stores the identity via a
[PSR-7 request attribute](https://docs.mezzio.dev/mezzio/v3/cookbook/passing-data-between-middleware/).

The identity generated by mezzio-authentication is stored as the
request attribute `Mezzio\Authentication\UserInterface` as a
`UserInterface` implementation. That interface looks like the following:

```php
namespace Mezzio\Authentication;

interface UserInterface
{
    /**
     * Get the unique user identity (id, username, email address or ...)
     */
    public function getIdentity() : string;

    /**
     * Get all user roles
     *
     * @return Iterable
     */
    public function getRoles() : iterable;

    /**
     * Get a detail $name if present, $default otherwise
     */
    public function getDetail(string $name, $default = null);

    /**
     * Get all the details, if any
     */
    public function getDetails() : array;
}
```

mezzio-authorization consumes this identity attribute.  It checks if a
user's role (as retrieved from the `UserInterface` object) is authorized
(granted) to the perform the current HTTP request.

Authorization is performed using the `isGranted()` method of the
[AuthorizationInterface](https://github.com/mezzio/mezzio-authorization/blob/master/src/AuthorizationInterface.php).

We offer two adapters:

- [mezzio-authorization-rbac](https://docs.mezzio.dev/mezzio-authorization-rbac/),
  which implements Role-Based Access Controls ([RBAC](https://en.wikipedia.org/wiki/Role-based_access_control))
- [mezzio-authorization-acl](https://docs.mezzio.dev/mezzio-authorization-acl/),
  which implements an Access Control List ([ACL](https://en.wikipedia.org/wiki/Access_control_list)).

> If you want to know more about authentication using middleware in PHP,
> we suggest reading the blog post ["Authorize users using Middleware"](https://getlaminas.org/blog/2017-05-04-authorization-middleware.html).
