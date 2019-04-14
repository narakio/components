<?php namespace Naraki\Core\Support\Frontend\Jsonld;

class JsonLd
{
    /**
     * Context type
     *
     * @var \Naraki\Core\Support\Frontend\Jsonld\Schema
     */
    protected $schema = null;

    /**
     * Create a new Context instance
     *
     * @param string $schema
     * @param array $data
     */
    public function __construct($schema, array $data = [])
    {
        $this->schema = new $schema($data, true);
    }

    /**
     * Present given data as a JSON-LD object.
     *
     * @param string $context
     * @param array $data
     *
     * @return static
     */
    public static function createOne($context, array $data = [])
    {
        return new static($context, $data);
    }

    public static function create(array $data)
    {
        $r = [];
        foreach ($data as $d) {
            $r[] = self::createOne($d->class, $d->jsonld);
        }
        return $r;
    }

    public static function generate(array $data)
    {
        $r = '';
        foreach ($data as $d) {
            $r .= self::createOne($d->class, $d->jsonld)->generateOne();
        }
        return $r;
    }

    /**
     * Generate the JSON-LD script tag.
     *
     * @return string
     */
    public function generateOne()
    {
        return sprintf('<script type="application/ld+json">%s</script>', $this->getJson());
    }

    /**
     * Return script tag.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->generate();
    }

    /**
     * @return false|string
     */
    public function getJson()
    {
        return json_encode($this->schema->getProperties(), JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
    }
}
