<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Routes publiques de téléchargement des applications Android CSAR.
 *
 * Chaque route sert un APK depuis public/downloads/ avec un nom de
 * fichier explicite, ou renvoie un 404 contrôlé si le fichier est
 * absent — jamais une 500 ni un fichier arbitraire.
 */
class ApkDownloadTest extends TestCase
{
    /**
     * @return array<string, array{uri: string, files: list<string>, downloadName: string}>
     */
    private static function apkRoutes(): array
    {
        return [
            'sim'          => ['uri' => '/telecharger-app/apk',           'files' => ['sim-collecte.apk', 'sim-csar.apk'], 'downloadName' => 'CSAR-SIM-Collecte.apk'],
            'distribution' => ['uri' => '/telecharger-distribution/apk',  'files' => ['csar-distribution.apk'],            'downloadName' => 'CSAR-Distribution.apk'],
            'scanner'      => ['uri' => '/telecharger-scanner/apk',       'files' => ['csar-scanner.apk'],                 'downloadName' => 'CSAR-Scanner.apk'],
            'magasinier'   => ['uri' => '/telecharger-magasinier/apk',    'files' => ['csar-warehouse.apk'],               'downloadName' => 'CSAR-Magasinier.apk'],
            'comptable'    => ['uri' => '/telecharger-comptable/apk',     'files' => ['csar-comptable.apk'],               'downloadName' => 'CSAR-Comptable.apk'],
        ];
    }

    private static function apkExists(array $files): bool
    {
        foreach ($files as $name) {
            if (file_exists(public_path('downloads/' . $name))) {
                return true;
            }
        }
        return false;
    }

    public function test_central_page_lists_the_four_csar_applications(): void
    {
        $response = $this->get('/telecharger-app');

        $response->assertOk();
        foreach (['CSAR Distribution', 'CSAR Scanner', 'CSAR Magasinier', 'CSAR Comptable'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_central_page_shows_availability_badge(): void
    {
        $response = $this->get('/telecharger-app');

        $response->assertOk();
        // Au moins l'un des deux badges doit être rendu selon la présence réelle des APK.
        $content = $response->getContent();
        $this->assertTrue(
            str_contains($content, 'Disponible') || str_contains($content, 'Bientôt disponible'),
            'La page doit afficher le statut de disponibilité des applications.'
        );
    }

    /**
     * @dataProvider apkRouteProvider
     */
    public function test_apk_route_serves_file_or_controlled_404(string $uri, array $files, string $downloadName): void
    {
        $response = $this->get($uri);

        if (self::apkExists($files)) {
            $response->assertOk();
            $disposition = (string) $response->headers->get('content-disposition');
            $this->assertStringContainsString($downloadName, $disposition);
        } else {
            // Fichier absent : 404 contrôlé, jamais 500.
            $response->assertNotFound();
        }
    }

    public static function apkRouteProvider(): iterable
    {
        foreach (self::apkRoutes() as $name => $route) {
            yield $name => [$route['uri'], $route['files'], $route['downloadName']];
        }
    }

    /**
     * Un paramètre d'URL ne doit jamais permettre de servir un autre fichier :
     * la requête doit retourner l'APK prévu ou un 404, jamais le contenu d'un
     * fichier arbitraire comme .env.
     */
    public function test_download_routes_do_not_serve_arbitrary_files(): void
    {
        foreach (self::apkRoutes() as $route) {
            $response = $this->get($route['uri'] . '?file=../../.env');

            $this->assertContains($response->getStatusCode(), [200, 404]);

            if ($response->getStatusCode() === 200) {
                // Réponse binaire : le fichier servi doit être l'APK prévu (Content-Disposition),
                // le paramètre ?file= est ignoré — jamais un chemin arbitraire.
                $disposition = (string) $response->headers->get('content-disposition');
                $this->assertStringContainsString($route['downloadName'], $disposition);
                $this->assertStringNotContainsString('.env', $disposition);
            } else {
                $content = $response->getContent();
                $this->assertIsString($content);
                $this->assertStringNotContainsString('APP_KEY=', substr($content, 0, 4096));
            }
        }
    }

    /**
     * Les routes de téléchargement sont enregistrées avec leurs noms.
     */
    public function test_named_download_routes_resolve(): void
    {
        foreach ([
            'app.download.apk',
            'app.distribution.download.apk',
            'app.scanner.download.apk',
            'app.magasinier.download.apk',
            'app.comptable.download.apk',
        ] as $routeName) {
            $this->assertNotEmpty(route($routeName), "La route nommée {$routeName} doit exister.");
        }
    }
}
