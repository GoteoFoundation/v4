# Introduction

The v4 API is a multi-capable web API based on the [API Platform](https://api-platform.com/) framework, built to support the future generation of the [Goteo](https://goteo.org) crowdfunding platform, aiming to be it's new underlying engine.

v4 exposes a REST API with predictable and resource-oriented URLs, accepts request bodies (payloads) encoded in multiple open standard formats such as JSON, including [JSON-LD](https://json-ld.org/) and [Hydra](https://www.hydra-cg.com/), returns responses encoded in the same formats, and uses standard HTTP verbs and status codes.

This API is documented in the OpenApi initiative [Specification v3.1.0](https://spec.openapis.org/oas/v3.1.0), which allows v4 to be easily understood by API development suites, documentation generators and SDK generators for the convenience of developers and API users. Most of the spec is generated automatically as changes are introduced, but some additional content such as this introduction and other sections might not be updated as regularly.

<span class="hl-yellow">
This API is still in early development and is not set to have backward compatibility with the Goteo API v1 (http://api.goteo.org/v1/). Major changes are to be expected.
</span>

# Authentication

The v4 API uses Access Tokens to authenticate requests. To use a Token, include it in the `Authorization` header using the [Bearer](https://swagger.io/docs/specification/authentication/bearer-authentication/) strategy.

```sh
curl -X 'GET' \
  'https://api.goteo.org/v4/projects?page=1' \
  -H 'accept: application/json' \
  -H 'Authorization: Bearer <THE-TOKEN-HERE>'
```

UserTokens exist under an User's scope, when you obtain a token this will only grant you as much permissions as the User under which it was created.

To obtain a UserToken you must send a POST request to [/v4/user_tokens](/v4/user_tokens) with the User login credentials (username and password) in the payload. If the credentials are correct a UserToken will be created, the `token` property value of which you must include in future requests.

Users can delete UserTokens owned by them at any moment, revoking your application's access to the v4 API on their behalf. When a UserToken fails to authenticate a request you will receive a 401 Unauthorized response from the API, at which point your application must look to get a new UserToken from the User.

# Localization

The v4 API accepts localization of content. Resources such as Projects can have owner-submitted data (title, description, etc) in different languages, the extent of which is subject to the owner. Resources with localized content versions will expose a `locales` property listing the available localizations.

Retrieval of content in different locales is performed via standard HTTP [content negotiation](https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation). When the request supplies an `Accept-Language` header, the API will retrieve localized versions of the content where available, falling back to the default locale of the API instance, and finally to the first available localization of the content regardless of the request preferences or the API defaults if it cannot find suitable localizations.

Submission of localized content is performed over standard HTTP content submission with the addition of a `Content-Language` header to a POST, PUT or PATCH request.
