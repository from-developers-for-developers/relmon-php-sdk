<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriXmlParser;
use PHPUnit\Framework\TestCase;

class UriXmlParserTest extends TestCase
{
    /**
     * @dataProvider provideUriXmlInputs
     */
    public function testParse(string $xml, string $protocolIdentifier, string $scope): void
    {
        $input = 'relmon-xml://' . base64_encode($xml);

        $dto = (new UriXmlParser())->parse($input);

        $this->assertEquals($protocolIdentifier, $dto->protocolIdentifier);
        $this->assertEquals('10000', $dto->net);
        $this->assertEquals('12100', $dto->gross);
        $this->assertEquals('2100', $dto->tax);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertEquals($scope, $dto->scope);
        $this->assertEmpty($dto->components);
    }

    public function provideUriXmlInputs(): array
    {
        return [
            'compact' => [
                <<<XML
<Relmon>
    <p>relmon@1.0.0/3:c.m</p>
    <n>10000</n>
    <g>12100</g>
    <t>2100</t>
    <u>EUR</u>
    <s>r</s>
</Relmon>
XML,
                'relmon@1.0.0/3:c.m',
                Scope::ROOT,
            ],
            'non-compact' => [
                <<<XML
<Relmon>
    <protocol>relmon@1.0.0/3:m</protocol>
    <net>10000</net>
    <gross>12100</gross>
    <tax>2100</tax>
    <unit>EUR</unit>
    <scope>r</scope>
</Relmon>
XML,
                'relmon@1.0.0/3:m',
                Scope::ROOT,
            ],
        ];
    }

    public function testParseSupportsUrlSafeBase64WithoutPadding(): void
    {
        $xml = '<Relmon><p>relmon@1.0.0/3:c.m</p><n>10000</n><g>12100</g><t>2100</t><u>EUR</u><s>r</s></Relmon>';
        $input = rtrim(strtr(base64_encode($xml), '+/', '-_'), '=');

        $dto = (new UriXmlParser())->parse('relmon-xml://' . $input);

        $this->assertEquals('relmon@1.0.0/3:c.m', $dto->protocolIdentifier);
        $this->assertEquals('10000', $dto->net);
        $this->assertEquals('12100', $dto->gross);
        $this->assertEquals('2100', $dto->tax);
    }

    public function testParseThrowsExceptionOnWrongPrefix(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriXmlParser())->parse('relmon-json://abcd');
    }

    public function testParseThrowsExceptionOnWrongInputType(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriXmlParser())->parse(new \stdClass());
    }

    public function testParseThrowsExceptionOnEmptyPayload(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriXmlParser())->parse('relmon-xml://');
    }

    public function testParseThrowsExceptionOnInvalidBase64(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriXmlParser())->parse('relmon-xml://***');
    }
}
