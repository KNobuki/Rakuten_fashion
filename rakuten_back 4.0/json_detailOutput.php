
<?php
    if(isset($_GET['itemID'])){
        $merge_list = GoodsMerge($_GET['itemID'],5000);

        $Data = DataSet($merge_list);
        ob_clean();
        echo $Data;
   }

##以下関数定義#########
    function DataSet($merge_list){
        $data_map = []; $costList = [];
        foreach ((array)$merge_list as $item){
            array_push($costList,$item['price']);
            $max_price = max($costList);
            $min_price = min($costList);
        }
        foreach ((array)$merge_list as $item):
            // 画像の縮小パラメータを除去
            $imageURL = [];
            for($imgCnt = 0; $imgCnt < count($item['ImageUrls']); $imgCnt++){
                $explodeImageUrls = explode("?",$item['ImageUrls'][$imgCnt]->imageUrl);//画像に付属したサムネイル情報を除去
                array_push($imageURL,$explodeImageUrls[0]);
            }
            $goodsID = explode("/",$item['url']);
            $goodsID = $goodsID[count($goodsID)-2];
            $map = array(///////ここに全部追加
                     "ID" => $goodsID,
                     "price" =>$item['price'],
                     "max_price" => $max_price,
                     "min_price" => $min_price,
                     "image" =>$imageURL,
                     "pointRate" => $item['pointRate'],
                     "itemCaption" => $item['itemCaption'],
                     );
            $color_code = []; $color_map = [];$brand_map = [];
            foreach((array)$item['tagId'] as $number){
                for($j = 1000873; $j <= 1000887; $j++){
                    if($number == $j){
                        array_push($color_code, $number);
                    }
                }
            }
            if(count($color_code) > 0){
                foreach($color_code as $color){
                    $color_tag = search_brand($color);
                    foreach ((array)$color_tag as $tag){
                    $tag_array = $tag['tagname'];
                    $color_map += array(
                            "color_name" => $tag_array['tag']->tagName,
                    );
                    }
                }
            }
            else{
                $color_map = array(
                                  "color_name" => "non_color",
                                  );
            }
            
            
            $brand_code = 1;
            foreach((array)$item['tagId'] as $number){
                for($j = 1000709; $j <= 1000869; $j++){
                    if($number == $j){
                        $brand_code = $number;
                    }
                }
            }
    if($brand_code != 1){
        $rakuten_tag = search_brand($brand_code);
        foreach ( (array)$rakuten_tag as $tag) :
        $tag_array = $tag['tagname'];
        $brand_map = array(
                          "brand_name" => $tag_array['tag']->tagName,
                          );
        endforeach;
    }else{
        $brand_map = array(
                          "brand_name" => "non_brand",
                          );
    }
            $map += $color_map;
            $map += $brand_map;
        array_push($data_map, $map);
     
        endforeach;
        $json_data = json_encode($data_map,JSON_UNESCAPED_UNICODE);

        return $json_data;
       // $json_data = json_encode($map);
        //return $json_data;
    }
    
    ###same goods merge##########################################################
    function GoodsMerge($ID,$min_price){
        $i = 1; $MergeList = [];
            $rakuten_relust = getRakutenResult($ID,$min_price); // キーワードと最低価格を指定
            foreach ( (array)$rakuten_relust as $item):
                $explode_urls = explode("/",$rakuten_relust[$i-1]['url']);
                $item_id = $explode_urls[count($explode_urls)-2];
                if($item_id != $ID){
                    $i++;
                    continue;
                }
                array_push($MergeList, $item);
        $i++;
            //$MergeJson = json_encode($MergeList);
            endforeach;
            return $MergeList;
    }
    
    #########商品検索API検索##############################################################################
    function getRakutenResult($keyword,$min_price) {
        
        // ベースとなるリクエストURL
        $baseurl = 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20140222';
        $params = array();
        $params['applicationId'] = '1066483623417999424'; // アプリID
        $params['keyword'] = urlencode_rfc3986($keyword); // 任意のキーワード。※文字コードは UTF-8
        $params['minPrice'] = $min_price;
        $params['sort'] = urlencode_rfc3986('+itemPrice'); // ソートの方法。※文字コードは UTF-8
        $params['shopcode'] = 'kbf-rba'; //RBAのデータのみ取得
        $params['hits'] = 30;
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
                             'Genre' => (string)$item->Item->genreId,
                             'pointRate' => (string)$item->Item->pointRate,
                             'itemCaption' => (string)$item->Item->itemCaption,
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
    
    #########ブランドAPI検索######################################################################################
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
        foreach($rakuten_json->tagGroups as $tag){
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
