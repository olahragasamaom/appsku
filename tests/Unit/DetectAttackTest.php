<?php

namespace Tests\Unit;

use App\Http\Middleware\DetectAttack;
use PHPUnit\Framework\TestCase;

class DetectAttackTest extends TestCase
{
    private DetectAttack $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new DetectAttack;
    }

    private function isSafe(string $value): bool
    {
        return $this->middleware->isSafeHtmlContent($value);
    }

    public function test_safe_paragraph_tag(): void
    {
        $this->assertTrue($this->isSafe('<p>Hello World</p>'));
    }

    public function test_safe_bold_italic(): void
    {
        $this->assertTrue($this->isSafe('<p>Hello <b>bold</b> and <i>italic</i></p>'));
    }

    public function test_safe_image(): void
    {
        $this->assertTrue($this->isSafe('<p>Lihat <img src="/storage/test.png"></p>'));
    }

    public function test_unsafe_script_tag(): void
    {
        $this->assertFalse($this->isSafe('<script>alert(1)</script>'));
    }

    public function test_unsafe_iframe(): void
    {
        $this->assertFalse($this->isSafe('<iframe src="http://evil.com"></iframe>'));
    }

    public function test_unsafe_anchor_tag(): void
    {
        $this->assertFalse($this->isSafe('<a href="javascript:alert(1)">click</a>'));
    }

    public function test_unsafe_onclick_handler(): void
    {
        $this->assertFalse($this->isSafe('<p onclick="alert(1)">hover me</p>'));
    }

    public function test_unsafe_javascript_url(): void
    {
        $this->assertFalse($this->isSafe('<p>text javascript:alert(1)</p>'));
    }

    public function test_plain_text_not_html(): void
    {
        $this->assertFalse($this->isSafe('Just plain text with Don\'t and # characters'));
    }

    public function test_safe_list_tags(): void
    {
        $this->assertTrue($this->isSafe('<ul><li>Item 1</li><li>Item 2</li></ul>'));
    }

    public function test_mixed_safe_and_unsafe(): void
    {
        $this->assertFalse($this->isSafe('<p>Hello</p><script>alert(1)</script>'));
    }
}
