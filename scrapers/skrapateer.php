<?php /*This is my PHP scraping library
       ** fixQuotes() fixes the quotation marks using a specific pattern for every language
       ** sets the execution time limit to unlimited */

  //Defines how to use quotation marks for every language
  $quotePatterns = array(//Based on https://en.wikipedia.org/wiki/Quotation_mark#Languages
    'af' => array('“', '”', '‘', '’'),
    'sq' => array('„', '“', '‘', '’'),
    'am' => array('«', '»', '‹', '›'),
    'ar' => array('«', '»', '«', '»'),
    'hy' => array('«', '»', '«', '»'),
    'az' => array('“', '”', '\\"', '\\"'),//TODO: check if \\" is usable
    'eu' => array('«', '»', '“', '”'),
    'be' => array('«', '»', '„', '“'),
    'bs' => array('”', '”', '’', '’'),
    'bg' => array('„', '“', '‘', '’'),
    'ca' => array('«', '»', '“', '”'),
    'zh' => array('『', '』', '「', '」'),
    'hr' => array('„', '“', '‘', '’'),
    'cs' => array('„', '“', '‘', '’'),
    'da' => array('»', '«', '›', '‹'),
    'nl' => array('„', '”', '‚', '’'),
    'en' => array('“', '”', '‘', '’'),
    'et' => array('„', '“', '„', '“'),
    'tl' => array('“', '”', '‘', '’'),
    'fi' => array('”', '”', '’', '’'),
    'fr' => array('«', '»', '«', '»'),
    'fr_CH' => array('«', '»', '‹', '›'),
    'gl' => array('«', '»', '“', '”'),
    'ka' => array('„', '“', '‚', '‘'),
    'de' => array('„', '“', '‚', '‘'),
    'de_AT' => array('„', '“', '‚', '‘'),
    'de_CH' => array('«', '»', '‹', '›'),
    'el' => array('«', '»', '“', '”'),
    'he' => array('״', '״', '׳', '׳'),
    'hi' => array('“', '”', '‘', '’'),
    'hu' => array('„', '”', '»', '«'),
    'is' => array('„', '“', '‚', '‘'),
    'id' => array('“', '”', '‘', '’'),
    'ga' => array('“', '”', '‘', '’'),
    'it' => array('«', '»', '“', '”'),
    'it_CH' => array('«', '»', '‹', '›'),
    'ja' => array('「', '」', '『', '』'),
    'kz' => array('«', '»', '«', '»'),
    'km' => array('«', '»', '«', '»'),
    'ko' => array('《', '》', '〈', '〉'),
    'lo' => array('“', '”', '“', '”'),
    'lv' => array('„', '“', '‚', '‘'),
    'lt' => array('„', '“', '‘', '’'),
    'mk' => array('„', '“', '’', '‘'),
    'mt' => array('“', '”', '‘', '’'),
    'mn' => array('«', '»', '„', '“'),
    'no' => array('«', '»', '‘', '’'),
    'oc' => array('«', '»', '“', '”'),
    'ps' => array('«', '»', '«', '»'),
    'fa' => array('«', '»', '«', '»'),
    'pl' => array('„', '”', '»', '«'),
    'pt' => array('«', '»', '‘', '’'),
    'pt_BR' => array('“', '”', '‘', '’'),
    'ro' => array('„', '”', '«', '»'),
    'rm' => array('«', '»', '‹', '›'),
    'rm_CH' => array('«', '»', '‹', '›'),
    'ru' => array('«', '»', '„', '“'),
    'sr' => array('„', '”', '’', '’'),
    'gd' => array('“', '”', '‘', '’'),
    'sk' => array('»', '«', '›', '‹'),
    'sl' => array('»', '«', '›', '‹'),
    'es' => array('«', '»', '‘', '’'),
    'sv' => array('”', '”', '’', '’'),
    'ta' => array('“', '”', '‘', '’'),
    'bo' => array('《', '》', '〈', '〉'),
    'ti' => array('«', '»', '‹', '›'),
    'th' => array('“', '”', '‘', '’'),
    'tr' => array('“', '”', '‘', '’'),
    'uk' => array('«', '»', '‘', '’'),
    'ur' => array('“', '”', '‘', '’'),
    'uz' => array('«', '»', '‚', '‘'),
    'vi' => array('“', '”', '‘', '’'),
    'cy' => array('“', '”', '‘', '’'),
  );

  //Makes sure there are no quotes in the title
  function fixQuotes($string, $lang, $pattern = null) {
    //Imports the quotes as a global variable
    global $quotePatterns;
    if ($pattern == null) $pattern = $quotePatterns[$lang];

    //Continue with the function.
    $string = ' '.$string.' ';
    $string = strtr($string, array('""' => '"'));//Gets rid of double double quotes because that's just psychotic. Stupid Cyprus.
    $string = strtr($string,
      array(''=>$pattern[0], ''=>$pattern[1],
            ' "'=>' '.$pattern[0], '" '=>$pattern[1].' ', '("'=>'('.$pattern[0], '")'=>$pattern[1].')', '".'=>$pattern[1].'.',
            " '"=>' '.$pattern[2], "' "=>$pattern[3].' ', "('"=>'('.$pattern[2], "')"=>$pattern[3].')', "'."=>$pattern[3].'.',
            "'"=>"ꞌ"
      )
    );
    return trim($string);
  }

  //Redefines the maximum execution time of the PHP script
  ini_set('max_execution_time', 0);
?>