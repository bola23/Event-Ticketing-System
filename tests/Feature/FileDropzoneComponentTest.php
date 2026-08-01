<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FileDropzoneComponentTest extends TestCase
{
    public function test_renders_file_input_with_name_and_accept(): void
    {
        $html = view('components.file-dropzone', ['name' => 'cv_file', 'accept' => '.pdf,.doc,.docx'])->render();

        $this->assertStringContainsString('name="cv_file"', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('accept=".pdf,.doc,.docx"', $html);
    }

    public function test_renders_without_accept_attribute_when_omitted(): void
    {
        $html = view('components.file-dropzone', ['name' => 'portfolio_file'])->render();

        $this->assertStringContainsString('name="portfolio_file"', $html);
        $this->assertStringNotContainsString('accept=', $html);
    }

    public function test_renders_label_when_given(): void
    {
        $html = view('components.file-dropzone', ['name' => 'cv_file', 'label' => 'Upload your CV'])->render();

        $this->assertStringContainsString('Upload your CV', $html);
    }
}
