<?php

/*
 * Sanitizes HTML submitted through the product description editor.
 *
 * Only basic formatting elements needed by the CMS are retained.
 * Dangerous elements, event attributes, styles, and unsafe links
 * are removed before the content is stored in the database.
 */
function sanitizeDescription(string $html): string
{
    $html = trim($html);

    if ($html === "") {
        return "";
    }

    /*
     * Remove elements that should never be accepted.
     */
    $html = preg_replace(
        [
            '#<script\b[^>]*>.*?</script>#is',
            '#<style\b[^>]*>.*?</style>#is',
            '#<iframe\b[^>]*>.*?</iframe>#is',
            '#<object\b[^>]*>.*?</object>#is',
            '#<embed\b[^>]*>.*?</embed>#is',
            '#<form\b[^>]*>.*?</form>#is'
        ],
        "",
        $html
    );

    /*
     * Retain only the formatting elements supported by
     * the product-description editor.
     */
    $allowed_tags =
        "<p><br><strong><b><em><i>" .
        "<h2><h3><h4>" .
        "<ul><ol><li>" .
        "<blockquote><a>";

    $html = strip_tags($html, $allowed_tags);

    /*
     * Remove inline event handlers such as onclick,
     * onerror, and onmouseover.
     */
    $html = preg_replace(
        '/\s+on[a-z]+\s*=\s*(["\']).*?\1/isu',
        "",
        $html
    );

    $html = preg_replace(
        '/\s+on[a-z]+\s*=\s*[^\s>]+/isu',
        "",
        $html
    );

    /*
     * Remove style and class attributes so users cannot
     * inject their own page styling.
     */
    $html = preg_replace(
        '/\s+(style|class|id)\s*=\s*(["\']).*?\2/isu',
        "",
        $html
    );

    /*
     * Remove unsafe link protocols.
     */
    $html = preg_replace_callback(
        '/<a\b([^>]*)>/isu',
        function (array $matches): string {
            $attributes = $matches[1];

            if (
                preg_match(
                    '/href\s*=\s*(["\'])(.*?)\1/isu',
                    $attributes,
                    $href_match
                )
            ) {
                $href = trim(
                    html_entity_decode(
                        $href_match[2],
                        ENT_QUOTES | ENT_HTML5,
                        "UTF-8"
                    )
                );

                $is_safe =
                    preg_match(
                        '#^(https?://|mailto:|tel:|/|\#)#i',
                        $href
                    ) === 1;

                if (!$is_safe) {
                    return "<a>";
                }

                $safe_href = htmlspecialchars(
                    $href,
                    ENT_QUOTES,
                    "UTF-8"
                );

                return
                    '<a href="' . $safe_href . '"' .
                    ' target="_blank"' .
                    ' rel="noopener noreferrer">';
            }

            return "<a>";
        },
        $html
    );

    return trim($html);
}

/*
 * Returns the readable text from the formatted description.
 * This is useful for validation because HTML tags should not
 * count toward the description's length.
 */
function getDescriptionText(string $html): string
{
    $text = strip_tags($html);
    $text = html_entity_decode(
        $text,
        ENT_QUOTES | ENT_HTML5,
        "UTF-8"
    );

    return trim(
        preg_replace('/\s+/u', " ", $text)
    );
}