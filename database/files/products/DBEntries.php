<?php

class DBEntries
{

    private $products = [];
    private $productNutritionInfo = [];
    private $productBrands = [];
    private $productBrandRecords = [];
    private $packagers = [];
    private $productCategories = [];
    private $productCategoryRecords = [];
    private $productIngredients = [];
    private $productPackages = [];
    private $productNutriments = [];
    private $productIngredientRecords = [];
    private $productPackageRecords = [];
    private $productNutrimentRecords = [];
    private $productNutrientLevels = [];
    private $productNutrientLevelRecords = [];

    public function addProduct(Product $product)
    {
        if (is_null($product->embCode)) {
            $emb = 0;
        } else {
            $emb = $product->embCode->id;
        }
        $this->products[] = [
            'product_name' => $product->name,
            'product_identifier' => $product->identifier,
            'product_packager_id' => $emb,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public function addProductNutritionInfo($id, Product $product)
    {
        $this->productNutritionInfo[] = [
            'product_id' => $id,
            'product_nova_group' => $product->novaGroup,
            'product_ingredients_text' => $product->ingredientsText,
            'product_nutrition_grade' => $product->nutritionGrade
        ];
    }

    public function addBrand(Brand $brand)
    {
        $this->productBrands[] = [
            'product_brand_name' => $brand->name
        ];
    }

    public function addProductBrand($id, $brands)
    {
        if (is_null($brands)) {
            return;
        }
        foreach ($brands as $brand) {
            $this->productBrandRecords[] = [
                'product_id' => $id,
                'product_brand_id' => $brand->id
            ];
        }
    }

    public function addPackagers(Packager $packager)
    {
        $this->packagers[] = [
            'product_packager_name' => $packager->name,
            'product_packager_code' => $packager->code,
            'product_packager_siret' => $packager->siret,
            'product_packager_address' => $packager->address,
            'product_packager_postcode' => $packager->postcode,
            'product_packager_town' => $packager->town,
            'product_packager_category' => (empty($packager->category)) ? null : (implode(', ', $packager->category)),
            'product_packager_activity' => (empty($packager->activity)) ? null : (implode(', ', $packager->activity)),
            'product_packager_species' => $packager->species,
        ];
    }

    public function addProductCategories(ProductCategory $cat)
    {
        $this->productCategories[] = [
            'product_category_name' => $cat->name,
            'language_id' => $this->determineLanguage($cat->language)
        ];
    }

    public function addProductCategoryRecords(int $productId, $productCats)
    {
        if (is_null($productCats)) {
            return;
        }
        foreach ($productCats as $cat) {
            $this->productCategoryRecords[] = [
                'product_id' => $productId,
                'product_category_id' => $cat->id
            ];
        }
    }

    public function addProductIngredients(Ingredient $i)
    {
        $this->productIngredients[] = [
            'product_ingredient_name' => $i->name,
            'language_id' => $this->determineLanguage($i->language)
        ];
    }

    public function addProductIngredientRecords(int $productId, $productIngredients)
    {
        if (is_null($productIngredients)) {
            return;
        }
        foreach ($productIngredients as $productIngredient) {
            $this->productIngredientRecords[] = [
                'product_id' => $productId,
                'product_ingredient_id' => $productIngredient->id,
                'product_ingredient_rank' => $productIngredient->rank
            ];
        }
    }

    public function addProductNutrientLevels(Nutrient $n)
    {
        $this->productNutrientLevels[] = [
            'product_nutrient_level_name' => $n->name,
        ];
    }

    public function addProductNutrientLevelRecords(int $productId, $productNutrientLevels)
    {
        if (is_null($productNutrientLevels)) {
            return;
        }
        foreach ($productNutrientLevels as $productNutrientLevel) {
            $this->productNutrientLevelRecords[] = [
                'product_id' => $productId,
                'product_nutrient_level_id' => $productNutrientLevel->id,
            ];
        }
    }

    public function addProductPackages(Package $n)
    {
        $this->productPackages[] = [
            'product_package_name' => $n->name,
        ];
    }

    public function addProductPackageRecords(int $productId, $productPackages)
    {
        if (is_null($productPackages)) {
            return;
        }
        foreach ($productPackages as $productPackage) {
            $this->productPackageRecords[] = [
                'product_id' => $productId,
                'product_package_id' => $productPackage->id,
            ];
        }
    }

    public function addProductNutriments(Nutriment $n)
    {
        $this->productNutriments[] = [
            'product_nutriment_name' => $n->name,
        ];
    }

    public function addProductNutrimentRecords(int $productId, $productNutriments)
    {
        if (is_null($productNutriments)) {
            return;
        }
        $uniqueNuts = [];
        foreach ($productNutriments as $productNutriment) {
            if (!isset($uniqueNuts[$productNutriment->id])) {
                $uniqueNuts[$productNutriment->id] = true;
                $this->productNutrimentRecords[] = [
                    'product_id' => $productId,
                    'product_nutriment_id' => $productNutriment->id,
                ];
            }
        }
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    private function determineLanguage($field)
    {
        return ($field == 'en') ?
            ProductRepo::LANGUAGE_ID_ENGLISH :
            ProductRepo::LANGUAGE_ID_FRENCH;

    }

    public function __unset($name)
    {
        unset($this->{$name});
    }

}