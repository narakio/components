<?php
require_once 'Product.php';
require_once 'Brand.php';
require_once 'ProductCategory.php';
require_once 'Packager.php';
require_once 'Ingredient.php';
require_once 'Nutriment.php';
require_once 'Image.php';
require_once 'Nutrient.php';
require_once 'Package.php';
require_once 'DBEntries.php';

class ProductRepo
{
    const LANGUAGE_ID_ENGLISH = 1;
    const LANGUAGE_ID_FRENCH = 44;
    /**
     * @var array
     */
    private $productIndex = [];
    /**
     * @var array
     */
    private $brandIndex = [];
    /**
     * @var int
     */
    private $brandId = 1;
    /**
     * @var array
     */
    private $categoryIndex = [];
    /**
     * @var int
     */
    private $categoryId = 1;
    /**
     * @var array
     */
    private $embCodeIndex = [];
    /**
     * @var array
     */
    private $ingredientsIndex = [];
    /**
     * @var int
     */
    private $ingredientId = 1;
    /**
     * @var array
     */
    private $nutrientLevelsIndex = [];
    /**
     * @var int
     */
    private $nutrientLevelId = 1;
    /**
     * @var array
     */
    private $nutrimentsIndex = [];
    /**
     * @var int
     */
    private $nutrimentId = 1;
    /**
     * @var array
     */
    private $packagesIndex = [];
    /**
     * @var int
     */
    private $packageId = 1;


    public function add(Product $product)
    {
        $this->productIndex[] = $product;
    }

    public function getProducts()
    {
        return array_values($this->productIndex);
    }

    public function flushProducts()
    {
        $f = $this->productIndex;
        unset($this->productIndex);
        $this->productIndex = [];
        return $f;
    }

    public function addBrands(string $brands): array
    {
        $id = 1;
        $tmp = explode(',', $brands);
        $result = [];
        foreach ($tmp as $brand) {
            if (strlen($brand) < 50) {
                $index = slugify(trim($brand));
                if (!isset($this->brandIndex[$index])) {
                    $this->brandIndex[$index] = new Brand($this->brandId,
                        ucfirst(trim(str_replace('’', '\'', $brand))));
                    $this->brandId++;
                }
                if (!isset($result[$index])) {
                    $result[$index] = $this->brandIndex[$index];
                }
            }
        }
        return array_values($result);
    }

    public function addEmbCodes(string $embCode)
    {
        if (isset($this->embCodeIndex[$embCode])) {
            return $this->embCodeIndex[$embCode];
        }
        return null;
    }

    public function addCategories($categories, $delimiter = ',')
    {
        $tmp = explode($delimiter, $categories);
        $result = [];

        foreach ($tmp as $category) {
            $lang = explode(':', $category);
            if (isset($lang[1])) {
                $lg = trim($lang[0]);
                if ($lg == 'en') {
                    $cat = ucwords(str_replace('-', ' ', $lang[1]));
                } else {
                    $cat = $lang[1];
                }
            } else {
                $cat = trim($lang[0]);
                $lg = 'fr';
            }
            switch ($lg) {
                case "en":
                case "fr":
                    if (strlen($cat) < 60) {
                        if (!isset($this->categoryIndex[$lg])) {
                            $this->categoryIndex[$lg] = array();
                        }
                        $index = slugify($cat);
                        if (!isset($this->categoryIndex[$lg][$index])) {
                            $this->categoryIndex[$lg][$index] = new ProductCategory($this->categoryId, $cat, $lg);
                            $this->categoryId++;
                        }
                        $result[] = $this->categoryIndex[$lg][$index];
                    }
                    break;
            }
        }
        return array_values($result);
    }

    public function setEmbCodeList($codes)
    {
        $this->embCodeIndex = $codes;
    }

    public function addIngredients($ingredients)
    {
        $productIngredients = [];
        $ingredientList = explode(';¦;', $ingredients);
        foreach ($ingredientList as $ingredient) {
            $rankings = explode(';', $ingredient);
            $ingredientObj = new Ingredient();
            $ingredientObj2 = new Ingredient();
            foreach ($rankings as $rank) {
                $tmp = explode('|', $rank);
                switch ($tmp[0]) {
                    case "id":
                        $idTag = explode(':', $tmp[1]);
                        if ($idTag[0] == 'en') {
//                            $ingredientObj2 = new Ingredient(0, ucwords(str_replace('-',' ',$idTag[1])), 'en');
                            $ingredientObj2->setName(ucwords(str_replace('-', ' ', trim($idTag[1]))));
                            $ingredientObj2->setLanguage('en');
                        }
                        break;
                    case "text":
                        $ingredientObj->setName(ucfirst(trim($tmp[1])));
                        $ingredientObj->setLanguage('fr');
                        break;
                    case "rank":
                        $ingredientObj->setRank($tmp[1]);
                        $ingredientObj2->setRank($tmp[1]);
                        break;
                }
            }
            if (strlen($ingredientObj->name) < 50 && !is_null($ingredientObj->name) && !empty($ingredientObj->name)) {
                $label = slugify($ingredientObj->name);
                if (!isset($this->ingredientsIndex[$label])) {
                    $this->ingredientsIndex[$label] = $ingredientObj;
                    $this->ingredientsIndex[$label]->id = $this->ingredientId;
                    $this->ingredientId++;
                }
                if (!isset($productIngredients[$label])) {
                    $productIngredients[$label] = $this->ingredientsIndex[$label];
                }
            }
            if (strlen($ingredientObj2->name) < 50 && !is_null($ingredientObj2->name) && !empty($ingredientObj2->name)) {
                if ($ingredientObj2->hasName()) {
                    $label = slugify($ingredientObj2->name);
                    if (!isset($this->ingredientsIndex[$label])) {
                        $this->ingredientsIndex[$label] = $ingredientObj2;
                        $this->ingredientsIndex[$label]->id = $this->ingredientId;
                        $this->ingredientId++;
                    }

                    if (!isset($productIngredients[$label])) {
                        $productIngredients[$label] = $this->ingredientsIndex[$label];
                    }
                }
            }
        }
        return array_values($productIngredients);

    }

    public function addNutrientLevels($nutrientLevels)
    {
        if (empty($nutrientLevels)) {
            return null;
        }
        $productNutrients = [];
        $nutrients = explode(';', $nutrientLevels);
        foreach ($nutrients as $nutrient) {
            $label = explode(':', $nutrient);
            if ($label[0] == 'en' && strpos($label[1], 'quantity') !== false) {
                $labelSlug = slugify($label[1]);
                if (!isset($this->nutrientLevelsIndex[$labelSlug])) {
                    $this->nutrientLevelsIndex[$labelSlug] = new Nutrient($this->nutrientLevelId, $label[1]);
                    $this->nutrientLevelId++;
                }
                if (!isset($productNutrients[$labelSlug])) {
                    $productNutrients[$labelSlug] = $this->nutrientLevelsIndex[$labelSlug];
                }
            }
        }
        return array_values($productNutrients);
    }

    public function addNutriments($nutriments)
    {
        //nova-group_100g|1;alcohol_serving|14.5;alcohol|14.5;nova-group|1;nova-group_serving|1;alcohol_unit|% vol;alcohol_value|14.5;alcohol_100g|14.5
        $nutrimentList = [];
        if (empty($nutriments)) {
            return null;
        }
        $productNutriments = explode(';', $nutriments);
        foreach ($productNutriments as $nutriment) {
            $label = explode('|', $nutriment);
            if ((!is_null($label[0]) && !is_null($label[1])) && strlen($label[0]) < 50) {
                $lab = slugify(trim($label[0]));
                if (!isset($this->nutrimentsIndex[$lab])) {
                    $this->nutrimentsIndex[$lab] = new Nutriment(
                        $this->nutrimentId, ucfirst(trim($label[0])),
                        $label[1]);
                    $this->nutrimentId++;
                }
                $nutrimentList[] = $this->nutrimentsIndex[$lab];
            }
        }
        return $nutrimentList;
    }

    public function addPackaging($packaging)
    {
        $packageList = [];
        $packages = explode(',', $packaging);
        foreach ($packages as $package) {
            if (strlen($package) < 75) {
                $label = slugify(trim($package));
                if (!isset($this->packagesIndex[$label])) {
                    $this->packagesIndex[$label] = new Package($this->packageId, ucfirst(trim($package)));
                    $this->packageId++;
                }
                if (!isset($packageList[$label])) {
                    $packageList[$label] = $this->packagesIndex[$label];
                }
            }
        }
        return array_values($packageList);
    }

    public function processImage($fullId, $tag, $image)
    {
        switch ($tag) {
            case "code-2":
                $id = $fullId;
                break;
            case "code-4":
                $id = $fullId;
                break;
            case "code-5":
                $id = $fullId;
                break;
            case "code-6":
                $id = $fullId;
                break;
            case "code-8":
                $id = $fullId;
                break;
            case "code-9":
                $id = sprintf(
                    '%s/%s/%s/',
                    substr($fullId, 0, 3),
                    substr($fullId, 3, 3),
                    substr($fullId, -3)
                );
                break;
            case "code-12":
                $id = sprintf(
                    '%s/%s/%s/%s',
                    substr($fullId, 0, 3),
                    substr($fullId, 3, 3),
                    substr($fullId, 6, 3),
                    substr($fullId, -3)
                );
                break;
            case "code-13":
                $id = sprintf(
                    '%s/%s/%s/%s',
                    substr($fullId, 0, 3),
                    substr($fullId, 3, 3),
                    substr($fullId, 6, 3),
                    substr($fullId, -4)
                );
                break;
            case "code-14":
                $id = sprintf(
                    '%s/%s/%s/%s',
                    substr($fullId, 0, 3),
                    substr($fullId, 3, 3),
                    substr($fullId, 6, 3),
                    substr($fullId, -5)
                );
                break;
            default:
                return null;
        }
        return new Image($id, $image[0], $image[2], $image[3], $image[1]);

    }

    public function __get($name)
    {
        return $this->{$name};

    }

    public function __unset($name)
    {
        unset($this->{$name});
    }

}