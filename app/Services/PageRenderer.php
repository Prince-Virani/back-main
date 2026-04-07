<?php

namespace App\Services;

use App\Models\AdUnit;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use DOMDocument;

class PageRenderer
{
    public static function render(string $html, int $websiteId): string
    {
        try {
            $compiled = Blade::render($html, []);

            $inPageAds = AdUnit::where('website_id', $websiteId)
                ->where('status_flag', true)
                ->where('ad_unit_type', 'in_page')
                ->orderBy('in_page_position')
                ->get()
                ->values();

            if ($inPageAds->isEmpty()) {
                return $compiled;
            }

            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML(mb_convert_encoding($compiled, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $body = $doc->getElementsByTagName('body')->item(0);
            if (! $body) {
                return $compiled . self::renderRemainingAds($inPageAds);
            }

            $nodes = iterator_to_array($body->childNodes);
            $currentWords = 0;
            $output = '';

            $blockTags = [
                'p','h1','h2','h3','h4','h5','h6',
                'li','ul','ol','div','section','article',
                'blockquote','pre','figure','main','aside','header','footer','table'
            ];

            foreach ($nodes as $node) {
                $output .= $doc->saveHTML($node);

                if ($node->nodeType === XML_TEXT_NODE) {
                    $currentWords += str_word_count(trim($node->textContent));
                } elseif ($node->nodeType === XML_ELEMENT_NODE) {
                    $name = strtolower($node->nodeName);
                    if (in_array($name, $blockTags, true)) {
                        $currentWords += str_word_count(trim($node->textContent));
                    }
                }

                while ($inPageAds->first() && $currentWords >= (int) $inPageAds->first()->in_page_position) {
                    $adItem = $inPageAds->shift();
                    $output .= self::renderAd($adItem);
                }
            }

            if ($inPageAds->isNotEmpty()) {
                $output .= self::renderRemainingAds($inPageAds);
            }

            return $output;
        } catch (\Throwable $e) {
            Log::error("Rendering failed for website {$websiteId}: " . $e->getMessage());
            return $html;
        }
    }

    protected static function renderAd($adItem): string
    {
        try {
            $inPageId = "{$adItem->adunit_id}-inpage";
            return view('partial.gpt-ad', [
                'ad'         => $adItem,
                'overrideId' => $inPageId,
                'lazy'       => true,
            ])->render();
        } catch (\Throwable $e) {
            Log::error("Error rendering ad unit {$adItem->id}: " . $e->getMessage());
            return '';
        }
    }

    protected static function renderRemainingAds($ads): string
    {
        $html = '';
        foreach ($ads as $adItem) {
            $html .= self::renderAd($adItem);
        }
        return $html;
    }
}
