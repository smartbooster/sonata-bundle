<?php

namespace Smart\SonataBundle\Controller\Admin\Monitoring;

use Smart\SonataBundle\Composer\InstalledVersions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class HealthCheckController extends AbstractController
{
    protected array $currentVersions = [];
    protected string $projectDir;

    private Security $security;
    private TranslatorInterface $translator;
    private Environment $twig;

    #[IsGranted('ROLE_MONITORING_HEALTH_CHECK')]
    public function index(): Response
    {
        $releases = json_decode((string) file_get_contents('https://www.smartplatform.dev/releases.json'), true);
        foreach (
            [
                'symfony' => 'symfony/http-kernel',
                'vue' => 'vue',
            ] as $packageRef => $packageName
        ) {
            $this->currentVersions[$packageRef] = $this->getPackageVersion($packageName);
        }
        // MDT fix pour PHP où la var d'env embarque aussi le patch
        $explodedPhpVersion = explode('.', $this->currentVersions['php']);
        $this->currentVersions['php'] = $explodedPhpVersion[0] . '.' . $explodedPhpVersion[1];

        $etatActuelStatus = 'ok';
        foreach ($releases as $key => $data) {
            if (!isset($this->currentVersions[$key])) {
                unset($releases[$key]);
                continue;
            }
            $currentVersion = $this->currentVersions[$key];
            $releases[$key]['current'] = $currentVersion;
            $status = 'n_a';
            foreach ($data['releases'] as $release) {
                if ($release['release'] === $currentVersion) {
                    $status = $release['status'];
                    if ($key === 'smartplatform') {
                        $releases[$key]['current_description'] = $release['description'] ?? '';
                    }
                    break;
                }
            }
            // Report du status le plus bas sur l'état actuel
            if (
                ($status === 'minor' && $etatActuelStatus === 'ok')
                || ($status === 'major' && in_array($etatActuelStatus, ['minor', 'ok']))
                || ($status === 'legacy' && in_array($etatActuelStatus, ['major', 'minor', 'ok']))
                || $status === 'n_a'
            ) {
                $etatActuelStatus = $status;
            }
            $releases[$key]['status'] = $status;
            $releases[$key]['last'] = $data['releases'][0]['release'];
        }

        return new Response($this->twig->render('@SmartSonata/admin/monitoring/health_check.html.twig', [
            'etatActuelStatus' => $etatActuelStatus,
            'releases' => $releases,
            'phpVulnerabilities' => $this->getPhpVulnerabilities(),
            ...($this->security->isGranted('ROLE_MONITORING_HEALTH_CHECK_JS_CVE') ? ['jsVulnerabilities' => $this->getJsVulnerabilities()] : [])
        ]));
    }

    private function getPackageVersion(string $packageName): ?string
    {
        if ($packageName === 'vue') {
            return InstalledVersions::getFrontVersion($this->projectDir, $packageName);
        }

        return InstalledVersions::getFormattedVersion($packageName, false)['minor'] ?? null;
    }

    private function getPhpVulnerabilities(): array
    {
        $process = new Process(['composer', 'audit', '--format=json']);
        $process->setWorkingDirectory($this->projectDir);
        $process->setEnv(['COMPOSER_HOME' => $this->projectDir]);
        $process->run();

        $output = $process->getOutput();
        if (empty($output)) {
            return ['error' => $process->getErrorOutput()];
        }

        try {
            $auditOutput = json_decode($output, true);
            if (!isset($auditOutput['advisories']) || !is_array($auditOutput['advisories'])) {
                return ['severityCount' => []];
            }

            $severityCount = [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'unknown' => 0,
            ];
            $vulnerabilities = [];

            // Composer audit groupe les advisories par package d'où le double foreach
            foreach ($auditOutput['advisories'] as $packageName => $advisories) {
                foreach ($advisories as $advisory) {
                    // Compter les CVE par sévérité
                    $severityValue = strtolower($advisory['severity'] ?? 'unknown');
                    $severityCount[$severityValue]++;

                    $vulnerabilities[] = $this->getAdvisoryVulnerability($advisory, $packageName, $severityValue);
                }
            }

            return $this->formatVulnerabilitiesData($severityCount, $vulnerabilities);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getJsVulnerabilities(): array
    {
        $process = new Process(['yarn', 'audit', '--json']);
        $process->setWorkingDirectory($this->projectDir);
        $process->run();

        $output = $process->getOutput();
        if (empty($output)) {
            return ['error' => $process->getErrorOutput()];
        }

        try {
            $severityCount = [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'moderate' => 0,
                'low' => 0,
                'unknown' => 0,
            ];
            $vulnerabilities = [];

            // Yarn audit --json retourne une ligne JSON par événement (auditAdvisory)
            foreach (explode("\n", trim($output)) as $line) {
                $data = json_decode($line, true);
                if (isset($data['type']) && $data['type'] === 'auditAdvisory') {
                    $advisory = $data['data']['advisory'];

                    // Compter les CVE par sévérité
                    $severityValue = strtolower($advisory['severity'] ?? 'unknown');
                    $severityCount[$severityValue]++;

                    $firstDependent = $advisory['findings'][0]['paths'][0] ?? null;
                    $vulnerabilities[] = [
                        ...$this->getAdvisoryVulnerability($advisory, $advisory['module_name'], $severityValue),
                        'dependent' => $firstDependent !== null ? [
                            'parent' => substr($firstDependent, 0, (int) strpos($firstDependent, '>')),
                            'chain' => substr($firstDependent, 0, (int) strrpos($firstDependent, '>')),
                        ] : null,
                        'patched_versions' => $advisory['patched_versions'] ?? null,
                    ];
                }
            }

            return $this->formatVulnerabilitiesData($severityCount, $vulnerabilities);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getAdvisoryVulnerability(array $advisory, string $packageName, string $severityValue): array
    {
        return [
            'package' => $packageName,
            'title' => $advisory['title'] ?? null,
            'cve' => $advisory['cve'] ?? (!empty($advisory['cves']) ? $advisory['cves'][0] : null),
            'version' => isset($advisory['findings'])
                ? ($advisory['findings'][0]['version'] ?? 'unknown')
                : InstalledVersions::getFormattedVersion($packageName, false)['full'],
            'link' => $advisory['link'] ?? $advisory['url'] ?? null,
            'severity' => [
                'value' => $severityValue,
                'label' => $this->translator->trans('monitoring.severity_scale.' . $severityValue),
                'class' => match ($severityValue) {
                    'critical' => 'bg-danger',
                    'high' => 'bg-warning',
                    'medium', 'moderate' => 'bg-yellow-400 text-gray-900',
                    default => 'bg-info', // low ou non
                },
            ],
        ];
    }

    private function formatVulnerabilitiesData(array $severityCount, array $vulnerabilities): array
    {
        // Tri des CVE par criticité décroissante
        usort($vulnerabilities, function ($a, $b) {
            $severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'moderate' => 2, 'low' => 3, 'unknown' => 4];
            $severityA = $severityOrder[strtolower($a['severity']['value'])] ?? 4;
            $severityB = $severityOrder[strtolower($b['severity']['value'])] ?? 4;
            return $severityA - $severityB;
        });

        return [
            'severityCount' => array_map(function ($count, $severityValue) {
                return [
                    'count' => $count,
                    'prefix' => $this->translator->trans('monitoring.severity_scale.' . $severityValue),
                    'class' => match ($severityValue) {
                        'critical' => 'bg-danger',
                        'high' => 'bg-warning',
                        'medium', 'moderate' => 'bg-yellow-400 text-gray-900',
                        default => 'bg-info',
                    },
                ];
            }, $severityCount, array_keys($severityCount)),
            'details' => $vulnerabilities,
        ];
    }

    public function setCurrentVersions(array $currentVersions): void
    {
        $this->currentVersions = $currentVersions;
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }
}
