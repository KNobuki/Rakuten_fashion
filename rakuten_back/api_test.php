<!DOCTYPE html>
<html lang='ja'>
<head>
<title>楽天商品検索API テスト</title>
<meta charset='utf-8'>
<meta name="author" content="Rakuten Fashion Dev Team">
<title>Rakuten Fashion</title>
<link href="images/favicon.png" rel="icon">
<link rel="stylesheet" href="CSS/style.css">
<link href="http://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link href="js/slick/slick-theme.css" rel="stylesheet" type="text/css">
<link href="js/slick/slick.css" rel="stylesheet" type="text/css">
<link href="CSS/takestyle.css" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3/dist/jquery.min.js"></script>
<script type="text/javascript" src="js/slick/slick.min.js"></script>
<script type="text/javascript" src="js/main.js"></script>

</head>
<body>
<?php
    $i = 1; $args = [];
    $rakuten_relust = getRakutenResult('diesel  靴',5000); // キーワードと最低価格を指定
    
    
    foreach ( (array)$rakuten_relust as $item) :
    $test1 = explode("/",$rakuten_relust[$i-1]['url']);
    $key = in_array($test1[count($test1)-2], $args);
    if($key){
        $i++;
        continue;
    }
    array_push($args, $test1[count($test1)-2]);
    ?>
<ul class="slider">
<?php
    for($imgCnt = 0; $imgCnt < count($item['ImageUrls']); $imgCnt++){
        $explodeImageUrls = explode("?",$item['ImageUrls'][$imgCnt]->imageUrl);
        ?>
<li><a href=""><img src="<?php echo $explodeImageUrls[0]; ?>" alt=<?php echo 'image'.$imgCnt?>></a></li>
<?php
    }
    ?>
</ul>
<ul class="thumb">
<?php
    for($imgCnt = 0; $imgCnt < count($item['ImageUrls']); $imgCnt++){
        $explodeImageUrls = explode("?",$item['ImageUrls'][$imgCnt]->imageUrl);
        ?>
<li><a href="#"><img src="<?php echo $explodeImageUrls[0]; ?>" alt=<?php echo 'image'.$imgCnt?>></a></li>
<?php
    }
    ?>
</ul>
<div><?php echo $item['name']; ?></div>
<div><a href='<?php echo $item['url']; ?>' target="_blank"><?php echo $item['url']; ?></a></div>
<div><?php echo $item['price']; ?>円</div>
<div><?php echo $i; ?></div>

<div><?php
    foreach((array)$item['tagId'] as $number){
        for($j = 1000873; $j <= 1000887; $j++){
            if($number == $j){
                echo $number. ",";
            }
        }
    }
    ?></div>
<div><?php $test = explode("/",$rakuten_relust[$i-1]['url']);
    echo $test[count($test)-2];?></div>

</div>
</div>
<?php
    $i++;
    endforeach;
    ?>
<?php print_r($args) ?>
</body>
</html>

<?php
    
    function getRakutenResult($keyword,$min_price) {
        
        // ベースとなるリクエストURL
        $baseurl = 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20140222';
        $params = array();
        $params['applicationId'] = '1066483623417999424'; // アプリID
        $params['keyword'] = urlencode_rfc3986($keyword); // 任意のキーワード。※文字コードは UTF-8
        $params['sort'] = urlencode_rfc3986('+itemPrice'); // ソートの方法。※文字コードは UTF-8
        $params['minPrice'] = $min_price; // 最低価格
        $params['shopcode'] = 'kbf-rba'; //RBAのデータのみ取得
        $params['hits'] = 30;
        $params['page'] = 1;
        $canonical_string='';
        
        foreach($params as $k => $v) {
            $canonical_string .= '&' . $k . '=' . $v;
        }
        // 先頭の'&'を除去
        $canonical_string = substr($canonical_string, 1);
        
        // リクエストURL を作成
        $url = $baseurl . '?' . $canonical_string;
        
        // XMLをオブジェクトに代入
        $rakuten_json=json_decode(@file_get_contents($url, true));
        // XMLをオブジェクトに代入
        $rakuten_json2=json_decode(@file_get_contents($tag_url, true));
        
        $items = array();
        foreach($rakuten_json->Items as $item) {
            $items[] = array(
                             'name' => (string)$item->Item->itemName,
                             'url' => (string)$item->Item->itemUrl,
                             'img' => isset($item->Item->mediumImageUrls[0]->imageUrl) ? (string)$item->Item->mediumImageUrls[0]->imageUrl : '',
                             'price' => (string)$item->Item->itemPrice,
                             'shop' => (string)$item->Item->shopName,
                             'tagId' => (array)$item->Item->tagIds,
                             'ImageUrls' => (array)$item->Item->mediumImageUrls,
                             );
        }return $items;
        $image = array();
        $i = 0;
        foreach ($item['ImageUrls'] as $images){
            $image[] = array(
                             $i => isset($images[$i]->imageUrl) ? ((array)$images[$i]->imageUrl):'none',
                             );
            $i++;
        }return $image;
    }
    
    // RFC3986 形式で URL エンコードする関数
    function urlencode_rfc3986($str) {
        return str_replace('%7E', '~', rawurlencode($str));
    }?>
