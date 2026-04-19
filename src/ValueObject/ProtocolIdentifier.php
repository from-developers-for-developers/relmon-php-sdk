<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use Composer\Semver\VersionParser;
use FromDevelopersForDevelopers\RelMon\Exception\ProtocolIdentifierInvalidException;

class ProtocolIdentifier
{
    private string $version;
    private int $determinismLevel;
    private bool $inCompactMode = false;
    private bool $inMinorsMode = false;

    // Cached protocol identifiers to speed up parsing in case of series of RelMonObject instances are processed.
    private static array $cachedProtocolIdentifiers = [];

    /**
     * @param string $protocolIdentifier
     * @throws ProtocolIdentifierInvalidException
     */
    public function __construct(string $protocolIdentifier)
    {
        if (isset(self::$cachedProtocolIdentifiers[$protocolIdentifier])) {
            $this->version = self::$cachedProtocolIdentifiers[$protocolIdentifier]->getVersion();
            $this->determinismLevel = self::$cachedProtocolIdentifiers[$protocolIdentifier]->getDeterminismLevel();
            $this->inCompactMode = self::$cachedProtocolIdentifiers[$protocolIdentifier]->isInCompactMode();
            $this->inMinorsMode = self::$cachedProtocolIdentifiers[$protocolIdentifier]->isInMinorsMode();

            return;
        }

        if (!str_starts_with($protocolIdentifier, 'relmon@')) {
            throw new ProtocolIdentifierInvalidException('ProtocolIdentifier should start with "relmon@".');
        }

        $parts = explode('/', substr($protocolIdentifier, 7));
        $version = $parts[0] ?? '';
        $protocolOptions = $parts[1] ?? '';

        try {
            (new VersionParser())->normalize($version);
        } catch (\UnexpectedValueException $e) {
            throw new ProtocolIdentifierInvalidException("Invalid protocol version: {$version}");
        }

        $this->version = $version;

        if (!$protocolOptions) {
            throw new ProtocolIdentifierInvalidException('Protocol options not found in ProtocolIdentifier.');
        }

        $protocolOptions = explode(':', $protocolOptions);

        if (count($protocolOptions) > 2 || count($protocolOptions) < 1) {
            throw new ProtocolIdentifierInvalidException('Protocol options should be in format "determinismLevel[?:mode1[?.mode2...]]".');
        }

        $determinismLevel = (int)$protocolOptions[0];
        $modes = !empty($protocolOptions[1]) ? explode('.', $protocolOptions[1]) : [];

        $this->determinismLevel = $determinismLevel;

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

    public function getDeterminismLevel(): int
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
