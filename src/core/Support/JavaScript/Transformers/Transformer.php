<?php namespace Naraki\Core\Support\JavaScript\Transformers;

use Naraki\Core\Support\JavaScript\ViewBinder;
use Exception;

class Transformer
{
    /**
     * The namespace to nest JS variables under.
     *
     * @var string
     */
    protected $namespace;

    /**
     * What binds the variables to the views.
     *
     * @var ViewBinder
     */
    protected $viewBinder;

    protected $vars = [];

    /**
     * All transformable types.
     *
     * @var array
     */
    protected $transformers = [
        StringTransformer::class,
        ArrayTransformer::class,
        ObjectTransformer::class,
        NumericTransformer::class,
        BooleanTransformer::class,
        NullTransformer::class
    ];

    /**
     * Create a new JS transformer instance.
     *
     * @param ViewBinder $viewBinder
     * @param string $namespace
     */
    function __construct(ViewBinder $viewBinder, $namespace = 'window')
    {
        $this->viewBinder = $viewBinder;
        $this->namespace = $namespace;
    }

    /**
     * Bind the given array of variables to the view.
     */
    public function bindJsVariablesToView()
    {
        if (empty($this->vars)) {
            return;
        }
        $js = $this->constructNamespace();

        foreach ($this->vars as $name => $value) {
            $js .= $this->initializeVariable($name, $value);
        }
        $this->viewBinder->bind($js);
    }

    public function put($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function putArray(Array $array)
    {
        $this->vars = array_merge($this->vars,$array);
    }

    /**
     * Create the namespace to which all vars are nested.
     *
     * @return string
     */
    protected function constructNamespace()
    {
        return "window.{$this->namespace} = window.{$this->namespace} || {};";
    }

    /**
     * Translate a single PHP var to JS.
     *
     * @param string $key
     * @param string $value
     * @return string
     * @throws \Exception
     */
    private function initializeVariable($key, $value)
    {
        return "{$this->namespace}.{$key} = {$this->convertToJavaScript($value)};";
    }

    /**
     * Format a value for JavaScript.
     *
     * @param  string $value
     * @throws Exception
     * @return string
     */
    protected function convertToJavaScript($value)
    {
        foreach ($this->transformers as $transformer) {
            $js = (new $transformer)->transform($value);
            if (!is_null($js)) {
                return $js;
            }
        }
    }

    /**
     * Normalize the input arguments.
     *
     * @param  mixed $arguments
     * @return array
     * @throws \Exception
     */
    protected function normalizeInput($arguments)
    {
        if (is_array($arguments[0])) {
            return $arguments[0];
        }

        if (count($arguments) == 2) {
            return [$arguments[0] => $arguments[1]];
        }

        throw new Exception('Try JavaScript::put(["foo" => "bar"])');
    }
}