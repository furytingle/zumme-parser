<?php
/**
 * Created by PhpStorm.
 * User: tingle
 * Date: 25.02.17
 * Time: 18:22
 */

require_once "vendor/autoload.php";

try {
    $parser = new \Core\Parser();
    $parser->run(new Core\Helpers\CurlHelper());
} catch (\Exception $e) {
    echo $e->getMessage();
}
