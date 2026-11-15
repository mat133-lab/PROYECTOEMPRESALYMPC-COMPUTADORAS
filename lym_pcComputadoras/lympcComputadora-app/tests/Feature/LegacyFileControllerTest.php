<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyFileControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        require_once base_path('resources/legacy/includes/file_helpers.php');
    }

    public function test_legacy_relative_upload_paths_resolve_to_the_shared_file_route(): void
    {
        $viewUrl = legacy_file_url('../uploads/pasante/reporte final.pdf');
        $downloadUrl = legacy_file_url('../uploads/pasante/reporte final.pdf', true);

        $this->assertStringEndsWith('/uploads/pasante/reporte%20final.pdf', (string) $viewUrl);
        $this->assertStringEndsWith('/uploads/pasante/reporte%20final.pdf?download=1', (string) $downloadUrl);
        $this->assertNull(legacy_file_url('../uploads/../../.env'));
    }

    public function test_upload_route_serves_pdf_inline_and_as_a_pdf_download(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/pasante/reporte.pdf', "%PDF-1.4\nsample\n%%EOF");
        Storage::disk('public')->assertExists('uploads/pasante/reporte.pdf');

        $view = $this->get('http://localhost/uploads/pasante/reporte.pdf');
        $view->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $view->headers->get('content-type'));
        $this->assertStringNotContainsString('attachment', (string) $view->headers->get('content-disposition'));

        $download = $this->get('http://localhost/uploads/pasante/reporte.pdf?download=1');
        $download->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $download->headers->get('content-type'));
        $this->assertStringContainsString('attachment', (string) $download->headers->get('content-disposition'));
        $this->assertStringContainsString('reporte.pdf', (string) $download->headers->get('content-disposition'));
    }
}
