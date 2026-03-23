<?php

namespace FromDevelopersForDevelopers\RelMon;

use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevelEnum;
use FromDevelopersForDevelopers\RelMon\Exception\ProtocolIdentifierInvalidException;

class ProtocolIdentifier
{
    private string $version;
    private DeterminismLevelEnum $determinismLevel;
    private bool $inCompactMode = false;
    private bool $inMinorsMode = false;

    // Cached protocol identifiers to speed up parsing in case of series of RelMonObject instances are processed.
    private static array $cachedProtocolIdentifiers = [];

    public function __construct(string $protocolIdentifier)
    {
        if (isset(self::$cachedProtocolIdentifiers[$protocolIdentifier])) {
            return self::$cachedProtocolIdentifiers[$protocolIdentifier];
        }

        if (!str_starts_with($protocolIdentifier, 'relmon@')) {
            throw new ProtocolIdentifierInvalidException('ProtocolIdentifier should start with "relmon@".');
        }

        $protocolIdentifier = substr($protocolIdentifier, 7);
        list($version, $protocolOptions) = explode('/', $protocolIdentifier);

        // @TODO: assert version format
        $this->version = $version;

        if (!$protocolOptions) {
            throw new ProtocolIdentifierInvalidException('Protocol options not found in ProtocolIdentifier.');
        }

        $protocolOptions = explode(':', $protocolOptions);

        if (count($protocolOptions) > 2 || count($protocolOptions) < 1) {
            throw new ProtocolIdentifierInvalidException('Protocol options should be in format "determinismLevel[?:mode1[?.mode2...]]".');
        }

        $determinismLevel = $protocolOptions[0];
        $modes = $protocolOptions[1] ?? [];

        $this->determinismLevel = DeterminismLevelEnum::from((int)$determinismLevel);

        if (is_array($modes)) {
            $modesActive = 0;

            if (in_array('c', $modes, true)) {
                $this->inCompactMode = true;
                $modesActive++;
            }

            if (in_array('m', $modes, true)) {
                $this->inMinorsMode = true;
                $modesActive++;
            }

            if (count($modes) - $modesActive > 0) {
                throw new ProtocolIdentifierInvalidException('Only "c" and "m" modes are allowed in ProtocolIdentifier.');
            }
        }

        self::$cachedProtocolIdentifiers[$protocolIdentifier] = $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDeterminismLevel(): DeterminismLevelEnum
    {
        return $this->determinismLevel;
    }

    public function isInCompactMode(): bool
    {
        return $this->inCompactMode;
    }

    public function isInMinorsMode(): bool
    {
        return $this->inMinorsMode;
    }
}
