<?php
require_once '../helpers/helpers.php';
ini_set('max_execution_time', '8000');
require_once 'ProductRepo.php';

$startExecTime = microtime(true);
$startMemory = memory_get_usage();
//organizeData(organizeEmbCodes(), true, false, 1000);

//organizeData(organizeEmbCodes(), false, true, 10);
processDataForDB();
readData();

function test($data)
{
    $f = $data->getProducts();
    dd($f);
    foreach ($f as $d) {
        if ($d->getImage()) {
            dd($d);
        }
    }
}

function readData()
{
    $origDir = "data";

    $dir = opendir($origDir);
    if (!$dir) {
        die(sprintf("%s could not be read.", $origDir));
    }
    $productId = 1;
    //We wanna insert products in the database before the rest because of the integrity checks.
    $productsArray = [];
    while (($file = readdir($dir)) !== false) {
        $fullPath = $origDir . '/' . $file;
        if (is_file($fullPath) && strpos($file, 'product_data') !== false) {
            $size = filesize($fullPath);
            $handle = fopen($fullPath, "r");
            $products = unserialize(fread($handle, ($size + 128)));
            $data = writeSQLEntries($productId, $products);
            $productId = $data->productId;
            $productsArray = array_merge($data->products_list, $productsArray);
            fclose($handle);
        }
    }
    closedir($dir);
    writeData(1, $productsArray, 'products');
}

function processDataForDB()
{
    $size = filesize('data/non_product_values_000.txt');
    $handle = fopen('data/non_product_values_000.txt', "r");
    /**
     * @var $productRepo \ProductRepo
     */
    $productRepo = unserialize(fread($handle, ($size + 128)));
    fclose($handle);

    $dbEntries = new DBEntries();

    foreach ($productRepo->embCodeIndex as $packager) {
        $dbEntries->addPackagers($packager);
    }
    unset($productRepo->embCodeIndex);

    foreach ($productRepo->ingredientsIndex as $ingredient) {
        $dbEntries->addProductIngredients($ingredient);
    }
    unset($productRepo->ingredientsIndex);

    foreach ($productRepo->nutrientLevelsIndex as $nutrientLevel) {
        $dbEntries->addProductNutrientLevels($nutrientLevel);
    }
    unset($productRepo->nutrientLevelsIndex);

    foreach ($productRepo->brandIndex as $brand) {
        $dbEntries->addBrand($brand);
    }
    unset($productRepo->brandIndex);

    foreach ($productRepo->categoryIndex as $language) {
        foreach ($language as $cat) {
            $dbEntries->addProductCategories($cat);
        }
    }
    unset($productRepo->categoryIndex);

    foreach ($productRepo->packagesIndex as $package) {
        $dbEntries->addProductPackages($package);
    }
    unset($productRepo->packagesIndex);

    foreach ($productRepo->nutrimentsIndex as $nutriment) {
        $dbEntries->addProductNutriments($nutriment);
    }
    unset($productRepo->nutrimentsIndex);

//    dd($dbEntries->productPackages);
//    dd($dbEntries->productNutrimentRecords);

    writeData(0, $dbEntries, 'query_init');
    unset($dbEntries, $productRepo);
}

function writeSQLEntries(int $productId, array $products)
{
    $dbEntries = new DBEntries();
    foreach ($products as $key => $product) {
        $dbEntries->addProduct($product);
        $dbEntries->addProductNutritionInfo($productId, $product);
        $dbEntries->addProductBrand($productId, $product->brands);
        $dbEntries->addProductCategoryRecords($productId, $product->categories);
        $dbEntries->addProductIngredientRecords($productId, $product->ingredients);
        $dbEntries->addProductNutrientLevelRecords($productId, $product->nutrientLevels);
        $dbEntries->addProductPackageRecords($productId, $product->packaging);
        $dbEntries->addProductNutrimentRecords($productId, $product->nutriments);
        $productId++;
    }
    $productList = $dbEntries->products;
    unset($dbEntries->products);
    writeData($productId, $dbEntries, 'product_dependents');
    return (object)['productId' => $productId, 'products_list' => $productList];
}

function organizeData($codes, $test = true, $writeData = true, $amountOfRecordsProcessed = 10)
{
    $i = 1;
    $sep = chr(9);
    $handle = @fopen('../csv/products.tsv', "r");
    $header = fgetcsv($handle, 1024, $sep);
    $productRepo = new ProductRepo();
    $productRepo->setEmbCodeList($codes);
    unset($codes);
    while (($data = fgetcsv($handle, 8196, $sep)) != false) {
//        $product = new Product();
//        $tag = $id = '';
        $tag = '';

        while (list($key, $item) = each($data)) {
            switch ($header[$key]) {
                case '_id':
                    $id = trim(str_replace(['_', '-', ' '], '', $item));
                    if (preg_match('/^[0-9]+$/', $id)) {
                        $product = new Product();
                        $product->setIdentifier($id);
                    } else {
                        continue 2;
                    }
                    break;
                case 'product_name':
                    if (!is_null($item) && !empty($item) && strlen($item) < 150) {
                        $product->setName(ucfirst(trim($item)));
                    }
                    break;
                case 'brands':
                    $product->setBrands($productRepo->addBrands($item));
                    break;
                case 'categories':
                    $product->setCategories($productRepo->addCategories($item));
                    break;
                case 'categories_tags':
                    $product->setCategories($productRepo->addCategories($item, ';'));
                    break;
                case 'emb_codes_tags':
                    if (!is_null($item) && !empty($item)) {
                        $product->setEmbCode($productRepo->addEmbCodes($item));
                    }
                    break;
                case 'ingredients_text':
                    $product->setIngredientsText(ucfirst(trim($item)));
                    break;
                case 'ingredients':
                    $product->setIngredients($productRepo->addIngredients($item));
                    break;
                case 'nova_group':
                    if (strlen($item) == 1) {
                        $product->setNovaGroup(intval($item));
                    }
                    break;
                case 'nutrient_levels_tags':
                    $product->setNutrientLevels($productRepo->addNutrientLevels($item));
                    break;
                case 'nutriments':
                    $product->setNutriments($productRepo->addNutriments($item));
                    break;
                case 'nutrition_grades':
                    $product->setNutritionGrade($item);
                    break;
                case 'packaging':
                    $product->setPackaging($productRepo->addPackaging($item));
                    break;
                case 'codes_tags':
                    $tag = $item;
                    break;
                case 'images':
                    $imageData = explode(';', $item);
                    if (count($imageData) === 4) {
                        $product->setImage($productRepo->processImage($id, $tag, $imageData));
                    } else {
                        $product->setImage(null);
                    }
                    break;

            }
        }
        $product->unsetCategoryIndex();

        $productName = $product->name;
        if (isset($productName) && !empty($productName)) {
            $productRepo->add($product);
            $i++;
        }

        if ($test) {
            if ($i == $amountOfRecordsProcessed) {
                break;
            }
        } else {
            if ($i % 5000 === 0 && $writeData) {
                writeData($i, $productRepo->flushProducts());
            }
        }
    }
    fclose($handle);

    if (!$test && $writeData) {
        writeData($i, $productRepo->flushProducts());
        writeData(0, $productRepo, 'non_product_values');
    }

    unset($productRepo);
}

function writeData($i, $values, $filename = 'product_data')
{
    $fileNumber = (string)round($i / 5000);
    $handle2 = @fopen(sprintf('data/%s_%s.txt', $filename, str_pad($fileNumber, 3, '0', STR_PAD_LEFT)), "w");
    fwrite($handle2, serialize($values));
    fclose($handle2);
}

function organizeEmbCodes()
{
    $codes = [];
    $sep = chr(9);
    $handle = @fopen('_codes.csv', "r");
    fgetcsv($handle, 1024, $sep);
    $i = 1;
    $header = array_flip([
        'dept',
        'emb',
        'siret',
        'name',
        'address',
        'postcode',
        'town',
        'category',
        'activity',
        'species'
    ]);
    while (($data = fgetcsv($handle, 8192, $sep)) != false) {
        $embID = str_replace('_', '-', slugify(sprintf('FR %s EC', $data[$header['emb']])));
        if (!isset($codes[$embID])) {
            $codes[$embID] = new Packager(
                $data[$header['emb']],
                $i,
                $data[$header['name']],
                $data[$header['siret']],
                $data[$header['address']],
                $data[$header['postcode']],
                $data[$header['town']],
                [$data[$header['category']]],
                [$data[$header['activity']]],
                $data[$header['species']]
            );
            $i++;
        } else {
            $codes[$embID]->updateCategory($data[$header['category']]);
            $codes[$embID]->updateActivity($data[$header['activity']]);
        }
    }
    fclose($handle);
//    dd(array_shift(array_chunk($codes,100)));

    return $codes;
}

function getEmbCodesFromFiles()
{
    $origDir = "codes";
    $headerIsWritten = false;

    $dir = opendir($origDir);
    $handle2 = @fopen('codes.csv', "w");

    while (($file = readdir($dir)) !== false) {
        if (is_file($origDir . '/' . $file)) {
            $sep = ',';
            $handle = @fopen($origDir . '/' . $file, "r");
            $header = fgets($handle, 1024);
            if (!$headerIsWritten) {
                $headerIsWritten = true;
                fwrite($handle2, $header);
            }
            while (($data = fgets($handle, 8192)) != false) {
                fwrite($handle2, $data);
            }
            fclose($handle);
        }
    }
    fclose($handle2);
    closedir($dir);
}




execution_time($startExecTime);
execution_memory($startMemory);