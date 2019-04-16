<?php

use Illuminate\Database\Seeder;


class ProductsSeeder extends Seeder
{
    private $origDir = __DIR__ . '/../files/data';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        require_once(__DIR__ . '/../files/products/ProductRepo.php');
        $this->seedNonProductForeignKeyedItems();
        $this->seedProducts();
        $this->seedProductDependencies();
    }

    private function seedNonProductForeignKeyedItems()
    {
        $data = $this->initSeeding();
        $this->seedPackagers($data->packagers);
        unset($data->packagers);
        $this->seedChunk($data->productBrands, \Naraki\Shop\Models\ProductBrand::class);
        $this->seedChunk($data->productCategories, \Naraki\Shop\Models\ProductCategory::class);
        $this->seedChunk($data->productIngredients, \Naraki\Shop\Models\ProductIngredient::class, 100);
        $this->seedChunk($data->productPackages, \Naraki\Shop\Models\ProductPackage::class, 25);
        $this->seedChunk($data->productNutriments, \Naraki\Shop\Models\ProductNutriment::class, 15);
        $this->seedChunk($data->productNutrientLevels, \Naraki\Shop\Models\ProductNutrientLevel::class, 1);
    }

    private function insertProducts(DBEntries $DBEntries)
    {
        $this->seedChunk($DBEntries->productNutritionInfo,\Naraki\Shop\Models\ProductNutritionInfo::class,15);
        $this->seedChunk($DBEntries->productBrandRecords,\Naraki\Shop\Models\ProductBrandRecord::class,15);
        $this->seedChunk($DBEntries->productCategoryRecords,\Naraki\Shop\Models\ProductCategoryRecord::class,25);
        $this->seedChunk($DBEntries->productIngredientRecords,\Naraki\Shop\Models\ProductIngredientRecord::class,50);
        $this->seedChunk($DBEntries->productPackageRecords,\Naraki\Shop\Models\ProductPackageRecord::class,25);
        $this->seedChunk($DBEntries->productNutrimentRecords,\Naraki\Shop\Models\ProductNutrimentRecord::class,60);
        $this->seedChunk($DBEntries->productNutrientLevelRecords,\Naraki\Shop\Models\ProductNutrientLevelRecord::class,25);
    }

    private function seedProducts()
    {
        $fullPath = $this->origDir . '/products_000.txt' ;
        $size = filesize($fullPath);
        $handle = fopen($fullPath, "r");
        $products = unserialize(fread($handle, ($size + 128)));
        $this->seedChunk($products,\Naraki\Shop\Models\Product::class,15);
        fclose($handle);
    }

    private function seedProductDependencies()
    {
        $dir = opendir($this->origDir);
        if (!$dir) {
            die(sprintf("%s could not be read.", $this->origDir));
        }

        while (($file = readdir($dir)) !== false) {
            $fullPath = $this->origDir . '/' . $file;
            if (is_file($fullPath) && strpos($file, 'dependents') !== false) {
                $size = filesize($fullPath);
                $handle = fopen($fullPath, "r");
                /**
                 * @var $DBEntries \DBEntries
                 */
                $DBEntries = unserialize(fread($handle, ($size + 128)), [DBEntries::class]);
                $this->insertProducts($DBEntries);
                fclose($handle);
            }
        }
        closedir($dir);

    }

    private function seedChunk($data, $model, $nbChunks = 50)
    {
        $chunks = array_chunk($data, $nbChunks);
        foreach ($chunks as $chunk) {
            forward_static_call(sprintf('%s::insert', $model), $chunk);
        }
    }

    private function seedPackagers($packagers)
    {
        $this->seedChunk($packagers, \Naraki\Shop\Models\ProductPackager::class);

        $entity = \Naraki\Shop\Models\ProductPackager::create([
            'product_packager_name' => 'FOR SYSTEM USE'
        ]);
        $entity->save();
        $pk = $entity->getKeyName();
        $entity->setAttribute($pk, 0);
        $entity->save();
    }

    private function initSeeding()
    {
        $repoFile = $this->origDir . '/query_init_000.txt';
        $size = filesize($repoFile);
        $handle = fopen($repoFile, "r");
        $DBEntriesRepo = unserialize(fread($handle, ($size + 128)));
        fclose($handle);
        return $DBEntriesRepo;
    }

    private function getClassArrayCount($class)
    {
        $s = new \ReflectionClass($class);
        $arrays = $s->getProperties();
        /**
         * @var $item \ReflectionProperty
         */
        foreach ($arrays as $item) {
            if ($class->{$item->getName()}) {
                echo $item->getName() . " :" . count($class->{$item->getName()}) . "\n";
            }
        }

    }
}
