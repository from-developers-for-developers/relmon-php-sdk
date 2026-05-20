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
            'mixed signs on the same root level' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '-21.00', '21.00'),
                'Net, gross and tax fields on the same level must have the same sign.',
                '.',
            ],
            'mixed signs between root and components are allowed' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto(
                    'relmon@1.0.0/3',
                    '100.00',
                    '121.00',
                    '21.00',
                    '21.00',
                    components: [new MonetaryComponentDto('-10.00', '-12.10', '-2.10')]
                ),
                '',
            ],
            'mixed signs on the same component level' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto(
                    'relmon@1.0.0/3',
                    '100.00',
                    '121.00',
                    '21.00',
                    '21.00',
                    components: [new MonetaryComponentDto('-10.00', '12.10', '-2.10')]
                ),
                'Net, gross and tax fields on the same level must have the same sign.',
                '.components.0',
            ],
            'mixed types' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', 10000, '121.00', 2100, '21.00'),
                'Net, gross and tax fields of root and component levels must be of the same type.',
                '.',
            ],
            'all monetary fields missing for unsupported determinism are allowed' => [
                new ProtocolIdentifier('relmon@1.0.0/99'),
                new RelMonDto('relmon@1.0.0/99'),
                '',
            ],
            'integers in non-minors mode require decimal strings' => [
                new ProtocolIdentifier('relmon@1.0.0/99'),
                new RelMonDto('relmon@1.0.0/99', 10000, 12100, 2100),
                'Net, gross and tax of root and component levels must be of type decimal (string).',
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
            'dl1 missing net and gross' => [
                new ProtocolIdentifier('relmon@1.0.0/1'),
                new RelMonDto('relmon@1.0.0/1', taxRate: '21.00'),
                'Net or gross must be specified for DL1.',
                '.',
            ],
            'dl2 missing tax rate' => [
                new ProtocolIdentifier('relmon@1.0.0/2'),
                new RelMonDto('relmon@1.0.0/2', '100.00', '121.00'),
                'Tax rate must be specified for DL2.',
                '.taxRate',
            ],
            'dl2 missing gross' => [
                new ProtocolIdentifier('relmon@1.0.0/2'),
                new RelMonDto('relmon@1.0.0/2', '100.00', taxRate: '21.00'),
                'Net and gross must be specified for DL2.',
                '.',
            ],
            'dl3 missing net and gross' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', tax: '21.00'),
                'Net or gross must be specified for DL3.',
                '.',
            ],
            'dl3 missing tax' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00'),
                'Tax must be specified for DL3.',
                '.tax',
            ],
            'negative tax rate is invalid' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '-21.00'),
                'Tax rate must be a non-negative decimal.',
                '.taxRate',
            ],
            'integer tax rate is allowed' => [
                new ProtocolIdentifier('relmon@1.0.0/2'),
                new RelMonDto('relmon@1.0.0/2', '100.0', '121.00', taxRate: 21),
                '',
            ],
            'mixed decimal places within precision are allowed' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '100.0', '121.00', '21.000', precision: 3),
                '',
            ],
            'component missing tax returns nested violation' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto(
                    'relmon@1.0.0/3',
                    '100.00',
                    '121.00',
                    '21.00',
                    '21.00',
                    scope: 'c',
                    components: [new MonetaryComponentDto('100.00', '121.00', taxRate: '21.00')]
                ),
                'Tax must be specified for DL3.',
                '.components.0.tax',
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
            'tax less than gross negative' => [
                new ProtocolIdentifier('relmon@1.0.0/3'),
                new RelMonDto('relmon@1.0.0/3', '-100.00', '-121.00', '-122.00'),
                'Tax must be greater than or equal to gross for negative amounts.',
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
        string $expectedMessage = '',
        string $expectedField = ''
    ): void {
        $violations = (new ValidationService())->validate($protocolIdentifier, $dto);

        if ($expectedMessage === '') {
            $this->assertSame([], $violations);

            return;
        }

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
