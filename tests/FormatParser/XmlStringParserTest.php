<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlStringParser;
use PHPUnit\Framework\TestCase;

class XmlStringParserTest extends TestCase
{
    /**
     * @dataProvider provideXmlInputs
     */
    public function testParse(string $xml, string $protocolIdentifier, string $scope, string $roundingMode): void
    {
        $parser = new XmlStringParser();

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
        $this->assertCount(1, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('100.00', $component->getNet());
        $this->assertEquals('121.00', $component->getGross());
        $this->assertEquals('21.00', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Test component', $component->getComment());
    }

    public function provideXmlInputs(): array
    {
        return [
            'compact' => [
                <<<XML
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
            <n>100.00</n>
            <g>121.00</g>
            <t>21.00</t>
            <tr>21.00</tr>
            <c>Test component</c>
        </entry>
    </cs>
</Relmon>
XML,
                'relmon@1.0.0/3:c',
                Scope::COMPONENT,
                RoundingMode::UP,
            ],
            'non-compact' => [
                <<<XML
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
            <net>100.00</net>
            <gross>121.00</gross>
            <tax>21.00</tax>
            <taxRate>21.00</taxRate>
            <comment>Test component</comment>
        </component>
    </components>
</Relmon>
XML,
                'relmon@1.0.0/3',
                Scope::COMPONENT,
                RoundingMode::HALF_EVEN,
            ],
        ];
    }

    public function testParseThrowsExceptionOnWrongInputType(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlStringParser())->parse(['not-a-string']);
    }

    public function testParseThrowsExceptionOnWrongNotation(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlStringParser())->parse('protocol=relmon');
    }

    public function testParseThrowsExceptionOnMalformedXml(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlStringParser())->parse('<Relmon><protocol>relmon@1.0.0/3</protocol>');
    }
}
