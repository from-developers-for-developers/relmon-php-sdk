<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Service\ValidationService;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use PHPUnit\Framework\TestCase;

class ValidationServiceTest extends TestCase
{
    public static function validateDataProvider(): array
    {
        return [
            'negative precision' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00', precision: -1),
                'Precision must be greater or equal to zero.',
                '.precision',
            ],
            'invalid scope' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00', scope: 'x'),
                'Invalid scope.',
                '.scope',
            ],
            'invalid rounding mode' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00', roundingMode: 'x'),
                'Invalid rounding mode.',
                '.roundingMode',
            ],
            'invalid rounding application' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00', roundingApplication: 'x'),
                'Invalid rounding application.',
                '.roundingApplication',
            ],
            'component scope without components' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00', scope: 'c'),
                'Components are required if the scope is "c".',
                '.components',
            ],
            'mixed signs' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto(
                    'relmon@1.0.0/3',
                    '100.00',
                    '121.00',
                    '21.00',
                    '21.00',
                    components: [new MonetaryComponentDto('-10.00', '-12.10', '-2.10')]
                ),
                'Net, gross and tax fields of root and component levels must have the same sign.',
                '.',
            ],
            'mixed types' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', 10000, '121.00', 2100, '21.00'),
                'Net, gross and tax fields of root and component levels must be of the same type.',
                '.',
            ],
            'minors mode requires integers' => [
                new ProtocolIdentifier('relmon@1.0.0/3:m'),
                new RelMonDto('relmon@1.0.0/3:m', '100.00', '121.00', '21.00'),
                'Net, gross and tax of root and component levels in minors mode must be of type integer.',
                '.',
            ],
            'decimal format required' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100', '121.00', '21.00'),
                'Net, gross and tax of root and component levels must be of type decimal.',
                '.',
            ],
            'precision overflow' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', precision: 1),
                'Decimal places of net, gross and tax values must not exceed the given precision.',
                '.',
            ],
            'dl1 missing tax rate' => [
                new ProtocolIdentifier('relmon@1.0.0/1'),
                new RelMonDto('relmon@1.0.0/1', '100.00'),
                'Tax rate must be specified for DL1.',
                '.taxRate',
            ],
            'component tax rate mismatch' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto(
                    'relmon@1.0.0/3',
                    '100.00',
                    '121.00',
                    '21.00',
                    '21.00',
                    scope: 'c',
                    components: [new MonetaryComponentDto('100.00', '121.00', '21.00', '9.00')]
                ),
                'Tax rate on the root level must be the same on the component level.',
                '.components.0.taxRate',
            ],
            'gross less than net positive' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '121.00', '100.00', '21.00'),
                'Gross must be greater than or equal to net for positive amounts.',
                '.gross',
            ],
            'gross greater than net negative' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '-121.00', '-100.00', '-21.00'),
                'Gross must be less than or equal to net for negative amounts.',
                '.gross',
            ],
            'tax greater than gross positive' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '122.00'),
                'Tax must be less than or equal to gross for positive amounts.',
                '.tax',
            ],
        ];
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(
        ProtocolIdentifier $protocolIdentifier,
        RelMonDto $dto,
        string $expectedMessage,
        string $expectedField
    ): void
    {
        $violations = (new ValidationService())->validate($protocolIdentifier, $dto);

        $this->assertNotEmpty($violations);
        $this->assertSame($expectedMessage, $violations[0]->getMessage());
        $this->assertSame($expectedField, $violations[0]->getField());
    }

    public function testValidateReturnsEmptyArrayForValidDto(): void
    {
        $dto = new RelMonDto(
            'relmon@1.0.0/3',
            '100.00',
            '121.00',
            '21.00',
            '21.00',
            'EUR',
            2,
            'c',
            'heven',
            'tax',
            [
                new MonetaryComponentDto('40.00', '48.40', '8.40', '21.00', 'A'),
                new MonetaryComponentDto('60.00', '72.60', '12.60', '21.00', 'B'),
            ]
        );

        $violations = (new ValidationService())->validate(new ProtocolIdentifier('relmon@1.0.0/3'), $dto);

        $this->assertSame([], $violations);
    }
}
