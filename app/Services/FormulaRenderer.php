<?php

namespace App\Services;

class FormulaRenderer
{
    public function render(string $content): string
    {
        if (! $content) {
            return '';
        }

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        $rendered = preg_replace_callback(
            '/\[\[([^\]]+)\]\]/',
            function ($matches) {
                $formula = $matches[1];
                $escaped = htmlspecialchars($formula, ENT_QUOTES, 'UTF-8');

                return sprintf(
                    '<span class="math-formula" data-formula="%s" data-katex="%s"></span>',
                    $escaped,
                    $escaped
                );
            },
            $content
        );

        return $rendered;
    }

    public function stripParagraphTags(string $content): string
    {
        if (! $content) {
            return '';
        }

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $content = trim($content);

        if (str_starts_with($content, '<p>') && str_ends_with($content, '</p>')) {
            $content = substr($content, 3, -4);
        }

        return $content;
    }

    public function getKaTeXScripts(): string
    {
        return <<<'HTML'
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
        <script>
            window.renderMathFormulas = function() {
                document.querySelectorAll('[data-katex]').forEach(function(el) {
                    const formula = el.getAttribute('data-katex');
                    if (window.katex && formula && !el.dataset.rendered) {
                        try {
                            const html = window.katex.renderToString(formula, { throwOnError: true });
                            el.innerHTML = html;
                            el.dataset.rendered = '1';
                        } catch (e) {
                            el.innerHTML = '<span style="color: #dc2626; font-size: 0.875rem;">❌ ' + formula + '</span>';
                            el.dataset.rendered = '1';
                        }
                    }
                });
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', window.renderMathFormulas);
            } else {
                window.renderMathFormulas();
            }
        </script>
        HTML;
    }
}
