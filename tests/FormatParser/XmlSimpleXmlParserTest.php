<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlSimpleXmlParser;
use PHPUnit\Framework\TestCase;

class XmlSimpleXmlParserTest extends TestCase
{
    /**
     * @dataProvider provideXmlElements
     */
    public function testParse(
        \SimpleXMLElement $xml,
        string $protocolIdentifier,
        string $scope,
        string $roundingMode
    ): void {
        $parser = new XmlSimpleXmlParser();

        $dto = $parser->parse($xml);

        $this->assertEquals($protocolIdentifier, $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
        $this->assertEquals('21.00', $dto->taxRate);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertSame(2, $dto->precision);
        $this->assertEquals($scope, $dto->scope);
        $this->assertEquals($roundingMode, $dto->roundingMode);
        $this->assertEquals(RoundingApplication::TAX, $dto->roundingApplication);
        $this->assertCount(2, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('40.00', $component->getNet());
        $this->assertEquals('48.40', $component->getGross());
        $this->assertEquals('8.40', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Component A', $component->getComment());

        $secondComponent = $dto->components[1];
        $this->assertEquals('60.00', $secondComponent->getNet());
        $this->assertEquals('72.60', $secondComponent->getGross());
        $this->assertEquals('12.60', $secondComponent->getTax());
        $this->assertEquals('21.00', $secondComponent->getTaxRate());
        $this->assertEquals('Component B', $secondComponent->getComment());
    }

    public function provideXmlElements(): array
    {
        return [
            'compact' => [
                new \SimpleXMLElement(<<<XML
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
        <m>up</m>
        <a>tax</a>
    </r>
    <cs>
        <entry>
            <n>40.00</n>
            <g>48.40</g>
            <t>8.40</t>
            <tr>21.00</tr>
            <c>Component A</c>
        </entry>
        <entry>
            <n>60.00</n>
            <g>72.60</g>
            <t>12.60</t>
            <tr>21.00</tr>
            <c>Component B</c>
        </entry>
    </cs>
</Relmon>
XML),
                'relmon@1.0.0/3:c',
                Scope::COMPONENT,
                RoundingMode::UP,
            ],
            'non-compact' => [
                new \SimpleXMLElement(<<<XML
<Relmon>
    <protocol>relmon@1.0.0/3</protocol>
    <net>100.00</net>
    <gross>121.00</gross>
    <tax>21.00</tax>
    <taxRate>21.00</taxRate>
    <unit>EUR</unit>
    <precision>2</precision>
    <scope>c</scope>
    <rounding>
        <mode>heven</mode>
        <application>tax</application>
    </rounding>
    <components>
        <component>
            <net>40.00</net>
            <gross>48.40</gross>
            <tax>8.40</tax>
            <taxRate>21.00</taxRate>
            <comment>Component A</comment>
        </component>
        <line>
            <net>60.00</net>
            <gross>72.60</gross>
            <tax>12.60</tax>
            <taxRate>21.00</taxRate>
            <comment>Component B</comment>
        </line>
    </components>
</Relmon>
XML),
                'relmon@1.0.0/3',
                Scope::COMPONENT,
                RoundingMode::HALF_EVEN,
            ],
        ];
    }

    public function testParseThrowsExceptionOnWrongInputType(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlSimpleXmlParser())->parse(new \stdClass());
    }

    public function testParseThrowsExceptionOnInvalidRoundingMode(): void
    {
        $xml = new \SimpleXMLElement(<<<XML
<Relmon>
    <protocol>relmon@1.0.0/3</protocol>
    <rounding>
        <mode>invalid</mode>
        <application>tax</application>
    </rounding>
</Relmon>
XML);

        $this->expectException(ValidationException::class);
        (new XmlSimpleXmlParser())->parse($xml);
    }

    public function testParseThrowsExceptionOnIncompleteRounding(): void
    {
        $xml = new \SimpleXMLElement(<<<XML
<Relmon>
    <protocol>relmon@1.0.0/3</protocol>
    <rounding>
        <mode>up</mode>
    </rounding>
</Relmon>
XML);

        $this->expectException(ValidationException::class);
        (new XmlSimpleXmlParser())->parse($xml);
    }
}
