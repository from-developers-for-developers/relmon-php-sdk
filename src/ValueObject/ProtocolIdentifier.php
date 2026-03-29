<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

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
            $this->version = self::$cachedProtocolIdentifiers[$protocolIdentifier]->getVersion();
            $this->determinismLevel = self::$cachedProtocolIdentifiers[$protocolIdentifier]->getDeterminismLevel();
            $this->inCompactMode = self::$cachedProtocolIdentifiers[$protocolIdentifier]->isInCompactMode();
            $this->inMinorsMode = self::$cachedProtocolIdentifiers[$protocolIdentifier]->isInCompactMode();

            return;
        }

        if (!str_starts_with($protocolIdentifier, 'relmon@')) {
            throw new ProtocolIdentifierInvalidException('ProtocolIdentifier should start with "relmon@".');
        }

        list($version, $protocolOptions) = explode('/', substr($protocolIdentifier, 7));

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
        $modes = !empty($protocolOptions[1]) ? explode('.', $protocolOptions[1]) : [];

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
