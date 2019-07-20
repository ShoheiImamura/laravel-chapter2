---

theme: "Night"
transition: "default"
progress: true
slideNumber: true
loop: true
backgroundTransition: 'zoom'


---

# 2章

### Laravel アーキテクチャ

---

## はじめに

- 本資料は、Weeyble Laravel 輪読会用の資料です
- 対応箇所は、2章の前半部分のみです
- 権利関係で問題がございましたら、ご指摘いただけるとありがたいです

--

## 発表者自己紹介

- 今村昌平と申します。
- Web業務システム受託の会社で勤務(1年半)
- 自分で Webアプリ作成中(マニュアル共有アプリ)
- Web業界入る前は、動物のお医者さん

--

## 本日の概要

### 前半

- Laravel のライフサイクル
- サービスコンテナ

### 後半

- サービスプロバイダ
- コントラクト

---

## 2-1 ライフサイクル

- HTTPリクエスト ~ アプリケーション実行 ~ HTTPレスポンスの流れを説明します。

---

## 2-1-1 Laravelアプリケーションの実行の流れ

![Laravelアプリケーション実行の流れ](http://sample.com/images/sample.png)

--

### HTTPリクエストからアプリケーションの実行

1. HTTPリクエスト送信
2. public/index.php にてリクエストインスタンスを生成
3. HTTPカーネルにリクエストインスタンスを渡す
4. アプリケーションのセットアップ
5. ルータに　Request をディスパッチする


--

### アプリケーションの実行結果からHTTPレスポンスの出力

- リクエスト処理と逆の向きでレスポンスが返される

---

## 2-1-2 エントリポイント

- public/index.php が Laravel アプリケーションの起点となる
- ドキュメントルート配下に設置、Webサーバを設定する

--

### エントリポイントの全容

[エントリポイントへのリンク]()

--

### オートローダの読み込み

```php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

```

--

### フレームワークの起動

```php
$app = require_once __DIR__.'/../bootstrap/app.php';
```

--

### アプリケーションの実行

```php
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

```

--

### HTTPレスポンスの送信

```php
$response->send();
```

--

### 終了処理

```php
$kernel->terminate($request, $response);
```

---

## 2-1-3 HTTPカーネル

- アプリケーションのセットアップ
- ミドルウェアの設定
- ルータ実行
  - Request を与えて、Response を受け取る

--

### HTTPカーネルの実装

- エントリポイントから `handle` メソッドを実行
- デフォルトでは `App\Http\Kernel`クラスを実行
  - ミドルウェアの設定のみ記述
  - 実際の処理は `Illuminate\Foundation\Http\Kernel` クラスに実装

[App\Http\Kernel]()
[Illuminate\Foundation\Http\Kernel]()

--

### HTTPカーネルのhandleメソッド

- sendRequestThroughRouter() メソッドでアプリケーションを実行している
- 例外処理が発生した場合は、renderException()メソッドで Responseを生成

---

## 2-1-4 ルータ

- 定義されたルートから、Request にマッチするルートを探す
- マッチすると、そのコントローラ等を実行する
- `routes/web.php`(APIの場合は`routes/api.php`)に定義する

--

### ルートの定義

- ３種類の割当方法が存在する
  1. コントローラ名とメソッド名を指定する
  2. クラス名のみを指定する
  3. クロージャを指定する

--

### ルートの定義1

#### （コントローラ名とメソッド名を指定）

```php
Route::get('/tasks', 'TaskController@getTasks');
```

--

### ルートの定義2

#### （クラス名のみを指定）


```php
Route::post('/tasks', 'AddTaskAction');
```

--

### ルートの定義３3

#### （クロージャを指定）


```php
Route::get('/hello', function(Request $request){
    return view('hello);
});
```

---

## 2-1-5 ミドルウェア

---

## 2-1-6 コントローラ

---

## 2-2 サービスコンテナ

---

## 2-2-1 サービスコンテナとは

---

## 2-2-2 バインドと解決

---

## 2-2-3 バインド

---

## 2-2-4 解決

---

## 2-2-5 DIとサービスコンテナ

---

## 2-2-6 ファサード

---

## 2章 前半の用語集

| No. | 日本語名         | 英語名        | 説明                                                                                 |
|-----|------------------|---------------|--------------------------------------------------------------------------------------|
| 1   | エントリポイント | entry point   | コンピュータプログラムを構成するコードのうち、最初に実行する事になっている位置のこと |
| 2   | HTTPリクエスト   | HTTP request  | Web ブラウザからからサーバに送信されるデータ送信要求                                 |
| 3   | HTTPレスポンス   | HTTP response | サーバが、HTTPリクエストに対して応答するデータ                                       |
| 4   | オートローダ     | autoloader    | クラスやインターフェイス、トレイトが定義されたPHPファイルを自動で読み込む仕組み      |

