<?php namespace Naraki\Blog\Support\Presenters;

use Naraki\Core\Presenter;
use Illuminate\Support\Str;

class BlogPost extends Presenter
{
    public function date()
    {
        return new \Carbon\Carbon($this->entity->getAttribute('date'));
    }

    public function title()
    {
        return Str::limit($this->entity->getAttribute('title'));
    }

    public function content()
    {
        $string = $this->entity->getAttribute('content');
        $methods = array_flip(get_class_methods(self::class));
        $replacements = $matches = [];
        preg_match_all('/(?<=\[\[).*(?=\]\])/U', $string, $bbCodeMatches);
        preg_match_all('/(?<=```).*(?=```)/Us', $string, $rawCodeMatches);

        if (count($bbCodeMatches[0]) > 0) {
            foreach ($bbCodeMatches[0] as $bb) {
                $p = explode('|', $bb, 4);
                if (isset($methods[$p[0]])) {
                    $replacements[] = $this->{array_shift($p)}($p);
                } else {
                    $replacements[] = $bb;
                }
            }
        }
        if (count($rawCodeMatches[0]) > 0) {
            foreach ($rawCodeMatches[0] as $bb) {
                $p = explode('|', $bb, 2);
                $replacements[] = $this->code($p);
            }
        }
        if (!empty($replacements)) {
            return str_replace(['[[', ']]', '```'], '', str_replace(
                    array_merge($bbCodeMatches[0], $rawCodeMatches[0]),
                    $replacements,
                    $string
                )
            );
        }
        return $string;
    }

   private function link($d)
    {
        return sprintf('<a href="%s">%s</a>', isset($d[1]) ? $d[1] : '', isset($d[0]) ? $d[0] : '');
    }

   private function image($d)
    {
        return sprintf('<figure class="img-embed"><img class="lazy" 
src="%s" data-src="%s" alt="%s"/><figcaption>%s</figcaption>
</figure>',
            placeholder_image(),
            isset($d[2]) ? $d[2] : '',
            isset($d[0]) ? $d[0] : '',
            isset($d[1]) ? $d[1] : ''
        );
    }

   private function code($d)
    {
        return sprintf('<pre class="prettyprint linenums %s">%s</pre>',
            isset($d[0]) ? 'lang-' . $d[0] : '',
            $d[1]
        );
    }

   private function maps($d)
    {
        preg_match('/^https?\:\/\/(?:www\.)?google\.[a-z]+\/maps\/(.*)/', $d[0], $m);
        if (isset($m[1])) {
            return sprintf('<iframe src="https://www.google.com/maps/embed/v1/%s" 
frameborder="0" allowfullscreen></iframe>',
                $m[1]);
        }
        return $d;

    }

   private function youtube($d)
    {
        preg_match('/http(?:s?):\/\/(?:www\.)?youtu(?:be\.com\/watch\?v=|\.be\/)([\w\-\_]*)(&(amp;)?‌​[\w\?‌​=]*)?/',
            $d[0], $m);
        if (isset($m[1])) {
            return sprintf('<div class="embed-container">
<iframe src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>
</div>',
                $m[1]);
        }
        return $d;
    }

   private function tweet($d)
    {
        $twitterIdPosition = strrpos($d[0], '/');
        if ($twitterIdPosition !== false) {
            return sprintf('<div id="tweet-%s" class="twitter-container"></div>',
                substr($d[0], $twitterIdPosition + 1));
        }
        return $d;
    }

}