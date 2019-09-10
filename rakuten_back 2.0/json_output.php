

<?php
    $test = getRakutenResult($_GET['keyWord'],1000,1);
    echo $test;
    ?>

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
        }
        $json = json_encode($image) ;
        return $json;
    }
    
    
     #########タグAPI検索##############################################################################
     function search_brand($tagId){
     // ベースとなるリクエストURL
     $urls = 'https://app.rakuten.co.jp/services/api/IchibaTag/Search/20140222';
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
     $rakuten_json = json_decode(@file_get_contents($url, true));
     $tags = array();
     foreach($rakuten_json->tagGroups as $tag) {
     $tags[] = array(
     'tagname' => (array)$tag->tagGroup,
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
     
     $genres = array();
     foreach($rakuten_json->tagGroups as $genre) {
     $genres[] = array(
     'parent' => (array)$genre->tagGroup->tags[0],
     );
     }return $genres;
     
     }
    
    // RFC3986 形式で URL エンコードする関数
    function urlencode_rfc3986($str) {
        return str_replace('%7E', '~', rawurlencode($str));
    }?>
