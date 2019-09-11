
<?php
    $results_json = getRakutenResult("diesel 靴",5000);
    $merge_json = GoodsMerge("diesel 靴",5000);
    DataSet($merge_json);
    function DataSet($merge_json){
        $data_map = [];
        foreach ((array)$merge_json as $item){
            $color_code = []; $color_data_map = [];$brand_data_map = [];
            foreach((array)$item['tagId'] as $number){
                for($j = 1000873; $j <= 1000887; $j++){
                    if($number == $j){
                        array_push($color_code, $number);
                    }
                }
            }
            if(count($color_code) > 0){
                foreach($color_code as $color){
                    $rakuten_tag = search_brand($color);
                    foreach ((array)$rakuten_tag as $tag) :
                    $tag_array = $tag['tagname'];
                    array_push($color_data_map,$tag_array['tag']->tagName);
                    endforeach;
                }
            }else{
                array_push($color_data_map,"non_color");
            }
            array_push($data_map,$color_data_map);
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
        array_push($brand_data_map,$tag_array['tag']->tagName);
        endforeach;
    }else{
array_push($brand_data_map,"non_color");
        
    }            array_push($data_map,$brand_data_map);

            
            
            
            $map = array(
                "url" => $item['url'],
                "price" =>$item['price'],
                "image" =>$item['ImageUrls'],
                "tags" =>$item['tagId'],
                
                         );
            array_push($data_map,$map);
        }
        for($i = 0; $i < 20; $i++){
        print_r($data_map[$i])."<br/>";
        echo "<br/>";
        }
    }
    ##以下関数定義#########
    ###same goods merge##########################################################
    function GoodsMerge($keyword,$min_price){
        $i = 1; $MergeList = []; $aleadyExistsItemList = [];$args = [];
            $rakuten_relust = getRakutenResult($keyword,$min_price); // キーワードと最低価格を指定
            foreach ( (array)$rakuten_relust as $item):
                $explode_urls = explode("/",$rakuten_relust[$i-1]['url']);
                $is_merge_item = in_array($explode_urls[count($explode_urls)-2], $args);
                if($is_merge_item){
                    $i++;
                    continue;
                }
        echo $explode_urls[count($explode_urls)-2]."<br/>";
                array_push($args, $explode_urls[count($explode_urls)-2]);
                array_push($MergeList, $item);
        $i++;
            //$MergeJson = json_encode($MergeList);
            endforeach;
            return $MergeList;
    }
        /*
    ###goods color name##########################################################
    function ColorSearch($item){
        $color_code = [];
        foreach((array)$item['tagId'] as $number){
            for($j = 1000873; $j <= 1000887; $j++){
                if($number == $j){
                    array_push($color_code, $number);
                }
            }
        }
        foreach($color_code as $color){
            $rakuten_tag = search_brand($color);
            foreach ((array)$rakuten_tag as $tag){
                $tag_array = $tag['tagname'];
                $map = array(
                    $color => $tag_array['tag']->tagName,
                             );
            }
        }
            $MergeMap = array();
            $MergeMap = array_merge($MergeMap,$map);
            $jsonMap = json_encode($MergeMap);

        return $jsonMap;
    }

    ###goods brand name##########################################################
    function BrandSearch($keyword){
        $rakuten_relust_goods = getRakutenResult($keyword); // キーワードと最低価格を指定
        $rakuten_relust_brand = search_brand($tagId);
        foreach((array)$item['tagId'] as $number){
            for($j = 1000709; $j <= 1000869; $j++){
                if($number == $j){
                    $brand_code = $number;
                }
            }
        }
            $rakuten_tag = search_brand($brand_code);
            foreach ( (array)$rakuten_tag as $tag):
                $tag_array = $tag['tagname'];
        $map = array(
        $brand_code => $tag_array['tag']->tagName,
                     );
         endforeach;
        $MergeMap = array();
        $MergeMap = array_merge($MergeMap,$map);
        $jsonMap = json_encode($MergeMap);

        return $jsonMap;

    }
        */
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
