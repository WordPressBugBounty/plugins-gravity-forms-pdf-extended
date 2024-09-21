<?php

namespace GFPDF_Vendor\Mpdf\Language;

use GFPDF_Vendor\Mpdf\Ucdn;
class ScriptToLanguage implements \GFPDF_Vendor\Mpdf\Language\ScriptToLanguageInterface
{
    private $scriptDelimiterMap = [
        'viet' => "\\x{01A0}\\x{01A1}\\x{01AF}\\x{01B0}\\x{1EA0}-\\x{1EF1}",
        'persian' => "\\x{067E}\\x{0686}\\x{0698}\\x{06AF}",
        'urdu' => "\\x{0679}\\x{0688}\\x{0691}\\x{06BA}\\x{06BE}\\x{06C1}\\x{06D2}",
        'pashto' => "\\x{067C}\\x{0681}\\x{0685}\\x{0689}\\x{0693}\\x{0696}\\x{069A}\\x{06BC}\\x{06D0}",
        // ? and U+06AB, U+06CD
        'sindhi' => "\\x{067A}\\x{067B}\\x{067D}\\x{067F}\\x{0680}\\x{0684}\\x{068D}\\x{068A}\\x{068F}\\x{068C}\\x{0687}\\x{0683}\\x{0699}\\x{06AA}\\x{06A6}\\x{06BB}\\x{06B1}\\x{06B3}",
    ];
    private $scriptToLanguageMap = [
        /* European */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LATIN => 'und-Latn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_ARMENIAN => 'hy',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CYRILLIC => 'und-Cyrl',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GEORGIAN => 'ka',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GREEK => 'el',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_COPTIC => 'cop',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GOTHIC => 'got',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CYPRIOT => 'und-Cprt',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GLAGOLITIC => 'und-Glag',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LINEAR_B => 'und-Linb',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OGHAM => 'und-Ogam',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OLD_ITALIC => 'und-Ital',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_RUNIC => 'und-Runr',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SHAVIAN => 'und-Shaw',
        /* African */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_ETHIOPIC => 'und-Ethi',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_NKO => 'nqo',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BAMUM => 'bax',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_VAI => 'vai',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_EGYPTIAN_HIEROGLYPHS => 'und-Egyp',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MEROITIC_CURSIVE => 'und-Merc',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MEROITIC_HIEROGLYPHS => 'und-Mero',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OSMANYA => 'und-Osma',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TIFINAGH => 'und-Tfng',
        /* Middle Eastern */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_ARABIC => 'und-Arab',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_HEBREW => 'he',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SYRIAC => 'syr',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_IMPERIAL_ARAMAIC => 'arc',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_AVESTAN => 'ae',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CARIAN => 'xcr',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LYCIAN => 'xlc',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LYDIAN => 'xld',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MANDAIC => 'mid',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OLD_PERSIAN => 'peo',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_PHOENICIAN => 'phn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SAMARITAN => 'smp',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_UGARITIC => 'uga',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CUNEIFORM => 'und-Xsux',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OLD_SOUTH_ARABIAN => 'und-Sarb',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_INSCRIPTIONAL_PARTHIAN => 'und-Prti',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_INSCRIPTIONAL_PAHLAVI => 'und-Phli',
        /* Central Asian */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MONGOLIAN => 'mn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TIBETAN => 'bo',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OLD_TURKIC => 'und-Orkh',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_PHAGS_PA => 'und-Phag',
        /* South Asian */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BENGALI => 'bn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_DEVANAGARI => 'hi',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GUJARATI => 'gu',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_GURMUKHI => 'pa',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KANNADA => 'kn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MALAYALAM => 'ml',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_ORIYA => 'or',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SINHALA => 'si',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAMIL => 'ta',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TELUGU => 'te',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CHAKMA => 'ccp',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LEPCHA => 'lep',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LIMBU => 'lif',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_OL_CHIKI => 'sat',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SAURASHTRA => 'saz',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SYLOTI_NAGRI => 'syl',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAKRI => 'dgo',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_THAANA => 'dv',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BRAHMI => 'und-Brah',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KAITHI => 'und-Kthi',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KHAROSHTHI => 'und-Khar',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MEETEI_MAYEK => 'und-Mtei',
        /* or omp-Mtei */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SHARADA => 'und-Shrd',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SORA_SOMPENG => 'und-Sora',
        /* South East Asian */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KHMER => 'km',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LAO => 'lo',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MYANMAR => 'my',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_THAI => 'th',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BALINESE => 'ban',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BATAK => 'bya',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BUGINESE => 'bug',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CHAM => 'cjm',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_JAVANESE => 'jv',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KAYAH_LI => 'und-Kali',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_REJANG => 'und-Rjng',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_SUNDANESE => 'su',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAI_LE => 'tdd',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAI_THAM => 'und-Lana',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAI_VIET => 'blt',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_NEW_TAI_LUE => 'und-Talu',
        /* Phillipine */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BUHID => 'bku',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_HANUNOO => 'hnn',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAGALOG => 'tl',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_TAGBANWA => 'tbw',
        /* East Asian */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_HAN => 'und-Hans',
        // und-Hans (simplified) or und-Hant (Traditional)
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_HANGUL => 'ko',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_HIRAGANA => 'ja',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_KATAKANA => 'ja',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_LISU => 'lis',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BOPOMOFO => 'und-Bopo',
        // zh-CN, zh-TW, zh-HK
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_MIAO => 'und-Plrd',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_YI => 'und-Yiii',
        /* American */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CHEROKEE => 'chr',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_CANADIAN_ABORIGINAL => 'cr',
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_DESERET => 'und-Dsrt',
        /* Other */
        \GFPDF_Vendor\Mpdf\Ucdn::SCRIPT_BRAILLE => 'und-Brai',
    ];
    public function getLanguageByScript($script)
    {
        return isset($this->scriptToLanguageMap[$script]) ? $this->scriptToLanguageMap[$script] : null;
    }
    public function getLanguageDelimiters($language)
    {
        return isset($this->scriptDelimiterMap[$language]) ? $this->scriptDelimiterMap[$language] : null;
    }
}
