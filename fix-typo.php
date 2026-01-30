<?php
$filePath = 'e:\webiste theme and plugin\affiliate-product-showcase\affiliate-product-showcase\wp-content\plugins\affiliate-product-showcase\src\Repositories\CategoryRepository.php';
$content = file_get_contents($filePath);
$content = str_replace("'search' => $search,", "'search' => $search,", $content);
file_put_contents($filePath, $content);
echo "Fixed!";
