<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include_once '../vendor/autoload.php';

use Dez\Config\Adapter\Json as JsonAdapter;
use Dez\Config\Adapter\NativeArray as ArrayAdapter;

$config = new JsonAdapter('./config/config.json');
$config->merge(new ArrayAdapter('./config/config.php'));

Dez\ORM\Connection::init($config, 'dev');

class Post extends \Dez\ORM\Model\Table
{

    static protected $table = 'articles';

    public function refs()
    {
        return $this->hasMany(TagsRefs::class, 'article_id', 'id');
    }

}

class Tags extends \Dez\ORM\Model\Table
{

    static protected $table = 'article_tags';

}

class TagsRefs extends \Dez\ORM\Model\Table
{

    static protected $table = 'article_tag_ref';

    public function tag()
    {
        return $this->hasOne(Tags::class, 'id', 'tag_id');
    }

}

$connection = Dez\ORM\Connection::connect();

$queries = [];

Dez\ORM\Common\Event::instance()->attach('query', function ($query) use (& $queries) {
    $queries[] = $query;
});


foreach (Post::all() as $item) {
    echo $item->getTitle();
    foreach ($item->refs() as $ref) {
        echo "<br><b>{$ref->tag()->getName()}</b>";
    }

    if($item->refs()->count() == 0) {
        $item->delete();
    }
    echo "<hr>";
}

echo "<h3>total queries: ". count($queries) ."</h3>";

foreach ($queries as $query) {
    echo "<pre>{$query}</pre><br><hr><br>";
}