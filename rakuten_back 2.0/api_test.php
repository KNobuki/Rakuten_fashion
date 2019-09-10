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
#########同一商品merge##############################################################################
    for($p = 1; $p < 2; $p ++){#取得ページ数指定
        $i = 1; $args = []; $aleadyExistsItemList = [];
    $rakuten_relust = getRakutenResult('diesel  靴',5000,$p); // キーワードと最低価格を指定

    $i = 1; $args = [];
    foreach ( (array)$rakuten_relust as $item) :
    $explode_urls = explode("/",$rakuten_relust[$i-1]['url']);
    $is_merge_item = in_array($explode_urls[count($explode_urls)-2], $args);
    if($is_merge_item){
        $i++;
        continue;
    }
    array_push($args, $explode_urls[count($explode_urls)-2]);

#########画像表示with takestyle#######################################################################
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
        $explodeImageUrls = explode("?",$item['ImageUrls'][$imgCnt]->imageUrl);//画像に付属したサムネイル情報を除去
        ?>
<li><a href="#"><img src="<?php echo $explodeImageUrls[0]; ?>" alt=<?php echo 'image'.$imgCnt?>></a></li>
<?php
    }
    ?>
</ul>

<div><?php
#########ブランド名表示##############################################################################
    foreach((array)$item['tagId'] as $number){
        for($j = 1000709; $j <= 1000869; $j++){
            if($number == $j){
                $brand_code = $number;
            }
        }
    }
    ?></div>
<?php
$rakuten_tag = search_brand($brand_code);
foreach ( (array)$rakuten_tag as $tag) :
        $tag_array = $tag['tagname'];
    echo $tag_array['tag']->tagName."<br/>";
    endforeach;
?>
<?php
#########詳細ジャンル表示##############################################################################
#########詳細検索用##############################################################################
    echo $item['Genre'];
    $rakuten_genre = search_genre($item['Genre']);
    foreach ( (array)$rakuten_genre as $genre) :
    print_r($genre['parent']);
    endforeach;
 

#########URL, Price表示##############################################################################   ?>

<div><a href='<?php echo $item['url']; ?>' target="_blank"><?php echo $item['url']; ?></a></div>
<div><?php echo $item['price']; ?>円</div>
<div><?php echo $i; ?></div>

<div><?php
########カラー名表示##############################################################################
    $color_code = [];
    foreach((array)$item['tagId'] as $number){
        for($j = 1000873; $j <= 1000887; $j++){
            if($number == $j){
                array_push($color_code, $number);
            }
        }
    }
    ?></div>

<?php
    if(count($color_code) > 0){
    foreach($color_code as $color){
$rakuten_tag = search_brand($color);
foreach ((array)$rakuten_tag as $tag) :
$tag_array = $tag['tagname'];
echo $tag_array['tag']->tagName."<br/>";
endforeach;
    }
    }else{
        echo "Non-color";
    }
?>
<div><?php
#########同一商品表示##############################################################################
 $test = explode("/",$rakuten_relust[$i-1]['url']);
    echo $test[count($test)-2];?></div>
    <div>
    <?php 
            $isAlreadyExistItem = in_array($explode_urls[count($explode_urls)-2], $aleadyExistsItemList);
                echo '<form method="POST" action="detail.php">';
                echo '<input type="hidden" name="Id" value="'.$test[count($test)-2].'">';
                echo '<input type="hidden" name="min_price" value="'.'5000'.'">';
                echo '<button>この商品をすべて見る</button>';
                echo '</form>';
        
        ?>
    </div>
</div>
</div>
<?php
    $i++;
    endforeach;
    }
    ?>
</body>
</html>
<?php
#########商品検索API検索##############################################################################
    function getRakutenResult($keyword,$min_price,$page) {
        
        // ベースとなるリクエストURL
        $baseurl = 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20140222';
        $params = array();
        $params['applicationId'] = '1066483623417999424'; // アプリID
        $params['keyword'] = urlencode_rfc3986($keyword); // 任意のキーワード。※文字コードは UTF-8
        $params['sort'] = urlencode_rfc3986('+itemPrice'); // ソートの方法。※文字コードは UTF-8
        $params['minPrice'] = $min_price; // 最低価格
        $params['shopcode'] = 'kbf-rba'; //RBAのデータのみ取得
        $params['hits'] = 30;
        $params['page'] = $page;
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
                             'CatchCopy'=> (string)$item->Item->catchcopy,
                             'Genre'=> (string)$item->Item->genreId,

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
    
#########タグAPI検索##############################################################################
    function search_brand($tagId){
        // ベースとなるリクエストURL
        $urls = "https://app.rakuten.co.jp/services/api/IchibaTag/Search/20140222";
        $params = array();
        $params['applicationId'] = '1066483623417999424'; // アプリID
        $params['tagId'] = $tagId; // 任意のキーワード。※文字コードは UTF-8
        $canonical_string='';
        
        foreach($params as $k => $v) {
            $canonical_string .= '&' . $k . '=' . $v;
        }
        // 先頭の'&'を除去
        $canonical_string = substr($canonical_string, 1);
        
        // リクエストURL を作成
        $url = $urls . '?' . $canonical_string;
        
        // XMLをオブジェクトに代入
        $rakuten_json=json_decode(@file_get_contents($url, true));
        
        $tags = array();
        foreach($rakuten_json->tagGroups as $tag) {
            $tags[] = array(
                             'tagname' => (array)$tag->tagGroup->tags[0],
                             );
        }return $tags;

    }
    
#########ジャンルAPI検索######################################################################################
    function search_genre($GenreId){
        // ベースとなるリクエストURL
        $urls = "https://app.rakuten.co.jp/services/api/IchibaGenre/Search/20140222";
        $params = array();
        $params['applicationId'] = '1066483623417999424'; // アプリID
        $params['genreId'] = $GenreId; // 任意のキーワード
        $params['genrePath'] = 1; // 任意のキーワード
        
        $canonical_string='';
        
        foreach($params as $k => $v) {
            $canonical_string .= '&' . $k . '=' . $v;
        }
        // 先頭の'&'を除去
        $canonical_string = substr($canonical_string, 1);
        
        // リクエストURL を作成
        $url = $urls . '?' . $canonical_string;
        
        // XMLをオブジェクトに代入
        $rakuten_json=json_decode(@file_get_contents($url, true));
        
        $tags = array();
        foreach($rakuten_json->tagGroups as $tag) {
            $tags[] = array(
                            'parent' => (array)$tag->tagGroup->tags[0],
                            );
        }return $tags;
        
    }
    
    // RFC3986 形式で URL エンコードする関数
    function urlencode_rfc3986($str) {
        return str_replace('%7E', '~', rawurlencode($str));
    }?>
