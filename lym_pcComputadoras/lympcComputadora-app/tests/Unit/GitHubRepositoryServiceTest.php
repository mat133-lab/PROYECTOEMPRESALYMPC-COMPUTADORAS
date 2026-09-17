<?php

namespace Tests\Unit;

use App\Services\GitHubRepositoryService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class GitHubRepositoryServiceTest extends TestCase
{
    public function test_it_publishes_a_file_to_the_configured_repository(): void
    {
        config([
            'services.github.token' => 'github_pat_test',
            'services.github.api_url' => 'https://api.github.com',
            'services.github.directory' => 'uploads/pasantes',
            'services.github.max_file_bytes' => 1024,
        ]);

        Http::fake([
            'https://api.github.com/repos/acme/demo' => Http::response(['id' => 1], 200),
            'https://api.github.com/repos/acme/demo/contents/uploads/pasantes/project.zip' => Http::response([
                'content' => ['html_url' => 'https://github.com/acme/demo/blob/main/uploads/pasantes/project.zip'],
                'commit' => ['sha' => 'abc123'],
            ], 201),
        ]);

        $file = tempnam(sys_get_temp_dir(), 'github-test-');
        file_put_contents($file, 'project contents');

        try {
            $result = app(GitHubRepositoryService::class)->publish(
                'https://github.com/acme/demo.git',
                $file,
                'project.zip',
            );
        } finally {
            @unlink($file);
        }

        $this->assertSame('acme', $result['owner']);
        $this->assertSame('demo', $result['repository']);
        $this->assertSame('uploads/pasantes/project.zip', $result['path']);
        $this->assertSame('abc123', $result['commit_sha']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'PUT'
                && $request->url() === 'https://api.github.com/repos/acme/demo/contents/uploads/pasantes/project.zip'
                && $request['branch'] === 'main'
                && $request['content'] === base64_encode('project contents');
        });
    }

    public function test_it_rejects_non_github_repository_urls(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GitHubRepositoryService())->parseRepositoryUrl('https://example.com/acme/demo');
    }
}
