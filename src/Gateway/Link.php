<?php

namespace App\Gateway;

class Link
{
    /**
     * The complete target URL.
     */
    public string $href;

    /**
     * The link relation type, which serves as an ID for a link that unambiguously describes the semantics of the link.
     *
     * @see https://www.iana.org/assignments/link-relations/link-relations.xhtml
     */
    public string $rel;

    /**
     * The HTTP method required to make the related call.
     */
    public string $method;

    /**
     * The type of the link indicates who is the intended user of a link.\
     * `debug` links are for developers and platform maintainers to get useful information about the checkout.\
     * `payment` links are for end-users who must visit this link to complete the checkout.
     */
    public LinkType $type;
}
