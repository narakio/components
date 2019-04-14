<?php namespace Naraki\Core\Support\Requests\Detectors;

class Profanity
{
    private $string;
    private $locale;
    private $badWords;
    private $regexPattern;

    private function __construct($string, $locale)
    {
        $this->string = $string;
        $this->locale = $locale;
        $this->generateRegexPattern();
    }

    public static function detect($string, $locale)
    {
        return (new self($string, $locale))->check();
    }

    private function check()
    {
        $match = null;
        foreach ($this->regexPattern as $pattern) {
            $matches = preg_match(
                $pattern,
                $this->string,
                $m
            );
            if ($matches===1) {
                $match = $m;
                break;
            }
        }
        return $match ?? false;

    }

    private function generateRegexPattern()
    {
        $this->badWords = @include(sprintf('%s/dic/%s.php', __DIR__, $this->locale));
        if ($this->badWords === false) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Profanity checker does not have a dictionary for locale "%s"',
                    $this->locale
                )
            );
        }
        $leet_replace = [];
        $leet_replace['a'] = '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)';
        $leet_replace['b'] = '(b|b\.|b\-|8|\|3|ß|Β|β)';
        $leet_replace['c'] = '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)';
        $leet_replace['d'] = '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)';
        $leet_replace['e'] = '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)';
        $leet_replace['f'] = '(f|f\.|f\-|ƒ)';
        $leet_replace['g'] = '(g|g\.|g\-|6|9)';
        $leet_replace['h'] = '(h|h\.|h\-|Η)';
        $leet_replace['i'] = '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)';
        $leet_replace['j'] = '(j|j\.|j\-)';
        $leet_replace['k'] = '(k|k\.|k\-|Κ|κ)';
        $leet_replace['l'] = '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)';
        $leet_replace['m'] = '(m|m\.|m\-)';
        $leet_replace['n'] = '(n|n\.|n\-|η|Ν|Π)';
        $leet_replace['o'] = '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)';
        $leet_replace['p'] = '(p|p\.|p\-|ρ|Ρ|¶|þ)';
        $leet_replace['q'] = '(q|q\.|q\-)';
        $leet_replace['r'] = '(r|r\.|r\-|®)';
        $leet_replace['s'] = '(s|s\.|s\-|5|\$|§)';
        $leet_replace['t'] = '(t|t\.|t\-|Τ|τ|7)';
        $leet_replace['u'] = '(u|u\.|u\-|υ|µ)';
        $leet_replace['v'] = '(v|v\.|v\-|υ|ν)';
        $leet_replace['w'] = '(w|w\.|w\-|ω|ψ|Ψ)';
        $leet_replace['x'] = '(x|x\.|x\-|Χ|χ)';
        $leet_replace['y'] = '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)';
        $leet_replace['z'] = '(z|z\.|z\-|Ζ)';
        $this->regexPattern = [];
        $cntBadWords = count($this->badWords);
        for ($x = 0, $xMax = $cntBadWords; $x < $xMax; $x++) {
            $this->regexPattern[$x] = '/' . str_ireplace(array_keys($leet_replace), array_values($leet_replace),
                    $this->badWords[$x]) . '/';
        }
    }

}