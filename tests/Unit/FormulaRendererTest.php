<?php

namespace Tests\Unit;

use App\Services\FormulaRenderer;
use PHPUnit\Framework\TestCase;

class FormulaRendererTest extends TestCase
{
    private FormulaRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new FormulaRenderer;
    }

    public function test_render_simple_text(): void
    {
        $result = $this->renderer->render('Hello World');
        $this->assertEquals('Hello World', $result);
    }

    public function test_render_paragraph_tag(): void
    {
        $result = $this->renderer->render('<p>Hello World</p>');
        $this->assertStringContainsString('Hello World', $result);
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('</p>', $result);
    }

    public function test_render_bold_tag(): void
    {
        $result = $this->renderer->render('<p>Hello <b>World</b></p>');
        $this->assertStringContainsString('<b>World</b>', $result);
        $this->assertStringContainsString('<p>', $result);
    }

    public function test_render_italic_tag(): void
    {
        $result = $this->renderer->render('<p>Hello <i>World</i></p>');
        $this->assertStringContainsString('<i>World</i>', $result);
        $this->assertStringContainsString('<p>', $result);
    }

    public function test_render_formula_basic(): void
    {
        $result = $this->renderer->render('2[[\\frac{1}{6}]]');
        $this->assertStringContainsString('data-katex=', $result);
        $this->assertStringContainsString('\\frac{1}{6}', $result);
    }

    public function test_render_formula_with_paragraph(): void
    {
        $result = $this->renderer->render('<p>2[[\\frac{1}{6}]]</p>');
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('data-katex=', $result);
    }

    public function test_render_mixed_tags_and_formula(): void
    {
        $result = $this->renderer->render('<p>Hasil dari <b>2[[\\frac{1}{6}]]</b> adalah:</p>');
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<b>', $result);
        $this->assertStringContainsString('data-katex=', $result);
    }

    public function test_render_multiple_formulas(): void
    {
        $result = $this->renderer->render('<p>[[\\frac{1}{2}]] + [[\\frac{1}{3}]] = [[\\frac{5}{6}]]</p>');
        $this->assertStringContainsString('data-katex=', $result);
        preg_match_all('/data-katex=/', $result, $matches);
        $this->assertCount(3, $matches[0]);
    }

    public function test_render_complex_scenario(): void
    {
        $html = '<p>Soal: <i>Hitunglah</i> <b>[[\\frac{3}{5}]]</b> + [[\\frac{2}{5}]]</p>';
        $result = $this->renderer->render($html);

        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<i>Hitunglah</i>', $result);
        $this->assertStringContainsString('<b>', $result);
        preg_match_all('/data-katex=/', $result, $matches);
        $this->assertCount(2, $matches[0]);
    }

    public function test_render_empty_string(): void
    {
        $result = $this->renderer->render('');
        $this->assertEquals('', $result);
    }

    public function test_render_null(): void
    {
        $result = $this->renderer->render('');
        $this->assertEquals('', $result);
    }

    public function test_render_escaped_html(): void
    {
        $result = $this->renderer->render('&lt;p&gt;Hello&lt;/p&gt;');
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function test_render_escaped_bold(): void
    {
        $result = $this->renderer->render('&lt;b&gt;Bold Text&lt;/b&gt;');
        $this->assertStringContainsString('<b>', $result);
        $this->assertStringContainsString('Bold Text', $result);
    }

    public function test_render_escaped_italic(): void
    {
        $result = $this->renderer->render('&lt;i&gt;Italic Text&lt;/i&gt;');
        $this->assertStringContainsString('<i>', $result);
        $this->assertStringContainsString('Italic Text', $result);
    }

    public function test_render_escaped_with_formula(): void
    {
        $result = $this->renderer->render('&lt;p&gt;2[[\\frac{1}{6}]]&lt;/p&gt;');
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('data-katex=', $result);
    }

    public function test_render_escaped_complex(): void
    {
        $html = '&lt;p&gt;Soal: &lt;i&gt;Hitunglah&lt;/i&gt; &lt;b&gt;[[\\frac{3}{5}]]&lt;/b&gt; + [[\\frac{2}{5}]]&lt;/p&gt;';
        $result = $this->renderer->render($html);

        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<i>Hitunglah</i>', $result);
        $this->assertStringContainsString('<b>', $result);
        preg_match_all('/data-katex=/', $result, $matches);
        $this->assertCount(2, $matches[0]);
    }
}
