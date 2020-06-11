<?php


namespace Mail\EmailManagerBundle\Utils;

/**
 * Class HtmlToText
 * @package Mail\EmailManagerBundle\Utils
 */
class HtmlToText
{
    /**
     * transform $p_html to plaintext and return it
     * $p_tagsAndContents ex : '<style><head><script>'
     * @param $p_html
     * @param $p_tagsAndContents
     * @return string
     */
    public function htmlToText($p_html, $p_tagsAndContents = '')
    {
        $html = str_replace(' ' , '' , $p_html);
        $html = strip_tags($html,$p_tagsAndContents);
        $html = preg_replace(
                '@<(\w+)\b.*?>.*?</\1>@si',
                '',
                $html
            );
        return preg_replace("#(\r\n|\n\r|\n|\r)+#", "\n",$html);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mail.utils.html_to_text';
    }
}