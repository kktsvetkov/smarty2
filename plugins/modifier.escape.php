<?php

/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  Escape the string according to escapement type
 * @param string $subject
 * @param string $esc_type html|htmlall|url|quotes|hex|hexentity|javascript
 * @param string $char_set
 * @return string
 */
function smarty_modifier_escape($subject, $esc_type = 'html', $char_set = 'ISO-8859-1')
{
        // escape only objects that can be made into strings
        //
        if (is_object($subject))
        {
                if (is_callable([$subject, '__toString']))
                {
                        $subject = $subject->__toString();
                } else
                {
                        return $subject;
                }
        }

        // escape each array member individually
        //
        if (is_array($subject))
        {
                foreach ($subject as &$element)
                {
                        $element = smarty_modifier_escape(
                                $element,
                                $esc_type,
                                $char_set);
                }

                return $subject;
        }

        $subject = (string) $subject;
        if (!$subject)
        {
                return '';
        }

        switch ($esc_type)
        {
                case 'html':
                        return htmlspecialchars($subject, ENT_QUOTES, $char_set);

                case 'htmlall':
                        return htmlentities($subject, ENT_QUOTES, $char_set);

                case 'url':
                        return rawurlencode($subject);

                case 'urlpathinfo':
                        return str_replace('%2F','/',rawurlencode($subject));

                case 'quotes':
                        // escape unescaped single quotes
                        return preg_replace("%(?<!\\\\)'%", "\\'", $subject);

                case 'hex':
                        // escape every character into hex
                        $return = '';
                        for ($x=0; $x < strlen($subject); $x++)
                        {
                                $return .= '%' . bin2hex($subject[$x]);
                        }
                        return $return;

                case 'hexentity':
                        $return = '';
                        for ($x=0; $x < strlen($subject); $x++)
                        {
                                $return .= '&#x' . bin2hex($subject[$x]) . ';';
                        }
                        return $return;

                case 'decentity':
                        $return = '';
                        for ($x=0; $x < strlen($subject); $x++)
                        {
                                $return .= '&#' . ord($subject[$x]) . ';';
                        }
                        return $return;

                case 'javascript':
                        // escape quotes and backslashes, newlines, etc.
                        return strtr($subject, array(
                                '\\'=>'\\\\',
                                "'"=>"\\'",
                                '"'=>'\\"',
                                "\r"=>'\\r',
                                "\n"=>'\\n',
                                '</'=>'<\/'
                        ));

                case 'mail':
                        // safe way to display e-mail address on a web page
                        return str_replace(
                                array('@', '.'),
                                array(' [AT] ', ' [DOT] '),
                                $subject);

                case 'nonstd':
                        // escape non-standard chars, such as ms document quotes
                        $_res = '';
                        for($_i = 0, $_len = strlen($subject); $_i < $_len; $_i++)
                        {
                                $_ord = ord(substr($subject, $_i, 1));
                                // non-standard char, escape it
                                if($_ord >= 126)
                                {
                                        $_res .= '&#' . $_ord . ';';
                                } else
                                {
                                $_res .= substr($subject, $_i, 1);
                                }
                        }

                        return $_res;
        }

        return $subject;
}
