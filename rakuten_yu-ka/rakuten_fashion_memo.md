# Rakuten fashion

## goal
楽天市場でuserがほしい商品を最短距離で見つけられるweb pageの作成

## 大まかな役割分担
- frontend
  - Take, Rai, Nobu
- backend
  - Yu-ka, Ryo

## 使用する言語
- front
  - JavaScript
- back
  - SQL, JavaScript

## 目標
1. 同じitemの商品は複数表示しない
  1. 複数の店舗が出店していて，複数の値段・サービス内容がある場合は，検索結果ページでは，範囲(ex. ￥1000~￥2000)を表示
  2. 存在するcolor valiationは画像の下に各色を表示
    - カーソルを合わせて，それぞれの色の場合のimageを表示
  3. 一旦ある商品を検索した後に，類似商品ごとでsortされた検索結果での表示ができるようにする．

### 9/4~9/6
- front
  - 検索された商品の一覧を表示するページの雛形を作る
  - 検索フォームは，とりあえず簡素なものでOK.<br>
後からちゃんとしたものを作る．
- back
  - databaseの設計
  - rakuten apiの調査
  - 画像をカーソルを合わせて表示する方法の調査
