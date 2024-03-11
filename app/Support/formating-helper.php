<?php

/**
 * Escaping for HTML attributes.
 *
 * @since 1.0.0
 *
 * @param string $text
 * @return string
 */
if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        $safe_text = check_invalid_utf8($text);
        $safe_text = _specialchars($safe_text, ENT_QUOTES);
        return $safe_text;
    }
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing slash if it exists already before adding a trailing
 * slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 1.2.0
 * @uses untrailingslashit() Unslashes string if it was slashed already.
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
if (!function_exists('trailingslashit')) {
    function trailingslashit($string)
    {
        return untrailingslashit($string) . '/';
    }
}

/**
 * Removes trailing slash if it exists.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.2.0
 *
 * @param string $string What to remove the trailing slash from.
 * @return string String without the trailing slash.
 */

if (!function_exists('untrailingslashit'))
{
    function untrailingslashit($string)
    {
        return rtrim($string, '/');
    }
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 2.0.0
 *
 * @param mixed $value The value to be stripped.
 * @return mixed Stripped value.
 */

if (!function_exists('stripslashes_deep'))
{
    function stripslashes_deep($value)
    {
        if (is_array($value)) {
            $value = array_map('stripslashes_deep', $value);
        } elseif (is_object($value)) {
            $vars = get_object_vars($value);
            foreach ($vars as $key => $data) {
                $value->{$key} = stripslashes_deep($data);
            }
        } elseif (is_string($value)) {
            $value = stripslashes($value);
        }

        return $value;
    }
}

/**
 * Checks for invalid UTF8 in a string.
 *
 * @since 2.8
 *
 * @param string $string The text which is to be checked.
 * @param boolean $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
 * @return string The checked text.
 */

if (!function_exists('check_invalid_utf8'))
{
    function check_invalid_utf8($string, $strip = false)
    {
        $string = (string) $string;

        if (0 === strlen($string)) {
            return '';
        }

        // Store the site charset as a static to avoid multiple calls to get_option()
        static $is_utf8;
        if (!isset($is_utf8)) {
            $is_utf8 = in_array('utf8', array('utf8', 'utf-8', 'UTF8', 'UTF-8'));
        }
        if (!$is_utf8) {
            return $string;
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static
        static $utf8_pcre;
        if (!isset($utf8_pcre)) {
            $utf8_pcre = @preg_match('/^./u', 'a');
        }
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases
        if (!$utf8_pcre) {
            return $string;
        }

        // preg_match fails when it encounters invalid UTF8 in $string
        if (1 === @preg_match('/^./us', $string)) {
            return $string;
        }

        // Attempt to strip the bad chars if requested (not recommended)
        if ($strip && function_exists('iconv')) {
            return iconv('utf-8', 'utf-8', $string);
        }

        return '';
    }
}

/**
 * Converts a number of HTML entities into their special characters.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to decode " entities,
 * or ENT_QUOTES to do both " and '. Default is ENT_NOQUOTES where no quotes are decoded.
 *
 * @since 1.0
 *
 * @param string $string The text which is to be decoded.
 * @param mixed $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old _wp_specialchars() values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
 * @return string The decoded text without HTML entities.
 */

if (!function_exists('specialchars_decode'))
{
    function specialchars_decode($string, $quote_style = ENT_NOQUOTES)
    {
        $string = (string) $string;

        if (0 === strlen($string)) {
            return '';
        }

        // Don't bother if there are no entities - saves a lot of processing
        if (strpos($string, '&') === false) {
            return $string;
        }

        // Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        // More complete than get_html_translation_table( HTML_SPECIALCHARS )
        $single      = array('&#039;' => '\'', '&#x27;' => '\'');
        $single_preg = array('/&#0*39;/' => '&#039;', '/&#x0*27;/i' => '&#x27;');
        $double      = array('&quot;' => '"', '&#034;' => '"', '&#x22;' => '"');
        $double_preg = array('/&#0*34;/' => '&#034;', '/&#x0*22;/i' => '&#x22;');
        $others      = array('&lt;' => '<', '&#060;' => '<', '&gt;' => '>', '&#062;' => '>', '&amp;' => '&', '&#038;' => '&', '&#x26;' => '&');
        $others_preg = array('/&#0*60;/' => '&#060;', '/&#0*62;/' => '&#062;', '/&#0*38;/' => '&#038;', '/&#x0*26;/i' => '&#x26;');

        if ($quote_style === ENT_QUOTES) {
            $translation      = array_merge($single, $double, $others);
            $translation_preg = array_merge($single_preg, $double_preg, $others_preg);
        } elseif ($quote_style === ENT_COMPAT || $quote_style === 'double') {
            $translation      = array_merge($double, $others);
            $translation_preg = array_merge($double_preg, $others_preg);
        } elseif ($quote_style === 'single') {
            $translation      = array_merge($single, $others);
            $translation_preg = array_merge($single_preg, $others_preg);
        } elseif ($quote_style === ENT_NOQUOTES) {
            $translation      = $others;
            $translation_preg = $others_preg;
        }

        // Remove zero padding on numeric entities
        $string = preg_replace(array_keys($translation_preg), array_values($translation_preg), $string);

        // Replace characters according to translation table
        return strtr($string, $translation);
    }
}

/**
 * Converts a number of special characters into their HTML entities.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to encode " to
 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
 *
 * @since 1.2.2
 *
 * @param string $string The text which is to be encoded.
 * @param mixed $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
 * @param string $charset Optional. The character encoding of the string. Default is false.
 * @param boolean $double_encode Optional. Whether to encode existing html entities. Default is false.
 * @return string The encoded text with HTML entities.
 */
if (!function_exists('_specialchars'))
{
    function _specialchars($string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false)
    {
        $string = (string) $string;

        if (0 === strlen($string)) {
            return '';
        }

        // Don't bother if there are no specialchars - saves some processing
        if (!preg_match('/[&<>"\']/', $string)) {
            return $string;
        }

        // Account for the previous behaviour of the function when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        $charset = 'UTF-8';

        $_quote_style = $quote_style;

        if ($quote_style === 'double') {
            $quote_style  = ENT_COMPAT;
            $_quote_style = ENT_COMPAT;
        } elseif ($quote_style === 'single') {
            $quote_style = ENT_NOQUOTES;
        }

        // Handle double encoding ourselves
        if ($double_encode) {
            $string = @htmlspecialchars($string, $quote_style, $charset);
        } else {
            // Decode &amp; into &
            $string = specialchars_decode($string, $_quote_style);

            // Now re-encode everything except &entity;
            $string = preg_split('/(&#?x?[0-9a-z]+;)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

            for ($i = 0; $i < count($string); $i += 2) {
                $string[$i] = @htmlspecialchars($string[$i], $quote_style, $charset);
            }

            $string = implode('', $string);
        }

        // Backwards compatibility
        if ('single' === $_quote_style) {
            $string = str_replace("'", '&#039;', $string);
        }

        return $string;
    }
}

/**
 * Maybe unserialize functionality
 *
 * @return mixed
 */
if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($data)
    {
        if (is_serialized($data)) {
            return @unserialize($data);
        }
        return $data;
    }
}

/**
 * Maybe serialize functionality
 *
 * @return string
 */
if (!function_exists('maybe_serialize'))
{
  function maybe_serialize($data)
  {
      if (is_array($data) || is_object($data)) {
          return serialize($data);
      }

      // Double serialization is required for backward compatibility.
      // See https://core.trac.wordpress.org/ticket/12930
      if (is_serialized($data, false)) {
          return serialize($data);
      }

      return $data;
  }
}

/**
 * Check string whether serialize or not
 *
 * @return boolean
 */
if (!function_exists('is_serialized'))
{
    function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }

            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }

            if (false !== $brace && $brace < 4) {
                return false;
            }

        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}
