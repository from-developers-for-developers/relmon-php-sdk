# RelMon PHP SDK

Official PHP SDK for [RelMon protocol](https://github.com/from-developers-for-developers/relmon-protocol).

![Tests](https://github.com/from-developers-for-developers/relmon-php-sdk/actions/workflows/tests.yml/badge.svg)
[![codecov](https://codecov.io/gh/from-developers-for-developers/relmon-php-sdk/branch/main/graph/badge.svg)](https://codecov.io/gh/from-developers-for-developers/relmon-php-sdk)

## Requirements

- PHP >= 8.0
- `xml`, `mbstring` extensions

## Installation

```bash
composer require from-developers-for-developers/relmon-php-sdk
```

## Usage

### Facade

Use `RelMonFacade` when you want the SDK to wire the default parser and service stack.

```php
use FromDevelopersForDevelopers\RelMon\RelMonFacade;

$relmon = RelMonFacade::build([
    'protocol' => 'relmon@1.0.0/3',
    'net' => '100.00',
    'gross' => '121.00',
    'tax' => '21.00',
    'taxRate' => '21.00',
    'unit' => 'EUR',
    'precision' => 2,
]);

echo $relmon->getGross();          // 12100
echo $relmon->getGrossFormatted(); // 121.00
```

The second argument can be used to force a parser instead of auto-detection:

```php
use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\RelMonFacade;

$relmon = RelMonFacade::build($jsonString, Format::JSON_STRING);
```

### Services

Use `RelMonService` directly when your application manages dependencies itself.

```php
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserLocator;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonArrayParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonStringParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriJsonParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriMinimalisticParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlDomDocumentParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlSimpleXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlStringParser;
use FromDevelopersForDevelopers\RelMon\Service\DerivationService;
use FromDevelopersForDevelopers\RelMon\Service\MinorsService;
use FromDevelopersForDevelopers\RelMon\Service\RelMonService;
use FromDevelopersForDevelopers\RelMon\Service\ValidationService;

$parserLocator = new FormatParserLocator([
    new JsonArrayParser(),
    new JsonStringParser(),
    new XmlSimpleXmlParser(),
    new XmlDomDocumentParser(),
    new XmlStringParser(),
    new UriJsonParser(),
    new UriXmlParser(),
    new UriMinimalisticParser(),
]);

$service = new RelMonService(
    new FormatParserFactory($parserLocator),
    new ValidationService(),
    new MinorsService(),
    new DerivationService(),
);

$relmon = $service->build($input);
```

## Supported Formats

`Format::AUTO` detects:

- PHP array: `Format::JSON_ARRAY`
- JSON string: `Format::JSON_STRING`
- XML string: `Format::XML_STRING`
- `SimpleXMLElement`: `Format::XML_SIMPLE_XML`
- `DOMDocument`: `Format::XML_DOM_DOCUMENT`
- `relmon-json://...`: `Format::URI_JSON`
- `relmon-xml://...`: `Format::URI_XML`
- `relmon-min://...`: `Format::URI_MINIMALISTIC`

JSON and XML inputs support normal field names and compact aliases. URI JSON and URI XML contain a base64-encoded JSON/XML payload. URI XML also accepts URL-safe base64 without padding.

### JSON Array/String, Normal

```json
{
  "protocol": "relmon@1.0.0/3",
  "net": "100.00",
  "gross": "121.00",
  "tax": "21.00",
  "taxRate": "21.00",
  "unit": "EUR",
  "precision": 2,
  "scope": "r",
  "rounding": {
    "mode": "heven",
    "application": "tax"
  },
  "components": [
    {
      "net": "100.00",
      "gross": "121.00",
      "tax": "21.00",
      "taxRate": "21.00",
      "comment": "Test component"
    }
  ]
}
```

### JSON Array/String, Compact

```json
{
  "p": "relmon@1.0.0/3:c",
  "n": "100.00",
  "g": "121.00",
  "t": "21.00",
  "tr": "21.00",
  "u": "EUR",
  "pr": 2,
  "s": "c",
  "r": {
    "m": "heven",
    "a": "tax"
  },
  "cs": [
    {
      "n": "100.00",
      "g": "121.00",
      "t": "21.00",
      "tr": "21.00",
      "c": "Test component"
    }
  ]
}
```

### XML String/SimpleXML/DOMDocument, Normal

```xml
<Relmon>
    <protocol>relmon@1.0.0/3</protocol>
    <net>100.00</net>
    <gross>121.00</gross>
    <tax>21.00</tax>
    <taxRate>21.00</taxRate>
    <unit>EUR</unit>
    <precision>2</precision>
    <scope>r</scope>
    <rounding>
        <mode>heven</mode>
        <application>tax</application>
    </rounding>
    <components>
        <component>
            <net>100.00</net>
            <gross>121.00</gross>
            <tax>21.00</tax>
            <taxRate>21.00</taxRate>
            <comment>Test component</comment>
        </component>
    </components>
</Relmon>
```

### XML String/SimpleXML/DOMDocument, Compact

```xml
<Relmon>
    <p>relmon@1.0.0/3:c</p>
    <n>100.00</n>
    <g>121.00</g>
    <t>21.00</t>
    <tr>21.00</tr>
    <u>EUR</u>
    <pr>2</pr>
    <s>c</s>
    <r>
        <m>heven</m>
        <a>tax</a>
    </r>
    <cs>
        <entry>
            <n>100.00</n>
            <g>121.00</g>
            <t>21.00</t>
            <tr>21.00</tr>
            <c>Test component</c>
        </entry>
    </cs>
</Relmon>
```

### URI JSON

```php
$payload = base64_encode(json_encode([
    'p' => 'relmon@1.0.0/3:c',
    'n' => '100.00',
    'g' => '121.00',
    't' => '21.00',
    'tr' => '21.00',
]));

$input = 'relmon-json://' . $payload;
```

### URI XML

```php
$payload = base64_encode('<Relmon><p>relmon@1.0.0/3:c</p><n>100.00</n><g>121.00</g><t>21.00</t></Relmon>');

$input = 'relmon-xml://' . $payload;
```

### Minimalistic URI

Minimalistic URI format has exactly four semicolon-separated values:

```text
relmon-min://1.0.0/3;100.00;121.00;21.00
```

This maps to:

- protocol identifier: `relmon@1.0.0/3`
- net: `100.00`
- gross: `121.00`
- tax: `21.00`

## Field Aliases

| Normal | Compact |
| --- | --- |
| `protocol` | `p` |
| `net` | `n` |
| `gross` | `g` |
| `tax` | `t` |
| `taxRate` | `tr` |
| `unit` | `u` |
| `precision` | `pr` |
| `scope` | `s` |
| `rounding` | `r` |
| `rounding.mode` | `r.m` |
| `rounding.application` | `r.a` |
| `components` | `cs` |
| `components[].comment` | `cs[].c` |

Supported values:

- scope: `r` for root, `c` for component
- rounding mode: `haway`, `hzero`, `heven`, `up`, `down`
- rounding application: `tax`, `total`
- protocol modes: `c` for compact, `m` for minors, for example `relmon@1.0.0/3:c.m`

In normal mode, `net`, `gross`, and `tax` values must be decimal strings such as `"100.00"`. In minors mode (`:m`), they must be integers such as `10000`.

## RelMonObject Public Methods

`RelMonObject` stores monetary values as integer minors and exposes formatted helpers for display.

| Method | Return | Description |
| --- | --- | --- |
| `__construct(int $net, int $gross, int $tax, ?int $taxRate = null, ?string $unit = null, ?int $precision = null, ?int $taxRatePrecision = null, string $scope = Scope::ROOT, string $roundingMode = RoundingMode::HALF_EVEN, string $roundingApplication = RoundingApplication::TAX, array $components = [])` | `RelMonObject` | Creates an immutable RelMon value object. |
| `getNet()` | `int` | Net amount in minors. |
| `getGross()` | `int` | Gross amount in minors. |
| `getTax()` | `int` | Tax amount in minors. |
| `getTaxRate()` | `?int` | Tax rate converted using tax rate precision. |
| `getUnit()` | `?string` | Unit/currency, for example `EUR`. |
| `getPrecision()` | `?int` | Monetary precision used for net/gross/tax values. |
| `getTaxRatePrecision()` | `?int` | Precision used for tax rate values. |
| `getScope()` | `string` | `r` or `c`. |
| `getRoundingMode()` | `string` | Rounding mode code. |
| `getRoundingApplication()` | `string` | Rounding application code. |
| `getComponents()` | `MonetaryComponent[]` | Parsed and derived component values. |
| `getNetFormatted(string $decimalSeparator = '.', string $thousandsSeparator = '')` | `string` | Net formatted using object precision. |
| `getGrossFormatted(string $decimalSeparator = '.', string $thousandsSeparator = '')` | `string` | Gross formatted using object precision. |
| `getTaxFormatted(string $decimalSeparator = '.', string $thousandsSeparator = '')` | `string` | Tax formatted using object precision. |
| `getTaxRateFormatted(string $decimalSeparator = '.', string $thousandsSeparator = '')` | `?string` | Tax rate formatted using tax rate precision. |

Example:

```php
$relmon = RelMonFacade::build([
    'protocol' => 'relmon@1.0.0/3',
    'net' => '1234.56',
    'gross' => '1493.81',
    'tax' => '259.25',
    'taxRate' => '21.00',
]);

echo $relmon->getNet();                 // 123456
echo $relmon->getNetFormatted();        // 1234.56
echo $relmon->getNetFormatted(',', ' '); // 1 234,56
echo $relmon->getTaxRateFormatted();    // 21.00
```

## Features

- Support for PHP 8.0, 8.1, 8.2, 8.3, 8.4.
- Determinism Levels 1, 2, and 3 (DL1, DL2, DL3).
- Multiple input formats: JSON, XML, URI.
- Precise rounding and tax derivation.

## License

MIT
