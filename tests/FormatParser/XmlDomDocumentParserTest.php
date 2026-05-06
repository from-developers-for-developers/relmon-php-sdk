<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlDomDocumentParser;
use PHPUnit\Framework\TestCase;

class XmlDomDocumentParserTest extends TestCase
{
    /**
     * @dataProvider provideDomDocuments
     */
    public function testParse(
        \DOMDocument $document,
        string $protocolIdentifier,
        string $scope,
        string $roundingMode
    ): void {
        $dto = (new XmlDomDocumentParser())->parse($document);

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
        $this->assertEmpty($dto->components);
    }

    public function provideDomDocuments(): array
    {
        return [
            'compact' => [
                $this->createDocument(<<<XML
<Relmon>
    <p>relmon@1.0.0/3:c</p>
    <n>100.00</n>
    <g>121.00</g>
    <t>21.00</t>
    <tr>21.00</tr>
    <u>EUR</u>
    <pr>2</pr>
    <s>r</s>
    <r>
        <m>up</m>
        <a>tax</a>
    </r>
</Relmon>
XML),
                'relmon@1.0.0/3:c',
                Scope::ROOT,
                RoundingMode::UP,
            ],
            'non-compact' => [
                $this->createDocument(<<<XML
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
</Relmon>
XML),
                'relmon@1.0.0/3',
                Scope::ROOT,
                RoundingMode::HALF_EVEN,
            ],
        ];
    }

    private function createDocument(string $xml): \DOMDocument
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadXML($xml);

        return $document;
    }

    public function testParseThrowsExceptionOnWrongInputType(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlDomDocumentParser())->parse(new \stdClass());
    }

    public function testParseThrowsExceptionOnMissingRootElement(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new XmlDomDocumentParser())->parse(new \DOMDocument('1.0', 'UTF-8'));
    }
}
