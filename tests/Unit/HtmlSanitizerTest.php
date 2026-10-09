<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_removes_script_tags_and_their_content(): void
    {
        $clean = HtmlSanitizer::clean('<p>Halo</p><script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringContainsString('Halo', $clean);
    }

    public function test_strips_event_handler_attributes(): void
    {
        $clean = HtmlSanitizer::clean('<p onclick="alert(1)" onmouseover="alert(2)">Teks</p>');

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringContainsString('Teks', $clean);
    }

    public function test_neutralizes_javascript_urls(): void
    {
        $clean = HtmlSanitizer::clean('<a href="javascript:alert(1)">Klik</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('Klik', $clean);
    }

    public function test_keeps_safe_formatting_tags(): void
    {
        $clean = HtmlSanitizer::clean('<p><strong>Tebal</strong> dan <em>miring</em></p><ul><li>Satu</li></ul>');

        $this->assertStringContainsString('<strong>Tebal</strong>', $clean);
        $this->assertStringContainsString('<em>miring</em>', $clean);
        $this->assertStringContainsString('<li>Satu</li>', $clean);
    }

    public function test_handles_empty_input(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean(''));
    }
}
