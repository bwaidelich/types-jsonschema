<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/string#built-in-formats
 */
enum StringFormat
{
    case date_time;
    case time;
    case date;
    case duration;
    case email;
    case idn_email;
    case hostname;
    case idn_hostname;
    case ipv4;
    case ipv6;
    case uuid;
    case uri;
    case uri_reference;
    case iri;
    case iri_reference;
    case uri_template;
    case json_pointer;
    case relative_json_pointer;
    case regex;
}
