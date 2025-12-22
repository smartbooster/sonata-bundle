<?php

namespace Smart\SonataBundle\Composer;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class InstalledVersions
{
    public static function getFormattedVersion(string $packageName, bool $withVPrefix = true): array
    {
        $version = str_replace(['dev-', 'release_'], ['', ''], \Composer\InstalledVersions::getPrettyVersion($packageName));
        if ($withVPrefix && !str_starts_with($version, 'v')) {
            $version = 'v' . $version;
        } elseif (!$withVPrefix && str_starts_with($version, 'v')) {
            $version = substr($version, 1);
        }
        $explodedVersion = explode('.', $version);
        $minorVersion = $explodedVersion[0] . (isset($explodedVersion[1]) ? ('.' . $explodedVersion[1]) : null);
        $version = $minorVersion . '.' . ($explodedVersion[2] ?? 0);

        return [
            'minor' => $minorVersion,
            'full' => $version,
        ];
    }

    public static function getFrontVersion(string $projectDir, string $packageName): string
    {
        $frontPackage = json_decode((string) file_get_contents($projectDir . '/package.json'), true);
        $packageVersion = $frontPackage['dependencies'][$packageName] ?? $frontPackage['devDependencies'][$packageName] ?? null;
        if ($packageVersion === null) {
            throw new \InvalidArgumentException('Package "' . $packageName . '" not found in package.json.');
        }
        if (str_starts_with($packageVersion, '^')) {
            $packageVersion = substr($packageVersion, 1); // MDT on enlève le ^ en début de version si présent
        }
        $explodedVersion = explode('.', $packageVersion);

        return $explodedVersion[0] . '.' . $explodedVersion[1];
    }
}
